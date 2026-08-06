<?php

namespace Modules\CourtJurisdiction\Services;

use Modules\Administration\Models\AuditLogModel;
use Modules\CourtJurisdiction\Models\ConfigurationNiveauJuridictionModel;
use Modules\CourtJurisdiction\Models\NiveauJuridictionModel;

class JurisdictionLevelConfigService
{
    private ConfigurationNiveauJuridictionModel $configs;
    private NiveauJuridictionModel $levels;
    private AuditLogModel $audit;

    public function __construct(
        ?ConfigurationNiveauJuridictionModel $configs = null,
        ?NiveauJuridictionModel $levels = null,
        ?AuditLogModel $audit = null
    ) {
        $this->configs = $configs ?? new ConfigurationNiveauJuridictionModel();
        $this->levels  = $levels ?? new NiveauJuridictionModel();
        $this->audit   = $audit ?? new AuditLogModel();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(?bool $isActive = null): array
    {
        try {
            $rows = $this->configs->listWithRelations($isActive);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list jurisdiction level configs: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static function (array $row): array {
            $active = db_bool($row['is_active'] ?? false);

            return [
                'id'                           => (int) $row['configuration_niveau_juridiction_id'],
                'niveau_juridiction_id'        => (int) $row['niveau_juridiction_id'],
                'niveau_juridiction_parent_id' => (int) ($row['niveau_juridiction_parent_id'] ?? 0),
                'level'                        => $row['desc_niveau_juridiction'] ?? '—',
                'parent_level'                 => $row['parent_desc_niveau'] ?? '—',
                'is_active'                    => $active,
                'status'                       => $active ? lang('Backoffice.status_active') : lang('Backoffice.status_inactive'),
            ];
        }, $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $row = $this->configs->find($id);

        return $row ?: null;
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
            'niveau_juridiction_id'        => (int) $input['niveau_juridiction_id'],
            'niveau_juridiction_parent_id' => (int) $input['niveau_juridiction_parent_id'],
            'is_active'                    => true,
        ];

        try {
            $id = $this->configs->insert($data, true);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.jlc_err_save')]];
        }

        if (! $id) {
            return ['ok' => false, 'errors' => [lang('Backoffice.jlc_err_save')]];
        }

        $this->audit->record('CREATE', 'juridiction.configuration_niveau_juridiction', (int) $id, null, $data, $this->actorId());

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
            return ['ok' => false, 'errors' => [lang('Backoffice.jlc_err_not_found')]];
        }

        $errors = $this->validate($input, $id);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $data = [
            'niveau_juridiction_id'        => (int) $input['niveau_juridiction_id'],
            'niveau_juridiction_parent_id' => (int) $input['niveau_juridiction_parent_id'],
        ];

        try {
            $this->configs->update($id, $data);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.jlc_err_save')]];
        }

        $this->audit->record('UPDATE', 'juridiction.configuration_niveau_juridiction', $id, $existing, $data, $this->actorId());

        return ['ok' => true];
    }

    /**
     * @return array{ok:bool,errors?:list<string>,activated?:bool}
     */
    public function toggleStatus(int $id): array
    {
        $row = $this->configs->find($id);
        if (! $row) {
            return ['ok' => false, 'errors' => [lang('Backoffice.jlc_err_not_found')]];
        }

        $isActive   = db_bool($row['is_active'] ?? false);
        $activating = ! $isActive;

        try {
            $this->configs->update($id, ['is_active' => $activating]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.jlc_err_save')]];
        }

        $this->audit->record(
            $activating ? 'ACTIVATE' : 'DEACTIVATE',
            'juridiction.configuration_niveau_juridiction',
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
        $errors   = [];
        $levelId  = (int) ($input['niveau_juridiction_id'] ?? 0);
        $parentId = (int) ($input['niveau_juridiction_parent_id'] ?? 0);

        if ($levelId < 1) {
            $errors[] = lang('Backoffice.jlc_err_required_level');
        }
        if ($parentId < 1) {
            $errors[] = lang('Backoffice.jlc_err_required_parent');
        }
        if ($levelId > 0 && $levelId === $parentId) {
            $errors[] = lang('Backoffice.jlc_err_self_parent');
        }
        if ($levelId && ! $this->levels->find($levelId)) {
            $errors[] = lang('Backoffice.jlc_err_level');
        }
        if ($parentId && ! $this->levels->find($parentId)) {
            $errors[] = lang('Backoffice.jlc_err_parent');
        }
        if ($levelId && $parentId && $this->configs->relationshipExists($levelId, $parentId, $ignoreId)) {
            $errors[] = lang('Backoffice.jlc_err_duplicate');
        }

        return $errors;
    }

    private function actorId(): ?int
    {
        $id = session('backoffice_user_id');

        return $id ? (int) $id : null;
    }
}
