<?php

namespace Modules\Administration\Services;

use App\Models\SexeModel;
use Modules\Administration\Models\JuridictionModel;
use Modules\Administration\Models\ProfilModel;
use Modules\Administration\Models\StatutCompteModel;
use Modules\Administration\Models\UtilisateurModel;
use Modules\CourtJurisdiction\Models\CollineModel;
use Modules\CourtJurisdiction\Models\CommuneModel;
use Modules\CourtJurisdiction\Models\ProvinceModel;
use Modules\CourtJurisdiction\Models\ZoneModel;

class UserService
{
    private UtilisateurModel $users;
    private ProfilModel $profiles;
    private StatutCompteModel $statuses;
    private JuridictionModel $jurisdictions;
    private SexeModel $sexes;

    public function __construct(
        ?UtilisateurModel $users = null,
        ?ProfilModel $profiles = null,
        ?StatutCompteModel $statuses = null,
        ?JuridictionModel $jurisdictions = null,
        ?SexeModel $sexes = null
    ) {
        $this->users         = $users ?? new UtilisateurModel();
        $this->profiles      = $profiles ?? new ProfilModel();
        $this->statuses      = $statuses ?? new StatutCompteModel();
        $this->jurisdictions = $jurisdictions ?? new JuridictionModel();
        $this->sexes         = $sexes ?? new SexeModel();
    }

