<?php

namespace Modules\Complaint\Services;

use Modules\Administration\Models\AuditLogModel;
use Modules\Administration\Models\ProfilModel;
use Modules\Complaint\Models\EtapePlainteModel;
use Modules\Complaint\Models\EtapePlainteProfilModel;
use Modules\CourtJurisdiction\Models\NiveauJuridictionModel;

class ComplaintStageService
{
    private EtapePlainteModel $stages;
    private EtapePlainteProfilModel $stageProfiles;
    private ProfilModel $profiles;
    private NiveauJuridictionModel $levels;
    private AuditLogModel $audit;

    public function __construct(
        ?EtapePlainteModel $stages = null,
        ?EtapePlainteProfilModel $stageProfiles = null,
        ?ProfilModel $profiles = null,
        ?NiveauJuridictionModel $levels = null,
        ?AuditLogModel $audit = null
    ) {
        $this->stages        = $stages ?? new EtapePlainteModel();
        $this->stageProfiles = $stageProfiles ?? new EtapePlainteProfilModel();
        $this->profiles      = $profiles ?? new ProfilModel();
        $this->levels        = $levels ?? new NiveauJuridictionModel();
        $this->audit         = $audit ?? new AuditLogModel();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(?bool $isActive = null): array
    {
        try {
            $rows = $this->stages->listWithCounts($isActive);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list complaint stages: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static function (array $row): array {
            $active = filter_var($row['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);

            return [
                'id'              => (int) $row['etape_plainte_id'],
                'description'     => $row['description_etape_plainte'] ?? '',
                'level'           => $row['desc_niveau_juridiction'] ?? '—',
                'niveau_id'       => (int) ($row['niveau_juridiction_id'] ?? 0),
                'profiles_count'  => (int) ($row['profiles_count'] ?? 0),
                'is_convocation'  => filter_var($row['is_convocation'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'is_audience'     => filter_var($row['is_audience'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'is_active'       => $active,
                'status'          => $active ? lang('Backoffice.status_active') : lang('Backoffice.status_inactive'),
            ];
        }, $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $row = $this->stages->find($id);
        if (! $row) {
            return null;
        }

        $row['profil_ids'] = $this->stages->profileIds($id);

        return $row;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function profiles(int $etapeId): array
    {
        try {
            $rows = $this->stages->listProfiles($etapeId);
        } catch (\Throwable $e) {
            return [];
        }

        return array_map(static function (array $row): array {
            $active = filter_var($row['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);

            return [
                'code'        => $row['code_profil'] ?? '',
                'name'        => $row['libelle_profil'] ?? '',
                'description' => $row['description_profil'] ?? '—',
                'is_active'   => $active,
                'status'      => $active ? lang('Backoffice.status_active') : lang('Backoffice.status_inactive'),
            ];
        }, $rows);
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

        $data = $this->mapWritable($input) + ['is_active' => true];
        $profilIds = $this->extractProfilIds($input);

        $db = db_connect();
        $db->transStart();

        try {
            $id = $this->stages->insert($data, true);
            if (! $id) {
                throw new \RuntimeException('insert failed');
            }
            $this->stageProfiles->syncForEtape((int) $id, $profilIds);
            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Failed to create complaint stage: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.cs_err_save')]];
        }

        if ($db->transStatus() === false) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cs_err_save')]];
        }

        $this->audit->record('CREATE', 'plainte.etape_plainte', (int) $id, null, $data + ['profil_ids' => $profilIds], $this->actorId());

        return ['ok' => true, 'id' => (int) $id];
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>}
     */
    public function update(int $id, array $input): array
    {
        $existing = $this->stages->find($id);
        if (! $existing) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cs_err_not_found')]];
        }

        $errors = $this->validate($input);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $data      = $this->mapWritable($input);
        $profilIds = $this->extractProfilIds($input);

        $db = db_connect();
        $db->transStart();

        try {
            $this->stages->update($id, $data);
            $this->stageProfiles->syncForEtape($id, $profilIds);
            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Failed to update complaint stage: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.cs_err_save')]];
        }

        if ($db->transStatus() === false) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cs_err_save')]];
        }

        $this->audit->record('UPDATE', 'plainte.etape_plainte', $id, $existing, $data + ['profil_ids' => $profilIds], $this->actorId());

        return ['ok' => true];
    }

    /**
     * @return array{ok:bool,errors?:list<string>,activated?:bool}
     */
    public function toggleStatus(int $id): array
    {
        $row = $this->stages->find($id);
        if (! $row) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cs_err_not_found')]];
        }

        $isActive   = filter_var($row['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $activating = ! $isActive;

        try {
            $this->stages->update($id, ['is_active' => $activating]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cs_err_save')]];
        }

        $this->audit->record(
            $activating ? 'ACTIVATE' : 'DEACTIVATE',
            'plainte.etape_plainte',
            $id,
            ['is_active' => $isActive],
            ['is_active' => $activating],
            $this->actorId()
        );

        return ['ok' => true, 'activated' => $activating];
    }

    /**
     * @param array<string, mixed> $input
     * @return list<string>
     */
    private function validate(array $input): array
    {
        $errors = [];
        if (trim((string) ($input['description_etape_plainte'] ?? '')) === '') {
            $errors[] = lang('Backoffice.cs_err_required_description');
        }

        $niveauId = (int) ($input['niveau_juridiction_id'] ?? 0);
        if ($niveauId < 1 || ! $this->levels->find($niveauId)) {
            $errors[] = lang('Backoffice.cs_err_level');
        }

        $profilIds = $this->extractProfilIds($input);
        if ($profilIds === []) {
            $errors[] = lang('Backoffice.cs_err_profiles');
        } else {
            foreach ($profilIds as $profilId) {
                if (! $this->profiles->find($profilId)) {
                    $errors[] = lang('Backoffice.cs_err_profile_invalid');
                    break;
                }
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
            'description_etape_plainte' => trim((string) ($input['description_etape_plainte'] ?? '')),
            'niveau_juridiction_id'     => (int) ($input['niveau_juridiction_id'] ?? 0),
            'is_convocation'            => $this->toBool($input['is_convocation'] ?? false),
            'is_audience'               => $this->toBool($input['is_audience'] ?? false),
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return list<int>
     */
    private function extractProfilIds(array $input): array
    {
        $raw = $input['profil_ids'] ?? [];
        if (! is_array($raw)) {
            $raw = [$raw];
        }

        return array_values(array_unique(array_filter(array_map('intval', $raw), static fn (int $id): bool => $id > 0)));
    }

    private function toBool(mixed $value): bool
    {
        return $value === true || $value === 1 || $value === '1' || $value === 'true' || $value === 'on';
    }

    private function actorId(): ?int
    {
        $id = session('backoffice_user_id');

        return $id ? (int) $id : null;
    }
}
