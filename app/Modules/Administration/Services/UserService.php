<?php

namespace Modules\Administration\Services;

use App\Models\SexeModel;
use DateTimeImmutable;
use Modules\Administration\Models\JuridictionModel;
use Modules\Administration\Models\ProfilModel;
use Modules\Administration\Models\StatutCompteModel;
use Modules\Administration\Models\UtilisateurModel;
use Modules\CourtJurisdiction\Models\CollineModel;
use Modules\CourtJurisdiction\Models\CommuneModel;
use Modules\CourtJurisdiction\Models\ProvinceModel;
use Modules\CourtJurisdiction\Models\ZoneModel;
use Modules\Notification\Models\StatutNotificationModel;
use Modules\Notification\Services\NotificationMailer;
use Throwable;

class UserService
{
    private const MAX_FIRST_NAME = 100;
    private const MAX_LAST_NAME  = 100;
    private const MAX_CNI        = 50;
    private const MAX_MATRICULE  = 50;
    private const MAX_EMAIL      = 150;
    private const MAX_PHONE      = 20;
    private const MIN_AGE_YEARS  = 16;

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
        $statutCompteId = ! empty($query['statut_compte_id']) ? (int) $query['statut_compte_id'] : null;

        // Backward-compatible Active/Inactive values from older filter URLs.
        if ($statutCompteId === null) {
            $legacyStatus = (string) ($query['account_status'] ?? '');
            if ($legacyStatus === '1' || $legacyStatus === 'true') {
                $statutCompteId = $this->statuses->findActiveId();
            } elseif ($legacyStatus === '0' || $legacyStatus === 'false') {
                $statutCompteId = $this->statuses->findInactiveId();
            }
        }