    /**
     * @param array<string, mixed> $query
     * @return list<array<string, mixed>>
     */
    public function listUsers(array $query = []): array
    {
        $accountActive = null;
        if (($query['account_status'] ?? '') === '1' || ($query['account_status'] ?? '') === 'true') {
            $accountActive = true;
        } elseif (($query['account_status'] ?? '') === '0' || ($query['account_status'] ?? '') === 'false') {
            $accountActive = false;
        }

        try {
            $rows = $this->users->listWithRelations([
                'province_id'            => ! empty($query['province_id']) ? (int) $query['province_id'] : null,
                'commune_id'             => ! empty($query['commune_id']) ? (int) $query['commune_id'] : null,
                'niveau_juridiction_id'  => ! empty($query['niveau_juridiction_id']) ? (int) $query['niveau_juridiction_id'] : null,
                'juridiction_id'         => ! empty($query['juridiction_id']) ? (int) $query['juridiction_id'] : null,
                'account_active'         => $accountActive,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list users: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(function (array $row): array {
            $isActive = $this->statuses->isActiveStatus(
                isset($row['statut_compte_id']) ? (int) $row['statut_compte_id'] : null
            );

            return [
                'id'              => (int) $row['utilisateur_id'],
                'full_name'       => trim(($row['prenom_utilisateur'] ?? '') . ' ' . ($row['nom_utilisateur'] ?? '')),
                'nom'             => $row['nom_utilisateur'] ?? '',
                'prenom'          => $row['prenom_utilisateur'] ?? '',
                'numero_cni'      => $row['numero_cni'] ?? '',
                'numero_matricule' => $row['numero_matricule'] ?? '',
                'email'           => $row['email'] ?? '',
                'telephone'       => $row['telephone'] ?? '',
                'profile'         => $row['libelle_profil'] ?? '—',
                'status_label'    => $row['desc_statut_compte'] ?? '—',
                'is_active'       => $isActive,
                'jurisdiction'    => $row['nom_juridiction'] ?? '—',
            ];
        }, $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        try {
            return $this->users->findWithRelations($id);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load user {id}: {message}', [
                'id'      => $id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>,id?:int}
     */
    public function create(array $input): array
    {
        $errors = $this->validate($input);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $activeId = $this->statuses->findActiveId();
        if (! $activeId) {
            return ['ok' => false, 'errors' => [lang('Backoffice.users_err_no_active_status')]];
        }

        $email = trim((string) $input['email']);
        $data  = $this->mapWritable($input) + [
            'statut_compte_id'  => $activeId,
            'user_name'         => $email,
            'mot_de_passe_hash' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
        ];

        try {
            $id = $this->users->insert($data, true);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to create user: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.users_err_save')]];
        }

        if (! $id) {
            return ['ok' => false, 'errors' => [lang('Backoffice.users_err_save')]];
        }

        return ['ok' => true, 'id' => (int) $id];
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>}
     */
    public function update(int $id, array $input): array
    {
        $existing = $this->users->find($id);
        if (! $existing) {
            return ['ok' => false, 'errors' => [lang('Backoffice.users_err_not_found')]];
        }

        $errors = $this->validate($input, $id);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $data = $this->mapWritable($input);

        try {
            $ok = $this->users->update($id, $data);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to update user {id}: {message}', [
                'id'      => $id,
                'message' => $e->getMessage(),
            ]);

            return ['ok' => false, 'errors' => [lang('Backoffice.users_err_save')]];
        }

        if (! $ok) {
            return ['ok' => false, 'errors' => [lang('Backoffice.users_err_save')]];
        }

        return ['ok' => true];
    }

    /**
     * @return array{ok:bool,errors?:list<string>,activated?:bool}
     */
    public function toggleStatus(int $id): array
    {
        $user = $this->users->find($id);
        if (! $user) {
            return ['ok' => false, 'errors' => [lang('Backoffice.users_err_not_found')]];
        }

        $isActive   = $this->statuses->isActiveStatus((int) ($user['statut_compte_id'] ?? 0));
        $targetId   = $isActive ? $this->statuses->findInactiveId() : $this->statuses->findActiveId();
        $activating = ! $isActive;

        if (! $targetId) {
            return ['ok' => false, 'errors' => [lang('Backoffice.users_err_status_missing')]];
        }

        try {
            $this->users->update($id, ['statut_compte_id' => $targetId]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to toggle user status {id}: {message}', [
                'id'      => $id,
                'message' => $e->getMessage(),
            ]);

            return ['ok' => false, 'errors' => [lang('Backoffice.users_err_save')]];
        }

        return ['ok' => true, 'activated' => $activating];
    }

    /**
     * @param array<string, mixed> $input
     * @return list<string>
     */
    private function validate(array $input, ?int $ignoreId = null): array
    {
        $errors = [];
        $required = [
            'nom_utilisateur'        => lang('Backoffice.users_field_last_name'),
            'prenom_utilisateur'     => lang('Backoffice.users_field_first_name'),
            'numero_cni'             => lang('Backoffice.users_field_cni'),
            'numero_matricule'        => lang('Backoffice.users_field_matricule'),
            'telephone'              => lang('Backoffice.users_field_phone'),
            'email'                  => lang('Backoffice.users_field_email'),
            'date_naissance'         => lang('Backoffice.users_field_birth_date'),
            'profil_id'              => lang('Backoffice.users_field_profile'),
            'juridiction_id'         => lang('Backoffice.users_field_jurisdiction'),
            'sexe_id'                => lang('Backoffice.users_field_sex'),
            'province_naissance_id'  => lang('Backoffice.users_field_province'),
            'commune_naissance_id'   => lang('Backoffice.users_field_commune'),
            'zone_naissance_id'      => lang('Backoffice.users_field_zone'),
            'colline_naissance_id'   => lang('Backoffice.users_field_colline'),
        ];

        foreach ($required as $key => $label) {
            if (trim((string) ($input[$key] ?? '')) === '') {
                $errors[] = lang('Backoffice.users_err_required', [$label]);
            }
        }

        $email = trim((string) ($input['email'] ?? ''));
        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = lang('Backoffice.users_err_email');
        }

        if ($email !== '' && $this->users->emailExists($email, $ignoreId)) {
            $errors[] = lang('Backoffice.users_err_email_taken');
        }

        $cni = trim((string) ($input['numero_cni'] ?? ''));
        if ($cni !== '' && $this->users->cniExists($cni, $ignoreId)) {
            $errors[] = lang('Backoffice.users_err_cni_taken');
        }

        $matricule = trim((string) ($input['numero_matricule'] ?? ''));
        if ($matricule !== '' && $this->users->matriculeExists($matricule, $ignoreId)) {
            $errors[] = lang('Backoffice.users_err_matricule_taken');
        }

        $dob = trim((string) ($input['date_naissance'] ?? ''));
        if ($dob !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
            $errors[] = lang('Backoffice.users_err_birth_date');
        }

        if (! empty($input['profil_id']) && ! $this->profiles->find((int) $input['profil_id'])) {
            $errors[] = lang('Backoffice.users_err_profile');
        }

        if (! empty($input['juridiction_id']) && ! $this->jurisdictions->find((int) $input['juridiction_id'])) {
            $errors[] = lang('Backoffice.users_err_jurisdiction');
        }

        if (! empty($input['sexe_id']) && ! $this->sexes->find((int) $input['sexe_id'])) {
            $errors[] = lang('Backoffice.users_err_sex');
        }

        $provinceId = (int) ($input['province_naissance_id'] ?? 0);
        $communeId  = (int) ($input['commune_naissance_id'] ?? 0);
        $zoneId     = (int) ($input['zone_naissance_id'] ?? 0);
        $collineId  = (int) ($input['colline_naissance_id'] ?? 0);

        if ($provinceId && ! (new ProvinceModel())->find($provinceId)) {
            $errors[] = lang('Backoffice.users_err_province');
        }

        if ($communeId) {
            $commune = (new CommuneModel())->find($communeId);
            if (! $commune || (int) ($commune['province_id'] ?? 0) !== $provinceId) {
                $errors[] = lang('Backoffice.users_err_commune');
            }
        }

        if ($zoneId) {
            $zone = (new ZoneModel())->find($zoneId);
            if (! $zone || (int) ($zone['commune_id'] ?? 0) !== $communeId) {
                $errors[] = lang('Backoffice.users_err_zone');
            }
        }

        if ($collineId) {
            $colline = (new CollineModel())->find($collineId);
            if (! $colline || (int) ($colline['zone_id'] ?? 0) !== $zoneId) {
                $errors[] = lang('Backoffice.users_err_colline');
            }
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    private function mapWritable(array $input): array
    {
        return [
            'nom_utilisateur'       => trim((string) ($input['nom_utilisateur'] ?? '')),
            'prenom_utilisateur'    => trim((string) ($input['prenom_utilisateur'] ?? '')),
            'numero_cni'            => trim((string) ($input['numero_cni'] ?? '')),
            'numero_matricule'       => trim((string) ($input['numero_matricule'] ?? '')),
            'telephone'             => trim((string) ($input['telephone'] ?? '')),
            'email'                 => trim((string) ($input['email'] ?? '')),
            'date_naissance'        => trim((string) ($input['date_naissance'] ?? '')),
            'profil_id'             => (int) ($input['profil_id'] ?? 0),
            'juridiction_id'        => (int) ($input['juridiction_id'] ?? 0),
            'sexe_id'               => (int) ($input['sexe_id'] ?? 0),
            'province_naissance_id' => (int) ($input['province_naissance_id'] ?? 0),
            'commune_naissance_id'  => (int) ($input['commune_naissance_id'] ?? 0),
            'zone_naissance_id'     => (int) ($input['zone_naissance_id'] ?? 0),
            'colline_naissance_id'  => (int) ($input['colline_naissance_id'] ?? 0),
        ];
    }
}
