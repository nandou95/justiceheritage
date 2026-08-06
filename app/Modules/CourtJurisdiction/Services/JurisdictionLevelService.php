<?php

namespace Modules\CourtJurisdiction\Services;

use Modules\Administration\Models\AuditLogModel;
use Modules\CourtJurisdiction\Models\NiveauJuridictionModel;

class JurisdictionLevelService
{
    private NiveauJuridictionModel $levels;
    private AuditLogModel $audit;

    public function __construct(?NiveauJuridictionModel $levels = null, ?AuditLogModel $audit = null)
    {
        $this->levels = $levels ?? new NiveauJuridictionModel();
        $this->audit  = $audit ?? new AuditLogModel();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(?bool $isActive = null): array
    {
        try {
            $rows = $this->levels->listFiltered($isActive);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list jurisdiction levels: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static function (array $row): array {
            $active  = db_bool($row['is_active'] ?? false);
            $recours = db_bool($row['is_recours'] ?? false);

            return [
                'id'          => (int) $row['niveau_juridiction_id'],
                'description' => $row['desc_niveau_juridiction'] ?? '',
                'is_recours'  => $recours,
                'is_appeal'   => $recours ? lang('Backoffice.yes') : lang('Backoffice.no'),
                'is_active'   => $active,
                'status'      => $active ? lang('Backoffice.status_active') : lang('Backoffice.status_inactive'),
            ];
        }, $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $row = $this->levels->find($id);

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
            'desc_niveau_juridiction' => trim((string) $input['desc_niveau_juridiction']),
            'is_recours'              => $this->toBool($input['is_recours'] ?? false),
            'is_active'               => true,
        ];

        try {
            $id = $this->levels->insert($data, true);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.jl_err_save')]];
        }

        if (! $id) {
            return ['ok' => false, 'errors' => [lang('Backoffice.jl_err_save')]];
        }

        $this->audit->record('CREATE', 'juridiction.niveau_juridiction', (int) $id, null, $data, $this->actorId());

        return ['ok' => true, 'id' => (int) $id];
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>}
     */
    public function update(int $id, array $input): array
    {
        $existing = $this->levels->find($id);
        if (! $existing) {
            return ['ok' => false, 'errors' => [lang('Backoffice.jl_err_not_found')]];
        }

        $errors = $this->validate($input);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $data = [
            'desc_niveau_juridiction' => trim((string) $input['desc_niveau_juridiction']),
            'is_recours'              => $this->toBool($input['is_recours'] ?? false),
        ];

        try {
            $this->levels->update($id, $data);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.jl_err_save')]];
        }

        $this->audit->record('UPDATE', 'juridiction.niveau_juridiction', $id, $existing, $data, $this->actorId());

        return ['ok' => true];
    }

    /**
     * @return array{ok:bool,errors?:list<string>,activated?:bool}
     */
    public function toggleStatus(int $id): array
    {
        $row = $this->levels->find($id);
        if (! $row) {
            return ['ok' => false, 'errors' => [lang('Backoffice.jl_err_not_found')]];
        }

        $isActive   = db_bool($row['is_active'] ?? false);
        $activating = ! $isActive;

        try {
            $this->levels->update($id, ['is_active' => $activating]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.jl_err_save')]];
        }

        $this->audit->record(
            $activating ? 'ACTIVATE' : 'DEACTIVATE',
            'juridiction.niveau_juridiction',
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
        if (trim((string) ($input['desc_niveau_juridiction'] ?? '')) === '') {
            $errors[] = lang('Backoffice.jl_err_required_description');
        }

        return $errors;
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
