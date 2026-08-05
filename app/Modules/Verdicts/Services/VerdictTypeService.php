<?php

namespace Modules\Verdicts\Services;

use Modules\Administration\Models\AuditLogModel;
use Modules\Verdicts\Models\TypeVerdictModel;

class VerdictTypeService
{
    private TypeVerdictModel $types;
    private AuditLogModel $audit;

    public function __construct(?TypeVerdictModel $types = null, ?AuditLogModel $audit = null)
    {
        $this->types = $types ?? new TypeVerdictModel();
        $this->audit = $audit ?? new AuditLogModel();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(): array
    {
        try {
            $rows = $this->types->listAll();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list verdict types: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static fn (array $row): array => [
            'id'          => (int) $row['type_verdict_id'],
            'description' => $row['description_type_verdict'] ?? '',
        ], $rows);
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>,id?:int}
     */
    public function create(array $input): array
    {
        if (trim((string) ($input['description_type_verdict'] ?? '')) === '') {
            return ['ok' => false, 'errors' => [lang('Backoffice.vrd_type_err_required')]];
        }

        $data = ['description_type_verdict' => trim((string) $input['description_type_verdict'])];

        try {
            $id = $this->types->insert($data, true);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.vrd_type_err_save')]];
        }

        if (! $id) {
            return ['ok' => false, 'errors' => [lang('Backoffice.vrd_type_err_save')]];
        }

        $this->audit->record('CREATE', 'verdict.type_verdict', (int) $id, null, $data, $this->actorId());

        return ['ok' => true, 'id' => (int) $id];
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>}
     */
    public function update(int $id, array $input): array
    {
        $existing = $this->types->find($id);
        if (! $existing) {
            return ['ok' => false, 'errors' => [lang('Backoffice.vrd_type_err_not_found')]];
        }

        if (trim((string) ($input['description_type_verdict'] ?? '')) === '') {
            return ['ok' => false, 'errors' => [lang('Backoffice.vrd_type_err_required')]];
        }

        $data = ['description_type_verdict' => trim((string) $input['description_type_verdict'])];

        try {
            $this->types->update($id, $data);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.vrd_type_err_save')]];
        }

        $this->audit->record('UPDATE', 'verdict.type_verdict', $id, $existing, $data, $this->actorId());

        return ['ok' => true];
    }

    private function actorId(): ?int
    {
        $id = session('backoffice_user_id');

        return $id ? (int) $id : null;
    }
}