        try {
            $rows = $this->users->listWithRelations([
                'province_id'      => ! empty($query['province_id']) ? (int) $query['province_id'] : null,
                'commune_id'       => ! empty($query['commune_id']) ? (int) $query['commune_id'] : null,
                'juridiction_id'   => ! empty($query['juridiction_id']) ? (int) $query['juridiction_id'] : null,
                'statut_compte_id' => $statutCompteId,
            ]);
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
            log_message('error', 'Failed to load user {id}: {message}', [
                'id'      => $id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>,id?:int,email_sent?:bool}
     */
    public function create(array $input): array
    {
        $input  = $this->normalizeInput($input);
        $errors = $this->validate($input);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $activeId = $this->statuses->findActiveId();
        if (! $activeId) {
            return ['ok' => false, 'errors' => [lang('Backoffice.users_err_no_active_status')]];
        }

        $plainPassword = $this->generateTemporaryPassword();
        $data          = $this->mapWritable($input) + [
            'statut_compte_id'  => $activeId,
            'mot_de_passe_hash' => password_hash($plainPassword, PASSWORD_DEFAULT),
        ];

        try {
            $id = $this->users->insert($data, true);
        } catch (Throwable $e) {
            log_message('error', 'Failed to create user: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.users_err_save')]];
        }

        if (! $id) {
            return ['ok' => false, 'errors' => [lang('Backoffice.users_err_save')]];
        }

        // Same pattern as complainant registration: persist account first, then notify.
        log_message('info', 'Back-office user {id} created — invoking welcome email to {email}.', [
            'id'    => (int) $id,
            'email' => (string) ($data['email'] ?? ''),
        ]);

        $emailSent = $this->sendWelcomeCredentials((int) $id, $data, $plainPassword);

        return ['ok' => true, 'id' => (int) $id, 'email_sent' => $emailSent];
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

        $input  = $this->normalizeInput($input);
        $errors = $this->validate($input, $id);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $data = $this->mapWritable($input);

        try {
            $ok = $this->users->update($id, $data);
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
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
     * @return array<string, mixed>
     */
    private function normalizeInput(array $input): array
    {
        foreach ([
            'nom_utilisateur',
            'prenom_utilisateur',
            'numero_cni',
            'numero_matricule',
            'telephone',
            'email',
            'date_naissance',
        ] as $key) {
            if (array_key_exists($key, $input) && is_string($input[$key])) {
                $input[$key] = trim($input[$key]);
            }
        }

        return $input;
    }

    /**
     * @param array<string, mixed> $input
     * @return list<string>
     */
    private function validate(array $input, ?int $ignoreId = null): array
    {
        $errors   = [];
        $required = [
            'nom_utilisateur'       => lang('Backoffice.users_field_last_name'),
            'prenom_utilisateur'    => lang('Backoffice.users_field_first_name'),
            'numero_cni'            => lang('Backoffice.users_field_cni'),
            'numero_matricule'       => lang('Backoffice.users_field_matricule'),
            'telephone'             => lang('Backoffice.users_field_phone'),
            'email'                 => lang('Backoffice.users_field_email'),
            'date_naissance'        => lang('Backoffice.users_field_birth_date'),
            'profil_id'             => lang('Backoffice.users_field_profile'),
            'juridiction_id'        => lang('Backoffice.users_field_jurisdiction'),
            'sexe_id'               => lang('Backoffice.users_field_sex'),
            'province_naissance_id' => lang('Backoffice.users_field_province'),
            'commune_naissance_id'  => lang('Backoffice.users_field_commune'),
            'zone_naissance_id'     => lang('Backoffice.users_field_zone'),
            'colline_naissance_id'  => lang('Backoffice.users_field_colline'),
        ];

        foreach ($required as $key => $label) {
            if (trim((string) ($input[$key] ?? '')) === '') {
                $errors[] = lang('Backoffice.users_err_required', [$label]);
            }
        }

        $lengthRules = [
            'prenom_utilisateur' => [self::MAX_FIRST_NAME, lang('Backoffice.users_field_first_name')],
            'nom_utilisateur'    => [self::MAX_LAST_NAME, lang('Backoffice.users_field_last_name')],
            'numero_cni'         => [self::MAX_CNI, lang('Backoffice.users_field_cni')],
            'numero_matricule'    => [self::MAX_MATRICULE, lang('Backoffice.users_field_matricule')],
            'email'              => [self::MAX_EMAIL, lang('Backoffice.users_field_email')],
            'telephone'          => [self::MAX_PHONE, lang('Backoffice.users_field_phone')],
        ];

        foreach ($lengthRules as $key => [$max, $label]) {
            $value = (string) ($input[$key] ?? '');
            if ($value !== '' && mb_strlen($value) > $max) {
                $errors[] = lang('Backoffice.users_err_max_length', [$label, $max]);
            }
        }

        $email = (string) ($input['email'] ?? '');
        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = lang('Backoffice.users_err_email');
        }

        if ($email !== '' && $this->users->emailExists($email, $ignoreId)) {
            $errors[] = lang('Backoffice.users_err_email_taken');
        }

        $cni = (string) ($input['numero_cni'] ?? '');
        if ($cni !== '' && $this->users->cniExists($cni, $ignoreId)) {
            $errors[] = lang('Backoffice.users_err_cni_taken');
        }

        $matricule = (string) ($input['numero_matricule'] ?? '');
        if ($matricule !== '' && $this->users->matriculeExists($matricule, $ignoreId)) {
            $errors[] = lang('Backoffice.users_err_matricule_taken');
        }

        $dob = (string) ($input['date_naissance'] ?? '');
        if ($dob !== '') {
            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
                $errors[] = lang('Backoffice.users_err_birth_date');
            } else {
                try {
                    $birth   = new DateTimeImmutable($dob);
                    $cutoff  = (new DateTimeImmutable('today'))->modify('-' . self::MIN_AGE_YEARS . ' years');
                    if ($birth > $cutoff) {
                        $errors[] = lang('Backoffice.users_err_min_age');
                    }
                } catch (Throwable $e) {
                    $errors[] = lang('Backoffice.users_err_birth_date');
                }
            }
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

    private function generateTemporaryPassword(int $length = 12): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        $max      = strlen($alphabet) - 1;
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $alphabet[random_int(0, $max)];
        }

        return $password;
    }

    /**
     * Send French welcome credentials and persist notification_utilisateur.
     * Account creation must succeed even when delivery fails.
     *
     * @param array<string, mixed> $userData
     */
    private function sendWelcomeCredentials(int $utilisateurId, array $userData, string $plainPassword): bool
    {
        $email = trim((string) ($userData['email'] ?? ''));
        $name  = trim(
            trim((string) ($userData['prenom_utilisateur'] ?? '')) . ' '
            . trim((string) ($userData['nom_utilisateur'] ?? ''))
        );
        $cni       = trim((string) ($userData['numero_cni'] ?? ''));
        $matricule = trim((string) ($userData['numero_matricule'] ?? ''));
        $loginUrl  = site_url('backoffice');
        $loginId   = $this->primaryLoginIdentifier($cni, $matricule, $email);

        $language = service('language');
        $previous = $language->getLocale();
        $language->setLocale('fr');

        try {
            $subject = lang('Mail.subject_bo_user_registration');
            $body    = $this->buildWelcomeNotificationBody(
                $name,
                $loginId,
                $cni,
                $matricule,
                $email,
                $plainPassword,
                $loginUrl
            );
        } finally {
            $language->setLocale($previous);
        }

        /** @var NotificationMailer $mailer */
        $mailer = service('notifications');
        $sentOk = false;

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            log_message('error', 'Back-office welcome email skipped for user {id}: missing or invalid email.', [
                'id' => $utilisateurId,
            ]);
            $this->persistUserNotification($utilisateurId, $subject, $body, false);

            return false;
        }

