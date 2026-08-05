<?php

namespace Modules\Summons\Services;

use Modules\Administration\Models\AuditLogModel;
use Modules\Summons\Models\StatutConvocationModel;

class SummonsStatusService
{
    private StatutConvocationModel $statuses;
    private AuditLogModel $audit;

    public function __construct(?StatutConvocationModel $statuses = null, ?AuditLogModel $audit = null)
    {
        $this->statuses = $statuses ?? new StatutConvocationModel();
        $this->audit    = $audit ?? new AuditLogModel();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(): array
    {
        try {
            $rows = $this->statuses->listAll();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list summons statuses: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static fn (array $row): array => [
            'id'          => (int) $row['statut_convocation_id'],
            'description' => $row['description_statut_convocation'] ?? '',
        ], $rows);
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
            'description_statut_convocation' => trim((string) $input['description_statut_convocation']),
        ];

        try {
            $id = $this->statuses->insert($data, true);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.sum_st_err_save')]];
        }

        if (! $id) {
            return ['ok' => false, 'errors' => [lang('Backoffice.sum_st_err_save')]];
        }

        $this->audit->record('CREATE', 'convocation.statut_convocation', (int) $id, null, $data, $this->actorId());

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
            return ['ok' => false, 'errors' => [lang('Backoffice.sum_st_err_not_found')]];
        }

        $errors = $this->validate($input);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $data = [
            'description_statut_convocation' => trim((string) $input['description_statut_convocation']),
        ];

        try {
            $this->statuses->update($id, $data);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.sum_st_err_save')]];
        }

        $this->audit->record('UPDATE', 'convocation.statut_convocation', $id, $existing, $data, $this->actorId());

        return ['ok' => true];
    }

    /**
     * @param array<string, mixed> $input
     * @return list<string>
     */
    private function validate(array $input): array
    {
        if (trim((string) ($input['description_statut_convocation'] ?? '')) === '') {
            return [lang('Backoffice.sum_st_err_required')];
        }

        return [];
    }

    private function actorId(): ?int
    {
        $id = session('backoffice_user_id');

        return $id ? (int) $id : null;
    }
}
