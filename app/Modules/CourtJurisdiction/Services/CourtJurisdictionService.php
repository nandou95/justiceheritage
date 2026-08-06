<?php

namespace Modules\CourtJurisdiction\Services;

use Modules\Administration\Models\AuditLogModel;
use Modules\CourtJurisdiction\Models\CollineModel;
use Modules\CourtJurisdiction\Models\CommuneModel;
use Modules\CourtJurisdiction\Models\JuridictionModel;
use Modules\CourtJurisdiction\Models\NiveauJuridictionModel;
use Modules\CourtJurisdiction\Models\ProvinceModel;
use Modules\CourtJurisdiction\Models\ZoneModel;

class CourtJurisdictionService
{
    private JuridictionModel $courts;
    private NiveauJuridictionModel $levels;
    private AuditLogModel $audit;

    public function __construct(
        ?JuridictionModel $courts = null,
        ?NiveauJuridictionModel $levels = null,
        ?AuditLogModel $audit = null
    ) {
        $this->courts = $courts ?? new JuridictionModel();
        $this->levels = $levels ?? new NiveauJuridictionModel();
        $this->audit  = $audit ?? new AuditLogModel();
    }

    /**
     * @param array<string, mixed> $query
     * @return list<array<string, mixed>>
     */
    public function list(array $query = []): array
    {
        try {
            $rows = $this->courts->listWithRelations([
                'province_id'           => ! empty($query['province_id']) ? (int) $query['province_id'] : null,
                'commune_id'            => ! empty($query['commune_id']) ? (int) $query['commune_id'] : null,
                'niveau_juridiction_id' => ! empty($query['niveau_juridiction_id']) ? (int) $query['niveau_juridiction_id'] : null,
                'is_active'             => $this->parseActive($query['status'] ?? null),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list court jurisdictions: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map([$this, 'mapListRow'], $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        try {
            $row = $this->courts->findWithRelations($id);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load court jurisdiction {id}: {message}', [
                'id' => $id, 'message' => $e->getMessage(),
            ]);

            return null;
        }

        return $row ? $this->mapDetailRow($row) : null;
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

        $data = $this->mapWritable($input) + [
            'is_active'  => true,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        try {
            $id = $this->courts->insert($data, true);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to create court jurisdiction: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.cj_err_save')]];
        }

        if (! $id) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cj_err_save')]];
        }

        $this->audit->record('CREATE', 'juridiction.juridiction', (int) $id, null, $data, $this->actorId());

        return ['ok' => true, 'id' => (int) $id];
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>}
     */
    public function update(int $id, array $input): array
    {
        $existing = $this->courts->find($id);
        if (! $existing) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cj_err_not_found')]];
        }

        $errors = $this->validate($input, $id);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $data = $this->mapWritable($input);

        try {
            $this->courts->update($id, $data);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to update court jurisdiction {id}: {message}', [
                'id' => $id, 'message' => $e->getMessage(),
            ]);

            return ['ok' => false, 'errors' => [lang('Backoffice.cj_err_save')]];
        }

        $this->audit->record('UPDATE', 'juridiction.juridiction', $id, $existing, $data, $this->actorId());