        if (! $mailer->isConfigured()) {
            log_message('error', 'Back-office welcome email aborted for user {id}: SMTP is not configured.', [
                'id' => $utilisateurId,
            ]);
            $this->persistUserNotification($utilisateurId, $subject, $body, false);

            return false;
        }

        try {
            $sentOk = $mailer->sendBackofficeUserRegistration(
                $email,
                $name !== '' ? $name : $email,
                $plainPassword,
                [
                    'cni'       => $cni,
                    'matricule' => $matricule,
                    'email'     => $email,
                    'login_id'  => $loginId,
                ],
                $loginUrl
            );
        } catch (Throwable $e) {
            log_message('error', 'Back-office welcome email exception for user {id}: {message}', [
                'id'      => $utilisateurId,
                'message' => $e->getMessage(),
            ]);
            $sentOk = false;
        }

        if (! $sentOk) {
            log_message('error', 'Welcome email failed after successful back-office user creation for user {id} ({email}): {error}', [
                'id'    => $utilisateurId,
                'email' => $email,
                'error' => $mailer->getLastError() ?: 'unknown error',
            ]);
        } else {
            log_message('info', 'Back-office welcome email sent for user {id} to {email}.', [
                'id'    => $utilisateurId,
                'email' => $email,
            ]);
        }

        $this->persistUserNotification($utilisateurId, $subject, $body, $sentOk);

        return $sentOk;
    }

    private function primaryLoginIdentifier(string $cni, string $matricule, string $email): string
    {
        if ($cni !== '') {
            return $cni;
        }
        if ($matricule !== '') {
            return $matricule;
        }

        return $email;
    }

    private function buildWelcomeNotificationBody(
        string $name,
        string $loginId,
        string $cni,
        string $matricule,
        string $email,
        string $plainPassword,
        string $loginUrl
    ): string {
        $lines = [
            'Bonjour' . ($name !== '' ? ' ' . $name : '') . ',',
            '',
            'Bienvenue sur JusticeHeritage. Votre compte utilisateur Back Office a été créé avec succès.',
            '',
            'Identifiant de connexion : ' . ($loginId !== '' ? $loginId : '—'),
            'Numéro CNI : ' . ($cni !== '' ? $cni : '—'),
            'Numéro matricule : ' . ($matricule !== '' ? $matricule : '—'),
            'Adresse e-mail : ' . ($email !== '' ? $email : '—'),
            'Mot de passe temporaire : ' . $plainPassword,
            '',
            'Connexion Back Office : ' . $loginUrl,
            '',
            'Pour votre sécurité, changez votre mot de passe dès la première connexion.',
        ];

        return implode("\n", $lines);
    }

    private function persistUserNotification(int $utilisateurId, string $subject, string $body, bool $sentOk): void
    {
        $canalId  = $this->lookupCanalId(['email', 'e-mail', 'mail', 'courriel']);
        $statuses = new StatutNotificationModel();
        $statusId = $sentOk
            ? ($statuses->idByKeywords(['envoy', 'sent', 'deliver', 'succès', 'success']) ?? 2)
            : ($statuses->idByKeywords(['échec', 'echec', 'fail', 'error', 'erreur']) ?? 4);

        if ($utilisateurId < 1) {
            return;
        }

        if (! $canalId) {
            log_message('error', 'Unable to persist welcome notification_utilisateur for user {id}: email canal not found.', [
                'id' => $utilisateurId,
            ]);

            return;
        }

        if (! $statusId) {
            log_message('error', 'Unable to persist welcome notification_utilisateur for user {id}: notification status not found.', [
                'id' => $utilisateurId,
            ]);

            return;
        }

        $now = date('Y-m-d H:i:s');

        try {
            db_connect()->table('notification.notification_utilisateur')->insert([
                'utilisateur_id'         => $utilisateurId,
                'canal_notification_id'  => $canalId,
                'sujet'                  => $subject,
                'corps'                  => $body,
                'statut_notification_id' => $statusId,
                'envoye_le'              => $now,
                'created_at'             => $now,
            ]);
        } catch (Throwable $e) {
            log_message('error', 'Failed to insert welcome notification_utilisateur for user {id}: {message}', [
                'id'      => $utilisateurId,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param list<string> $needles
     */
    private function lookupCanalId(array $needles): ?int
    {
        try {
            $rows = db_connect()
                ->table('notification.canal_notification')
                ->select('canal_notification_id, description_canal_notification')
                ->get()
                ->getResultArray();
        } catch (Throwable $e) {
            return null;
        }

        foreach ($rows as $row) {
            $label = mb_strtolower(trim((string) ($row['description_canal_notification'] ?? '')));
            foreach ($needles as $needle) {
                if ($label !== '' && str_contains($label, mb_strtolower($needle))) {
                    return (int) $row['canal_notification_id'];
                }
            }
        }

        $first = $rows[0] ?? null;

        return $first ? (int) $first['canal_notification_id'] : null;
    }
}
