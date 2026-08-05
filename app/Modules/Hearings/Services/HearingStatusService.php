<?php

namespace Modules\Hearings\Services;

use Modules\Administration\Models\AuditLogModel;
use Modules\Hearings\Models\StatutAudienceModel;

class HearingStatusService
{
    private StatutAudienceModel $statuses;
    private AuditLogModel $audit;

    public function __construct(?StatutAudienceModel $statuses = null, ?AuditLogModel $audit = null)
    {
        $this->statuses = $statuses ?? new StatutAudienceModel();
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
            log_message('error', 'Failed to list hearing statuses: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static fn (array $row): array => [
            'id'          => (int) $row['statut_audience_id'],
            'description' => $row['description_statut_audience'] ?? '',
        ], $rows);
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>,id?:int}
     */
    public function create(array $input): array
    {
        if (trim((string) ($input['description_statut_audience'] ?? '')) === '') {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_st_err_required')]];
        }

        $data = ['description_statut_audience' => trim((string) $input['description_statut_audience'])];

        try {
            $id = $this->statuses->insert($data, true);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_st_err_save')]];
        }

        if (! $id) {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_st_err_save')]];
        }

        $this->audit->record('CREATE', 'audience.statut_audience', (int) $id, null, $data, $this->actorId());

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
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_st_err_not_found')]];
        }

        if (trim((string) ($input['description_statut_audience'] ?? '')) === '') {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_st_err_required')]];
        }

        $data = ['description_statut_audience' => trim((string) $input['description_statut_audience'])];

        try {
            $this->statuses->update($id, $data);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_st_err_save')]];
        }

        $this->audit->record('UPDATE', 'audience.statut_audience', $id, $existing, $data, $this->actorId());

        return ['ok' => true];
    }

    private function actorId(): ?int
    {
        $id = session('backoffice_user_id');

        return $id ? (int) $id : null;
    }
}
