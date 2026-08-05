<?php

namespace Modules\CourtJurisdiction\Services;

use Modules\Administration\Models\AuditLogModel;
use Modules\CourtJurisdiction\Models\ConfigurationJuridictionModel;
use Modules\CourtJurisdiction\Models\ConfigurationNiveauJuridictionModel;
use Modules\CourtJurisdiction\Models\JuridictionModel;

class CourtJurisdictionConfigService
{
    private ConfigurationJuridictionModel $configs;
    private ConfigurationNiveauJuridictionModel $levelConfigs;
    private JuridictionModel $courts;
    private AuditLogModel $audit;

    public function __construct(
        ?ConfigurationJuridictionModel $configs = null,
        ?ConfigurationNiveauJuridictionModel $levelConfigs = null,
        ?JuridictionModel $courts = null,
        ?AuditLogModel $audit = null
    ) {
        $this->configs      = $configs ?? new ConfigurationJuridictionModel();
        $this->levelConfigs = $levelConfigs ?? new ConfigurationNiveauJuridictionModel();
        $this->courts       = $courts ?? new JuridictionModel();
        $this->audit        = $audit ?? new AuditLogModel();
    }

    /**
     * @param array<string, mixed> $query
     * @return list<array<string, mixed>>
     */
    public function list(array $query = []): array
    {
        try {
            $rows = $this->configs->listWithRelations([
                'province_id'           => ! empty($query['province_id']) ? (int) $query['province_id'] : null,
                'commune_id'            => ! empty($query['commune_id']) ? (int) $query['commune_id'] : null,
                'niveau_juridiction_id' => ! empty($query['niveau_juridiction_id']) ? (int) $query['niveau_juridiction_id'] : null,
                'is_active'             => $this->parseActive($query['status'] ?? null),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list court jurisdiction configs: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static function (array $row): array {
            $active = filter_var($row['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $child  = trim(($row['code_juridiction'] ?? '') . ' — ' . ($row['nom_juridiction'] ?? ''), ' —');
            $parent = trim(($row['parent_code_juridiction'] ?? '') . ' — ' . ($row['parent_nom_juridiction'] ?? ''), ' —');

            return [
                'id'                    => (int) $row['configuration_juridiction_id'],
                'juridiction_id'        => (int) $row['juridiction_id'],
                'juridiction_parent_id' => (int) ($row['juridiction_parent_id'] ?? 0),
                'court'                 => $child !== '' ? $child : '—',
                'parent_court'          => $parent !== '' ? $parent : '—',
                'province_id'           => $row['province_id'] ?? null,
                'commune_id'            => $row['commune_id'] ?? null,
                'niveau_juridiction_id' => $row['niveau_juridiction_id'] ?? null,
                'parent_province_id'    => $row['parent_province_id'] ?? null,
                'parent_commune_id'     => $row['parent_commune_id'] ?? null,
                'parent_niveau_id'      => $row['parent_niveau_juridiction_id'] ?? null,
                'is_active'             => $active,
                'status'                => $active ? lang('Backoffice.status_active') : lang('Backoffice.status_inactive'),
            ];
        }, $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $row = $this->configs->find($id);
        if (! $row) {
            return null;
        }

        $court = $this->courts->find((int) $row['juridiction_id']);
        $parent = ! empty($row['juridiction_parent_id'])
            ? $this->courts->find((int) $row['juridiction_parent_id'])
            : null;

        return [
            'configuration_juridiction_id' => (int) $row['configuration_juridiction_id'],
            'juridiction_id'               => (int) $row['juridiction_id'],
            'juridiction_parent_id'        => (int) ($row['juridiction_parent_id'] ?? 0),
            'is_active'                    => filter_var($row['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'province_id'                  => $court['province_id'] ?? null,
            'commune_id'                   => $court['commune_id'] ?? null,
            'niveau_juridiction_id'        => $court['niveau_juridiction_id'] ?? null,
            'parent_province_id'           => $parent['province_id'] ?? null,
            'parent_commune_id'            => $parent['commune_id'] ?? null,
            'parent_niveau_juridiction_id' => $parent['niveau_juridiction_id'] ?? null,
        ];
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

        $data = [
            'juridiction_id'        => (int) $input['juridiction_id'],
            'juridiction_parent_id' => (int) $input['juridiction_parent_id'],
            'is_active'             => true,
        ];

        try {
            $id = $this->configs->insert($data, true);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to create court jurisdiction config: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.cjc_err_save')]];
        }

        if (! $id) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cjc_err_save')]];
        }

        $this->audit->record('CREATE', 'juridiction.configuration_juridiction', (int) $id, null, $data, $this->actorId());

        return ['ok' => true, 'id' => (int) $id];
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>}
     */
    public function update(int $id, array $input): array
    {
        $existing = $this->configs->find($id);
        if (! $existing) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cjc_err_not_found')]];
        }

        $errors = $this->validate($input, $id);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $data = [
            'juridiction_id'        => (int) $input['juridiction_id'],
            'juridiction_parent_id' => (int) $input['juridiction_parent_id'],
        ];

        try {
            $this->configs->update($id, $data);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cjc_err_save')]];
        }

        $this->audit->record('UPDATE', 'juridiction.configuration_juridiction', $id, $existing, $data, $this->actorId());

        return ['ok' => true];
    }

    /**
     * @return array{ok:bool,errors?:list<string>,activated?:bool}
     */
    public function toggleStatus(int $id): array
    {
        $row = $this->configs->find($id);
        if (! $row) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cjc_err_not_found')]];
        }

        $isActive   = filter_var($row['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $activating = ! $isActive;

        try {
            $this->configs->update($id, ['is_active' => $activating]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cjc_err_save')]];
        }

        $this->audit->record(
            $activating ? 'ACTIVATE' : 'DEACTIVATE',
            'juridiction.configuration_juridiction',
            $id,
            ['is_active' => $isActive],
            ['is_active' => $activating],
            $this->actorId()
        );

        return ['ok' => true, 'activated' => $activating];
    }

    public function parentLevelFor(int $niveauId): ?int
    {
        return $this->levelConfigs->parentLevelId($niveauId);
    }

    /**
     * @param array<string, mixed> $input
     * @return list<string>
     */
    private function validate(array $input, ?int $ignoreId = null): array
    {
        $errors = [];
        $childId  = (int) ($input['juridiction_id'] ?? 0);
        $parentId = (int) ($input['juridiction_parent_id'] ?? 0);

        if ($childId < 1) {
            $errors[] = lang('Backoffice.cjc_err_required_court');
        }
        if ($parentId < 1) {
            $errors[] = lang('Backoffice.cjc_err_required_parent');
        }
        if ($childId > 0 && $childId === $parentId) {
            $errors[] = lang('Backoffice.cjc_err_self_parent');
        }

        $child  = $childId ? $this->courts->find($childId) : null;
        $parent = $parentId ? $this->courts->find($parentId) : null;

        if ($childId && ! $child) {
            $errors[] = lang('Backoffice.cjc_err_court');
        }
        if ($parentId && ! $parent) {
            $errors[] = lang('Backoffice.cjc_err_parent');
        }

        if ($child && $parent) {
            $expectedParentLevel = $this->levelConfigs->parentLevelId((int) $child['niveau_juridiction_id']);
            if ($expectedParentLevel && (int) $parent['niveau_juridiction_id'] !== $expectedParentLevel) {
                $errors[] = lang('Backoffice.cjc_err_parent_level');
            }
        }

        if ($childId && $parentId && $this->configs->relationshipExists($childId, $parentId, $ignoreId)) {
            $errors[] = lang('Backoffice.cjc_err_duplicate');
        }

        return $errors;
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
