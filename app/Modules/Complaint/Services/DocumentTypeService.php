<?php

namespace Modules\Complaint\Services;

use Modules\Administration\Models\AuditLogModel;
use Modules\Complaint\Models\TypeDocumentModel;
use Modules\CourtJurisdiction\Models\NiveauJuridictionModel;

class DocumentTypeService
{
    private TypeDocumentModel $types;
    private NiveauJuridictionModel $levels;
    private AuditLogModel $audit;

    public function __construct(
        ?TypeDocumentModel $types = null,
        ?NiveauJuridictionModel $levels = null,
        ?AuditLogModel $audit = null
    ) {
        $this->types  = $types ?? new TypeDocumentModel();
        $this->levels = $levels ?? new NiveauJuridictionModel();
        $this->audit  = $audit ?? new AuditLogModel();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(?int $niveauId = null, ?bool $isActive = null): array
    {
        try {
            $rows = $this->types->listFiltered($niveauId, $isActive);
        } catch (\Throwable $e) {
            return [];
        }

        return array_map(static function (array $row): array {
            $active = db_bool($row['is_actif'] ?? false);

            return [
                'id'             => (int) $row['type_document_id'],
                'code'           => $row['code_type_document'] ?? '',
                'description'    => $row['libelle_type_document'] ?? '',
                'level'          => $row['desc_niveau_juridiction'] ?? '—',
                'niveau_id'      => (int) ($row['niveau_juridiction_id'] ?? 0),
                'is_obligatoire' => db_bool($row['is_obligatoire'] ?? false),
                'is_active'      => $active,
                'status'         => $active ? lang('Backoffice.status_active') : lang('Backoffice.status_inactive'),
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

        $data = $this->mapWritable($input) + [
            'is_actif'   => true,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        try {
            $id = $this->types->insert($data, true);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.dt_err_save')]];
        }

        if (! $id) {
            return ['ok' => false, 'errors' => [lang('Backoffice.dt_err_save')]];
        }

        $this->audit->record('CREATE', 'plainte.type_document', (int) $id, null, $data, $this->actorId());

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
            return ['ok' => false, 'errors' => [lang('Backoffice.dt_err_not_found')]];
        }

        $errors = $this->validate($input, $id);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $data = $this->mapWritable($input);

        try {
            $this->types->update($id, $data);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.dt_err_save')]];
        }

        $this->audit->record('UPDATE', 'plainte.type_document', $id, $existing, $data, $this->actorId());

        return ['ok' => true];
    }

    /**
     * @return array{ok:bool,errors?:list<string>,activated?:bool}
     */
    public function toggleStatus(int $id): array
    {
        $row = $this->types->find($id);
        if (! $row) {
            return ['ok' => false, 'errors' => [lang('Backoffice.dt_err_not_found')]];
        }

        $isActive   = db_bool($row['is_actif'] ?? false);
        $activating = ! $isActive;

        try {
            $this->types->update($id, ['is_actif' => $activating]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.dt_err_save')]];
        }

        $this->audit->record(
            $activating ? 'ACTIVATE' : 'DEACTIVATE',
            'plainte.type_document',
            $id,
            ['is_actif' => $isActive],
            ['is_actif' => $activating],
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
        $code   = trim((string) ($input['code_type_document'] ?? ''));
        $label  = trim((string) ($input['libelle_type_document'] ?? ''));
        $niveau = (int) ($input['niveau_juridiction_id'] ?? 0);

        if ($code === '') {
            $errors[] = lang('Backoffice.dt_err_code');
        } elseif ($this->types->codeExists($code, $ignoreId)) {
            $errors[] = lang('Backoffice.dt_err_code_taken');
        }

        if ($label === '') {
            $errors[] = lang('Backoffice.dt_err_label');
        }

        if ($niveau < 1 || ! $this->levels->find($niveau)) {
            $errors[] = lang('Backoffice.dt_err_level');
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
            'code_type_document'    => trim((string) ($input['code_type_document'] ?? '')),
            'libelle_type_document' => trim((string) ($input['libelle_type_document'] ?? '')),
            'niveau_juridiction_id' => (int) ($input['niveau_juridiction_id'] ?? 0),
            'is_obligatoire'        => $this->toBool($input['is_obligatoire'] ?? false),
        ];
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
