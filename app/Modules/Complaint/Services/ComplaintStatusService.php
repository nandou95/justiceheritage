<?php

namespace Modules\Complaint\Services;

use Modules\Administration\Models\AuditLogModel;
use Modules\Complaint\Models\StatutPlainteModel;
use Modules\CourtJurisdiction\Models\NiveauJuridictionModel;

class ComplaintStatusService
{
    private StatutPlainteModel $statuses;
    private NiveauJuridictionModel $levels;
    private AuditLogModel $audit;

    public function __construct(
        ?StatutPlainteModel $statuses = null,
        ?NiveauJuridictionModel $levels = null,
        ?AuditLogModel $audit = null
    ) {
        $this->statuses = $statuses ?? new StatutPlainteModel();
        $this->levels   = $levels ?? new NiveauJuridictionModel();
        $this->audit    = $audit ?? new AuditLogModel();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(?int $niveauId = null, ?bool $isActive = null): array
    {
        try {
            $rows = $this->statuses->listFiltered($niveauId, $isActive);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list complaint statuses: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static function (array $row): array {
            $active = db_bool($row['is_active'] ?? false);

            return [
                'id'          => (int) $row['statut_plainte_id'],
                'description' => $row['description_statut_plainte'] ?? '',
                'level'       => $row['desc_niveau_juridiction'] ?? '—',
                'niveau_id'   => (int) ($row['niveau_juridiction_id'] ?? 0),
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

        $data = $this->mapWritable($input) + ['is_active' => true];

        try {
            $id = $this->statuses->insert($data, true);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to create complaint status: {message}', ['message' => $e->getMessage()]);

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

        $errors = $this->validate($input, $id);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $data = $this->mapWritable($input);

        try {
            $this->statuses->update($id, $data);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to update complaint status: {message}', ['message' => $e->getMessage()]);

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

        $isActive   = db_bool($row['is_active'] ?? false);
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
    private function validate(array $input, ?int $ignoreId = null): array
    {
        $errors      = [];
        $description = trim((string) ($input['description_statut_plainte'] ?? ''));
        $niveauId    = (int) ($input['niveau_juridiction_id'] ?? 0);

        if ($description === '') {
            $errors[] = lang('Backoffice.cst_err_required');
        }

        if ($niveauId < 1 || ! $this->isActiveLevel($niveauId)) {
            $errors[] = lang('Backoffice.cst_err_level');
        } elseif ($description !== '' && $this->statuses->descriptionExists($description, $niveauId, $ignoreId)) {
            $errors[] = lang('Backoffice.cst_err_duplicate');
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
            'description_statut_plainte' => trim((string) ($input['description_statut_plainte'] ?? '')),
            'niveau_juridiction_id'      => (int) ($input['niveau_juridiction_id'] ?? 0),
        ];
    }

    private function isActiveLevel(int $niveauId): bool
    {
        $row = $this->levels->builder()
            ->select('niveau_juridiction_id')
            ->where('niveau_juridiction_id', $niveauId)
            ->where('(is_active IS NULL OR is_active = TRUE)', null, false)
            ->get(1)
            ->getRowArray();

        return is_array($row);
    }

    private function actorId(): ?int
    {
        $id = session('backoffice_user_id');

        return $id ? (int) $id : null;
    }
}
