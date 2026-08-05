<?php

namespace Modules\Transfer\Services;

use Modules\Administration\Models\AuditLogModel;
use Modules\Transfer\Models\StatutTransfertDossierModel;

class TransferStatusService
{
    private StatutTransfertDossierModel $statuses;
    private AuditLogModel $audit;

    public function __construct(?StatutTransfertDossierModel $statuses = null, ?AuditLogModel $audit = null)
    {
        $this->statuses = $statuses ?? new StatutTransfertDossierModel();
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
            log_message('error', 'Failed to list transfer statuses: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static fn (array $row): array => [
            'id'          => (int) $row['statut_transfert_dossier_id'],
            'description' => $row['description_statut_transfert_dossier'] ?? '',
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
            'description_statut_transfert_dossier' => trim((string) $input['description_statut_transfert_dossier']),
        ];

        try {
            $id = $this->statuses->insert($data, true);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to create transfer status: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.trf_st_err_save')]];
        }

        if (! $id) {
            return ['ok' => false, 'errors' => [lang('Backoffice.trf_st_err_save')]];
        }

        $this->audit->record('CREATE', 'transfert.statut_transfert_dossier', (int) $id, null, $data, $this->actorId());

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
            return ['ok' => false, 'errors' => [lang('Backoffice.trf_st_err_not_found')]];
        }

        $errors = $this->validate($input);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $data = [
            'description_statut_transfert_dossier' => trim((string) $input['description_statut_transfert_dossier']),
        ];

        try {
            $this->statuses->update($id, $data);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to update transfer status {id}: {message}', [
                'id'      => $id,
                'message' => $e->getMessage(),
            ]);

            return ['ok' => false, 'errors' => [lang('Backoffice.trf_st_err_save')]];
        }

        $this->audit->record('UPDATE', 'transfert.statut_transfert_dossier', $id, $existing, $data, $this->actorId());

        return ['ok' => true];
    }

    /**
     * @param array<string, mixed> $input
     * @return list<string>
     */
    private function validate(array $input): array
    {
        $description = trim((string) ($input['description_statut_transfert_dossier'] ?? ''));

        if ($description === '') {
            return [lang('Backoffice.trf_st_err_required')];
        }

        if (mb_strlen($description) > 50) {
            return [lang('Backoffice.trf_st_err_max_length')];
        }

        return [];
    }

    private function actorId(): ?int
    {
        $id = session('backoffice_user_id');

        return $id ? (int) $id : null;
    }
}
