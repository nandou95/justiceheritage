<?php

namespace Modules\People\Services;

use App\Models\SexeModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use DateTimeImmutable;
use Modules\Administration\Models\AuditLogModel;
use Modules\CourtJurisdiction\Models\CollineModel;
use Modules\CourtJurisdiction\Models\CommuneModel;
use Modules\CourtJurisdiction\Models\ProvinceModel;
use Modules\CourtJurisdiction\Models\ZoneModel;
use Modules\People\Models\PersonneModel;
use Throwable;

class PersonService
{
    private const MIN_AGE_YEARS = 16;
    private const CNI_MAX_KB    = 2048;
    private const CNI_EXTS      = ['pdf', 'jpg', 'jpeg', 'png'];
    private const CNI_MIMES     = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/jpg',
    ];

    private PersonneModel $people;
    private SexeModel $sexes;
    private AuditLogModel $audit;

    public function __construct(
        ?PersonneModel $people = null,
        ?SexeModel $sexes = null,
        ?AuditLogModel $audit = null
    ) {
        $this->people = $people ?? new PersonneModel();
        $this->sexes  = $sexes ?? new SexeModel();
        $this->audit  = $audit ?? new AuditLogModel();
    }

    /**
     * @param array<string, mixed> $query
     * @return list<array<string, mixed>>
     */
    public function listPeople(array $query = []): array
    {
        try {
            $rows = $this->people->listWithRelations([
                'province_naissance_id' => ! empty($query['province_naissance_id']) ? (int) $query['province_naissance_id'] : null,
                'commune_naissance_id'  => ! empty($query['commune_naissance_id']) ? (int) $query['commune_naissance_id'] : null,
                'zone_naissance_id'     => ! empty($query['zone_naissance_id']) ? (int) $query['zone_naissance_id'] : null,
                'colline_naissance_id'  => ! empty($query['colline_naissance_id']) ? (int) $query['colline_naissance_id'] : null,
                'sexe_id'               => ! empty($query['sexe_id']) ? (int) $query['sexe_id'] : null,
                'date_naissance'        => ! empty($query['date_naissance']) ? (string) $query['date_naissance'] : null,
            ]);
        } catch (Throwable $e) {
            log_message('error', 'Failed to list people: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(fn (array $row): array => $this->mapListRow($row), $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        try {
            return $this->people->findWithRelations($id);
        } catch (Throwable $e) {
            log_message('error', 'Failed to load person {id}: {message}', [
                'id'      => $id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listComplaints(int $personneId): array
    {
        try {
            $rows = $this->people->listComplaintsForPerson($personneId);
        } catch (Throwable $e) {
            log_message('error', 'Failed to list complaints for person {id}: {message}', [
                'id'      => $personneId,
                'message' => $e->getMessage(),
            ]);

            return [];
        }

        return array_map(static function (array $row): array {
            $isAppeal = db_bool($row['is_recours'] ?? false);

            return [
                'case_number'   => (string) ($row['numero_dossier'] ?? ''),
                'subject'       => (string) ($row['objet'] ?? ''),
                'description'   => (string) ($row['description'] ?? ''),
                'role'          => (string) ($row['description_role_personne'] ?? '—'),
                'jurisdiction'  => (string) ($row['nom_juridiction'] ?? '—'),
                'level'         => (string) ($row['desc_niveau_juridiction'] ?? '—'),
                'stage'         => (string) ($row['description_etape_plainte'] ?? '—'),
                'status'        => (string) ($row['description_statut_plainte'] ?? '—'),
                'filing_date'   => (string) ($row['date_depot'] ?? '—'),
                'is_appeal'     => $isAppeal,
                'appeal_label'  => $isAppeal ? lang('Backoffice.yes') : lang('Backoffice.no'),
            ];
        }, $rows);
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>,id?:int}
     */
    public function create(array $input, ?UploadedFile $cniFile): array
    {
        $errors = $this->validate($input, null, $cniFile, true);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $uploadPath = $this->storeCniFile($cniFile);
        if ($uploadPath === null) {
            return ['ok' => false, 'errors' => [lang('Backoffice.people_err_cni_save')]];
        }

        $data = $this->mapWritable($input) + ['upload_cni' => $uploadPath];

        try {
            $id = $this->people->insert($data, true);
        } catch (Throwable $e) {
            $this->deleteUploadedFile($uploadPath);
            log_message('error', 'Failed to create person: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.people_err_save')]];
        }

        if (! $id) {
            $this->deleteUploadedFile($uploadPath);

            return ['ok' => false, 'errors' => [lang('Backoffice.people_err_save')]];
        }

        $this->audit->record('CREATE', 'plaignant.personne', (int) $id, null, $data, $this->actorId());

        return ['ok' => true, 'id' => (int) $id];
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>}
     */
    public function update(int $id, array $input, ?UploadedFile $cniFile): array
    {
        $existing = $this->people->find($id);
        if (! $existing) {
            return ['ok' => false, 'errors' => [lang('Backoffice.people_err_not_found')]];
        }

        $hasExistingFile = ! empty($existing['upload_cni']);
        $errors          = $this->validate($input, $id, $cniFile, ! $hasExistingFile);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $data      = $this->mapWritable($input);
        $newUpload = null;

        if ($cniFile !== null && $cniFile->isValid() && ! $cniFile->hasMoved()) {
            $newUpload = $this->storeCniFile($cniFile);
            if ($newUpload === null) {
                return ['ok' => false, 'errors' => [lang('Backoffice.people_err_cni_save')]];
            }
            $data['upload_cni'] = $newUpload;
        }

        try {
            $ok = $this->people->update($id, $data);
        } catch (Throwable $e) {
            if ($newUpload !== null) {
                $this->deleteUploadedFile($newUpload);
            }
            log_message('error', 'Failed to update person {id}: {message}', [
                'id'      => $id,
                'message' => $e->getMessage(),
            ]);

            return ['ok' => false, 'errors' => [lang('Backoffice.people_err_save')]];
        }

        if (! $ok) {
            if ($newUpload !== null) {
                $this->deleteUploadedFile($newUpload);
            }

            return ['ok' => false, 'errors' => [lang('Backoffice.people_err_save')]];
        }

        if ($newUpload !== null && ! empty($existing['upload_cni'])) {
            $this->deleteUploadedFile((string) $existing['upload_cni']);
        }

        $this->audit->record('UPDATE', 'plaignant.personne', $id, $existing, $data, $this->actorId());

        return ['ok' => true];
    }

    public function resolveCniAbsolutePath(string $relativePath): ?string
    {
        $relativePath = str_replace(['\\', "\0"], ['/', ''], $relativePath);
        $relativePath = ltrim($relativePath, '/');

        if ($relativePath === '' || str_contains($relativePath, '..')) {
            return null;
        }

        if (! str_starts_with($relativePath, 'uploads/cni/')) {
            return null;
        }

        $fullPath = WRITEPATH . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        if (! is_file($fullPath)) {
            return null;
        }

        return $fullPath;
    }

    /**
     * @param array<string, mixed> $input
     * @return list<string>
     */
    private function validate(array $input, ?int $ignoreId, ?UploadedFile $cniFile, bool $fileRequired): array
    {
        $errors = [];

        $required = [
            'nom_personne'          => lang('Backoffice.people_field_last_name'),
            'prenom_personne'       => lang('Backoffice.people_field_first_name'),
            'sexe_id'               => lang('Backoffice.people_field_gender'),
            'date_naissance'        => lang('Backoffice.people_field_birth_date'),
            'email'                 => lang('Backoffice.people_field_email'),
            'telephone'             => lang('Backoffice.people_field_phone'),
            'province_naissance_id' => lang('Backoffice.people_field_province'),
            'commune_naissance_id'  => lang('Backoffice.people_field_commune'),
            'zone_naissance_id'     => lang('Backoffice.people_field_zone'),
            'colline_naissance_id'  => lang('Backoffice.people_field_colline'),
            'numero_cni'            => lang('Backoffice.people_field_cni'),
            'adresse_residence'     => lang('Backoffice.people_field_address'),
        ];

        foreach ($required as $key => $label) {
            if (trim((string) ($input[$key] ?? '')) === '') {
                $errors[] = lang('Backoffice.people_err_required', [$label]);
            }
        }

        $email = trim((string) ($input['email'] ?? ''));
        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = lang('Backoffice.people_err_email');
        } elseif ($email !== '' && $this->people->emailExists($email, $ignoreId)) {
            $errors[] = lang('Backoffice.people_err_email_taken');
        }

        $cni = trim((string) ($input['numero_cni'] ?? ''));
        if ($cni !== '' && $this->people->cniExists($cni, $ignoreId)) {
            $errors[] = lang('Backoffice.people_err_cni_taken');
        }

        $dob = trim((string) ($input['date_naissance'] ?? ''));
        if ($dob !== '') {
            if (! $this->isValidDate($dob)) {
                $errors[] = lang('Backoffice.people_err_dob');
            } elseif (! $this->isMinimumAge($dob)) {
                $errors[] = lang('Backoffice.people_err_min_age');
            }
        }

        $sexeId = (int) ($input['sexe_id'] ?? 0);
        if ($sexeId > 0 && ! $this->sexes->find($sexeId)) {
            $errors[] = lang('Backoffice.people_err_gender');
        }

        $provinceId = (int) ($input['province_naissance_id'] ?? 0);
        $communeId  = (int) ($input['commune_naissance_id'] ?? 0);
        $zoneId     = (int) ($input['zone_naissance_id'] ?? 0);
        $collineId  = (int) ($input['colline_naissance_id'] ?? 0);

        if ($provinceId > 0 && ! (new ProvinceModel())->find($provinceId)) {
            $errors[] = lang('Backoffice.people_err_province');
        }
        if ($communeId > 0 && ! (new CommuneModel())->find($communeId)) {
            $errors[] = lang('Backoffice.people_err_commune');
        }
        if ($zoneId > 0 && ! (new ZoneModel())->find($zoneId)) {
            $errors[] = lang('Backoffice.people_err_zone');
        }
        if ($collineId > 0 && ! (new CollineModel())->find($collineId)) {
            $errors[] = lang('Backoffice.people_err_colline');
        }

        $fileErrors = $this->validateCniFile($cniFile, $fileRequired);
        $errors     = array_merge($errors, $fileErrors);

        return $errors;
    }

    /**
     * @return list<string>
     */
    private function validateCniFile(?UploadedFile $file, bool $required): array
    {
        if ($file === null || ! $file->isValid() || $file->hasMoved()) {
            return $required ? [lang('Backoffice.people_err_cni_required')] : [];
        }

        if ($file->getError() !== UPLOAD_ERR_OK) {
            return [lang('Backoffice.people_err_cni_required')];
        }

        $ext = strtolower((string) $file->getExtension());
        if (! in_array($ext, self::CNI_EXTS, true)) {
            return [lang('Backoffice.people_err_cni_type')];
        }

        $mime = strtolower((string) $file->getMimeType());
        if (! in_array($mime, self::CNI_MIMES, true)) {
            return [lang('Backoffice.people_err_cni_type')];
        }

        if ($file->getSize() > self::CNI_MAX_KB * 1024) {
            return [lang('Backoffice.people_err_cni_size')];
        }

        return [];
    }

    private function storeCniFile(?UploadedFile $file): ?string
    {
        if ($file === null || ! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        $targetDir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'cni';
        if (! is_dir($targetDir) && ! mkdir($targetDir, 0750, true) && ! is_dir($targetDir)) {
            log_message('error', 'Unable to create CNI upload directory: {dir}', ['dir' => $targetDir]);

            return null;
        }

        $newName = $file->getRandomName();
        if (! $file->move($targetDir, $newName)) {
            log_message('error', 'Unable to move CNI upload to {dir}', ['dir' => $targetDir]);

            return null;
        }

        return 'uploads/cni/' . $newName;
    }

    private function deleteUploadedFile(string $relativePath): void
    {
        $fullPath = $this->resolveCniAbsolutePath($relativePath);
        if ($fullPath !== null) {
            @unlink($fullPath);
        }
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    private function mapWritable(array $input): array
    {
        return [
            'nom_personne'          => trim((string) ($input['nom_personne'] ?? '')),
            'prenom_personne'       => trim((string) ($input['prenom_personne'] ?? '')),
            'sexe_id'               => (int) ($input['sexe_id'] ?? 0),
            'date_naissance'        => trim((string) ($input['date_naissance'] ?? '')),
            'email'                 => trim((string) ($input['email'] ?? '')),
            'telephone'             => trim((string) ($input['telephone'] ?? '')),
            'province_naissance_id' => (int) ($input['province_naissance_id'] ?? 0),
            'commune_naissance_id'  => (int) ($input['commune_naissance_id'] ?? 0),
            'zone_naissance_id'     => (int) ($input['zone_naissance_id'] ?? 0),
            'colline_naissance_id'  => (int) ($input['colline_naissance_id'] ?? 0),
            'numero_cni'            => trim((string) ($input['numero_cni'] ?? '')),
            'adresse_residence'     => trim((string) ($input['adresse_residence'] ?? '')),
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function mapListRow(array $row): array
    {
        $place = array_filter([
            $row['province_naissance_name'] ?? null,
            $row['commune_naissance_name'] ?? null,
            $row['zone_naissance_name'] ?? null,
            $row['colline_naissance_name'] ?? null,
        ], static fn ($v) => $v !== null && $v !== '');

        return [
            'id'           => (int) $row['personne_id'],
            'full_name'    => trim(($row['prenom_personne'] ?? '') . ' ' . ($row['nom_personne'] ?? '')),
            'gender'       => $row['description_sexe'] ?? '—',
            'numero_cni'   => $row['numero_cni'] ?? '',
            'has_cni_file' => ! empty($row['upload_cni']),
            'date_naissance' => $row['date_naissance'] ?? '',
            'email'        => $row['email'] ?? '',
            'telephone'    => $row['telephone'] ?? '',
            'place_of_birth' => $place ? implode(' / ', $place) : '—',
        ];
    }

    private function isValidDate(string $date): bool
    {
        $dt = DateTimeImmutable::createFromFormat('Y-m-d', $date);

        return $dt !== false && $dt->format('Y-m-d') === $date;
    }

    private function isMinimumAge(string $dateOfBirth): bool
    {
        try {
            $dob = new DateTimeImmutable($dateOfBirth);
        } catch (Throwable) {
            return false;
        }

        $today        = new DateTimeImmutable('today');
        $minBirthDate = $today->modify('-' . self::MIN_AGE_YEARS . ' years');

        return $dob <= $minBirthDate;
    }

    private function actorId(): ?int
    {
        $id = session('backoffice_user_id');

        return $id ? (int) $id : null;
    }
}