        return ['ok' => true];
    }

    /**
     * @return array{ok:bool,errors?:list<string>,activated?:bool}
     */
    public function toggleStatus(int $id): array
    {
        $row = $this->courts->find($id);
        if (! $row) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cj_err_not_found')]];
        }

        $isActive   = db_bool($row['is_active'] ?? false);
        $activating = ! $isActive;

        try {
            $this->courts->update($id, ['is_active' => $activating]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cj_err_save')]];
        }

        $this->audit->record(
            $activating ? 'ACTIVATE' : 'DEACTIVATE',
            'juridiction.juridiction',
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
    private function validate(array $input, ?int $ignoreId = null): array
    {
        $errors = [];
        $required = [
            'code_juridiction'      => lang('Backoffice.cj_field_code'),
            'nom_juridiction'       => lang('Backoffice.cj_field_name'),
            'niveau_juridiction_id' => lang('Backoffice.cj_field_level'),
            'adresse'               => lang('Backoffice.cj_field_address'),
            'telephone'             => lang('Backoffice.cj_field_phone'),
            'email'                 => lang('Backoffice.cj_field_email'),
            'province_id'           => lang('Backoffice.cj_field_province'),
            'commune_id'            => lang('Backoffice.cj_field_commune'),
            'zone_id'               => lang('Backoffice.cj_field_zone'),
            'colline_id'            => lang('Backoffice.cj_field_colline'),
        ];

        foreach ($required as $key => $label) {
            if (trim((string) ($input[$key] ?? '')) === '') {
                $errors[] = lang('Backoffice.cj_err_required', [$label]);
            }
        }

        $code = trim((string) ($input['code_juridiction'] ?? ''));
        if ($code !== '' && $this->courts->codeExists($code, $ignoreId)) {
            $errors[] = lang('Backoffice.cj_err_code_taken');
        }

        $email = trim((string) ($input['email'] ?? ''));
        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = lang('Backoffice.cj_err_email');
        }

        if (! empty($input['niveau_juridiction_id']) && ! $this->levels->find((int) $input['niveau_juridiction_id'])) {
            $errors[] = lang('Backoffice.cj_err_level');
        }

        $provinceId = (int) ($input['province_id'] ?? 0);
        $communeId  = (int) ($input['commune_id'] ?? 0);
        $zoneId     = (int) ($input['zone_id'] ?? 0);
        $collineId  = (int) ($input['colline_id'] ?? 0);

        if ($provinceId && ! (new ProvinceModel())->find($provinceId)) {
            $errors[] = lang('Backoffice.cj_err_province');
        }
        if ($communeId) {
            $commune = (new CommuneModel())->find($communeId);
            if (! $commune || (int) ($commune['province_id'] ?? 0) !== $provinceId) {
                $errors[] = lang('Backoffice.cj_err_commune');
            }
        }
        if ($zoneId) {
            $zone = (new ZoneModel())->find($zoneId);
            if (! $zone || (int) ($zone['commune_id'] ?? 0) !== $communeId) {
                $errors[] = lang('Backoffice.cj_err_zone');
            }
        }
        if ($collineId) {
            $colline = (new CollineModel())->find($collineId);
            if (! $colline || (int) ($colline['zone_id'] ?? 0) !== $zoneId) {
                $errors[] = lang('Backoffice.cj_err_colline');
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
            'code_juridiction'      => trim((string) ($input['code_juridiction'] ?? '')),
            'nom_juridiction'       => trim((string) ($input['nom_juridiction'] ?? '')),
            'niveau_juridiction_id' => (int) ($input['niveau_juridiction_id'] ?? 0),
            'adresse'               => trim((string) ($input['adresse'] ?? '')),
            'telephone'             => trim((string) ($input['telephone'] ?? '')),
            'email'                 => trim((string) ($input['email'] ?? '')),
            'province_id'           => (int) ($input['province_id'] ?? 0),
            'commune_id'            => (int) ($input['commune_id'] ?? 0),
            'zone_id'               => (int) ($input['zone_id'] ?? 0),
            'colline_id'            => (int) ($input['colline_id'] ?? 0),
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function mapListRow(array $row): array
    {
        $active = db_bool($row['is_active'] ?? false);
        $addressParts = array_filter([
            $row['province_name'] ?? null,
            $row['commune_name'] ?? null,
            $row['zone_name'] ?? null,
            $row['colline_name'] ?? null,
            $row['adresse'] ?? null,
        ]);

        return [
            'id'           => (int) $row['juridiction_id'],
            'code'         => $row['code_juridiction'] ?? '',
            'name'         => $row['nom_juridiction'] ?? '',
            'level'        => $row['desc_niveau_juridiction'] ?? '—',
            'phone'        => $row['telephone'] ?? '',
            'email'        => $row['email'] ?? '',
            'address'      => $addressParts ? implode(' / ', $addressParts) : '—',
            'is_active'    => $active,
            'status'       => $active ? lang('Backoffice.status_active') : lang('Backoffice.status_inactive'),
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function mapDetailRow(array $row): array
    {
        $active = db_bool($row['is_active'] ?? false);

        return $row + [
            'is_active' => $active,
            'status'    => $active ? lang('Backoffice.status_active') : lang('Backoffice.status_inactive'),
        ];
    }

    private function parseActive(mixed $status): ?bool
    {
        if ($status === '1' || $status === 'true') {
            return true;
        }
        if ($status === '0' || $status === 'false') {
            return false;
        }

        return null;
    }

    private function actorId(): ?int
    {
        $id = session('backoffice_user_id');

        return $id ? (int) $id : null;
    }
}
