<?php

namespace Modules\Complaint\Services;

use Modules\Administration\Models\AuditLogModel;
use Modules\Complaint\Models\StatutPlainteModel;

class ComplaintStatusService
{
    private StatutPlainteModel $statuses;
    private AuditLogModel $audit;

    public function __construct(?StatutPlainteModel $statuses = null, ?AuditLogModel $audit = null)
    {
        $this->statuses = $statuses ?? new StatutPlainteModel();
        $this->audit    = $audit ?? new AuditLogModel();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(?bool $isActive = null): array
    {
        try {
            $rows = $this->statuses->listFiltered($isActive);
        } catch (\Throwable $e) {
            return [];
        }

        return array_map(static function (array $row): array {
            $active = filter_var($row['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);

            return [
                'id'          => (int) $row['statut_plainte_id'],
                'description' => $row['description_statut_plainte'] ?? '',
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

        $data = [
            'description_statut_plainte' => trim((string) $input['description_statut_plainte']),
            'is_active'                  => true,
        ];

        try {
            $id = $this->statuses->insert($data, true);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cst_err_save')]];
        }

        if (! $id) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cst_err_save')]];
        }

        $this->audit->record('CREATE', 'plainte.statut_plainte', (int) $id, null, $data, $this->actorId());

        return ['ok' => true, 'id' => (int) $id];
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>}
     */
    public function update(int $id, array $input): array
    {
        $existing = $this->statuses->find($id);
        if (! $existing) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cst_err_not_found')]];
        }

        $errors = $this->validate($input);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $data = ['description_statut_plainte' => trim((string) $input['description_statut_plainte'])];

        try {
            $this->statuses->update($id, $data);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cst_err_save')]];
        }

        $this->audit->record('UPDATE', 'plainte.statut_plainte', $id, $existing, $data, $this->actorId());

        return ['ok' => true];
    }

    /**
     * @return array{ok:bool,errors?:list<string>,activated?:bool}
     */
    public function toggleStatus(int $id): array
    {
        $row = $this->statuses->find($id);
        if (! $row) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cst_err_not_found')]];
        }

        $isActive   = filter_var($row['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $activating = ! $isActive;

        try {
            $this->statuses->update($id, ['is_active' => $activating]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cst_err_save')]];
        }

        $this->audit->record(
            $activating ? 'ACTIVATE' : 'DEACTIVATE',
            'plainte.statut_plainte',
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
        if (trim((string) ($input['description_statut_plainte'] ?? '')) === '') {
            return [lang('Backoffice.cst_err_required')];
        }

        return [];
    }

    private function actorId(): ?int
    {
        $id = session('backoffice_user_id');

        return $id ? (int) $id : null;
    }
}
