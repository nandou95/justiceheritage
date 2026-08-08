<?php

namespace Modules\Complaint\Services;

use Modules\Administration\Models\AuditLogModel;
use Modules\Administration\Models\ProfilModel;
use Modules\Complaint\Models\EtapePlainteActionModel;
use Modules\Complaint\Models\EtapePlainteModel;
use Modules\Complaint\Models\EtapePlainteProfilModel;
use Modules\CourtJurisdiction\Models\NiveauJuridictionModel;

class ComplaintStageService
{
    private EtapePlainteModel $stages;
    private EtapePlainteProfilModel $stageProfiles;
    private EtapePlainteActionModel $stageActions;
    private ProfilModel $profiles;
    private NiveauJuridictionModel $levels;
    private AuditLogModel $audit;

    public function __construct(
        ?EtapePlainteModel $stages = null,
        ?EtapePlainteProfilModel $stageProfiles = null,
        ?EtapePlainteActionModel $stageActions = null,
        ?ProfilModel $profiles = null,
        ?NiveauJuridictionModel $levels = null,
        ?AuditLogModel $audit = null
    ) {
        $this->stages        = $stages ?? new EtapePlainteModel();
        $this->stageProfiles = $stageProfiles ?? new EtapePlainteProfilModel();
        $this->stageActions  = $stageActions ?? new EtapePlainteActionModel();
        $this->profiles      = $profiles ?? new ProfilModel();
        $this->levels        = $levels ?? new NiveauJuridictionModel();
        $this->audit         = $audit ?? new AuditLogModel();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(?bool $isActive = null, ?int $niveauId = null): array
    {
        try {
            $rows = $this->stages->listWithCounts($isActive, $niveauId);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list complaint stages: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static function (array $row): array {
            $active = db_bool($row['is_active'] ?? false);

            return [
                'id'              => (int) $row['etape_plainte_id'],
                'description'     => $row['description_etape_plainte'] ?? '',
                'level'           => $row['desc_niveau_juridiction'] ?? '—',
                'niveau_id'       => (int) ($row['niveau_juridiction_id'] ?? 0),
                'profiles_count'  => (int) ($row['profiles_count'] ?? 0),
                'actions_count'   => (int) ($row['actions_count'] ?? 0),
                'is_convocation'  => db_bool($row['is_convocation'] ?? false),
                'is_audience'     => db_bool($row['is_audience'] ?? false),
                'is_active'       => $active,
                'status'          => $active ? lang('Backoffice.status_active') : lang('Backoffice.status_inactive'),
            ];
        }, $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $row = $this->stages->find($id);
        if (! $row) {
            return null;
        }

        $row['profil_ids'] = $this->stages->profileIds($id);

        return $row;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function profiles(int $etapeId): array
    {
        try {
            $rows = $this->stages->listProfiles($etapeId);
        } catch (\Throwable $e) {
            return [];
        }

        return array_map(static function (array $row): array {
            $active = db_bool($row['is_active'] ?? false);

            return [
                'code'        => $row['code_profil'] ?? '',
                'name'        => $row['libelle_profil'] ?? '',
                'description' => $row['description_profil'] ?? '—',
                'is_active'   => $active,
                'status'      => $active ? lang('Backoffice.status_active') : lang('Backoffice.status_inactive'),
            ];
        }, $rows);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function actions(int $etapeId): array
    {
        try {
            $rows = $this->stageActions->listForEtape($etapeId);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list stage actions: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static function (array $row): array {
            $active = db_bool($row['is_active'] ?? false);

            return [
                'id'          => (int) $row['etape_plainte_action_id'],
                'description' => $row['desc_etape_plainte_action'] ?? '',
                'is_active'   => $active,
                'status'      => $active ? lang('Backoffice.status_active') : lang('Backoffice.status_inactive'),
            ];
        }, $rows);
    }

    /**
     * @return array{id:int,description:string,is_active:bool,status:string}|null
     */
    public function findAction(int $etapeId, int $actionId): ?array
    {
        $row = $this->stageActions->find($actionId);
        if (! is_array($row) || (int) ($row['etape_plainte_id'] ?? 0) !== $etapeId) {
            return null;
        }

        $active = db_bool($row['is_active'] ?? false);

        return [
            'id'          => (int) $row['etape_plainte_action_id'],
            'description' => (string) ($row['desc_etape_plainte_action'] ?? ''),
            'is_active'   => $active,
            'status'      => $active ? lang('Backoffice.status_active') : lang('Backoffice.status_inactive'),
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>,id?:int}
     */
    public function createAction(int $etapeId, array $input): array
    {
        if (! $this->stages->find($etapeId)) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cs_err_not_found')]];
        }

        $description = trim((string) ($input['desc_etape_plainte_action'] ?? ''));
        if ($description === '') {
            return ['ok' => false, 'errors' => [lang('Backoffice.cs_action_err_description')]];
        }

        try {
            $id = $this->stageActions->insert([
                'etape_plainte_id'          => $etapeId,
                'desc_etape_plainte_action' => $description,
                'is_active'                 => true,
            ], true);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cs_action_err_save')]];
        }

        if (! $id) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cs_action_err_save')]];
        }

        $this->audit->record('CREATE', 'plainte.etape_plainte_action', (int) $id, null, [
            'etape_plainte_id'          => $etapeId,
            'desc_etape_plainte_action' => $description,
        ], $this->actorId());

        return ['ok' => true, 'id' => (int) $id];
    }

    /**
     * @return array{ok:bool,errors?:list<string>,activated?:bool}
     */
    public function toggleAction(int $etapeId, int $actionId): array
    {
        $row = $this->stageActions->find($actionId);
        if (! $row || (int) ($row['etape_plainte_id'] ?? 0) !== $etapeId) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cs_action_err_not_found')]];
        }

        $isActive   = db_bool($row['is_active'] ?? false);
        $activating = ! $isActive;

        try {
            $this->stageActions->update($actionId, ['is_active' => $activating]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cs_action_err_save')]];
        }

        $this->audit->record(
            $activating ? 'ACTIVATE' : 'DEACTIVATE',
            'plainte.etape_plainte_action',
            $actionId,
            ['is_active' => $isActive],
            ['is_active' => $activating],
            $this->actorId()
        );

        return ['ok' => true, 'activated' => $activating];
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>,action?:array{id:int,description:string,is_active:bool,status:string}}
     */
    public function updateAction(int $etapeId, int $actionId, array $input): array
    {
        $row = $this->stageActions->find($actionId);
        if (! $row || (int) ($row['etape_plainte_id'] ?? 0) !== $etapeId) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cs_action_err_not_found')]];
        }

        $description = trim((string) ($input['desc_etape_plainte_action'] ?? ''));
        if ($description === '') {
            return ['ok' => false, 'errors' => [lang('Backoffice.cs_action_err_description')]];
        }

        $isActive = $this->toBool($input['is_active'] ?? false);
        $payload  = [
            'desc_etape_plainte_action' => $description,
            'is_active'                 => $isActive,
        ];

        try {
            $this->stageActions->update($actionId, $payload);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to update stage action {id}: {message}', [
                'id'      => $actionId,
                'message' => $e->getMessage(),
            ]);

            return ['ok' => false, 'errors' => [
                lang('Backoffice.cs_action_err_save') . ' ' . $e->getMessage(),
            ]];
        }

        $this->audit->record('UPDATE', 'plainte.etape_plainte_action', $actionId, $row, $payload, $this->actorId());

        $action = $this->findAction($etapeId, $actionId);
        if ($action === null) {
            $action = [
                'id'          => $actionId,
                'description' => $description,
                'is_active'   => $isActive,
                'status'      => $isActive ? lang('Backoffice.status_active') : lang('Backoffice.status_inactive'),
            ];
        }

        return ['ok' => true, 'action' => $action];
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
        $profilIds = $this->extractProfilIds($input);
        $actions   = $this->extractActions($input);

        $db = db_connect();
        $db->transStart();

        try {
            $id = $this->stages->insert($data, true);
            if (! $id) {
                throw new \RuntimeException('insert failed');
            }
            $etapeId = (int) $id;
            $this->stageProfiles->syncForEtape($etapeId, $profilIds);

            foreach ($actions as $action) {
                $actionId = $this->stageActions->insert([
                    'etape_plainte_id'          => $etapeId,
                    'desc_etape_plainte_action' => $action['desc_etape_plainte_action'],
                    'is_active'                 => $action['is_active'],
                ], true);
                if (! $actionId) {
                    throw new \RuntimeException('action insert failed');
                }
            }

            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Failed to create complaint stage: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.cs_err_save')]];
        }

        if ($db->transStatus() === false) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cs_err_save')]];
        }

        $this->audit->record(
            'CREATE',
            'plainte.etape_plainte',
            (int) $id,
            null,
            $data + ['profil_ids' => $profilIds, 'actions' => $actions],
            $this->actorId()
        );

        return ['ok' => true, 'id' => (int) $id];
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>}
     */
    public function update(int $id, array $input): array
    {
        $existing = $this->stages->find($id);
        if (! $existing) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cs_err_not_found')]];
        }

        $errors = $this->validate($input, $id);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $data      = $this->mapWritable($input);
        $profilIds = $this->extractProfilIds($input);

        $db = db_connect();
        $db->transStart();

        try {
            $this->stages->update($id, $data);
            $this->stageProfiles->syncForEtape($id, $profilIds);
            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Failed to update complaint stage: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.cs_err_save')]];
        }

        if ($db->transStatus() === false) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cs_err_save')]];
        }

        $this->audit->record('UPDATE', 'plainte.etape_plainte', $id, $existing, $data + ['profil_ids' => $profilIds], $this->actorId());

        return ['ok' => true];
    }

    /**
     * @return array{ok:bool,errors?:list<string>,activated?:bool}
     */
    public function toggleStatus(int $id): array
    {
        $row = $this->stages->find($id);
        if (! $row) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cs_err_not_found')]];
        }

        $isActive   = db_bool($row['is_active'] ?? false);
        $activating = ! $isActive;

        try {
            $this->stages->update($id, ['is_active' => $activating]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cs_err_save')]];
        }

        $this->audit->record(
            $activating ? 'ACTIVATE' : 'DEACTIVATE',
            'plainte.etape_plainte',
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
    private function validate(array $input, ?int $etapeId = null): array
    {
        $errors = [];
        if (trim((string) ($input['description_etape_plainte'] ?? '')) === '') {
            $errors[] = lang('Backoffice.cs_err_required_description');
        }

        $niveauId = (int) ($input['niveau_juridiction_id'] ?? 0);
        if ($niveauId < 1 || ! $this->levels->find($niveauId)) {
            $errors[] = lang('Backoffice.cs_err_level');
        }

        $profilIds = $this->extractProfilIds($input);
        if ($profilIds === []) {
            $errors[] = lang('Backoffice.cs_err_profiles');
        } else {
            $alreadyAssigned = $etapeId ? $this->stages->profileIds($etapeId) : [];
            foreach ($profilIds as $profilId) {
                if ($this->profiles->isActiveProfile($profilId)) {
                    continue;
                }
                // Keep previously assigned profiles selectable on edit even if deactivated later.
                if (in_array($profilId, $alreadyAssigned, true) && $this->profiles->find($profilId)) {
                    continue;
                }
                $errors[] = lang('Backoffice.cs_err_profile_invalid');
                break;
            }
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
            'description_etape_plainte' => trim((string) ($input['description_etape_plainte'] ?? '')),
            'niveau_juridiction_id'     => (int) ($input['niveau_juridiction_id'] ?? 0),
            'is_convocation'            => $this->toBool($input['is_convocation'] ?? false),
            'is_audience'               => $this->toBool($input['is_audience'] ?? false),
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return list<array{desc_etape_plainte_action:string,is_active:bool}>
     */
    private function extractActions(array $input): array
    {
        $raw = $input['actions'] ?? [];
        if (! is_array($raw)) {
            return [];
        }

        $actions = [];
        foreach ($raw as $row) {
            if (! is_array($row)) {
                continue;
            }

            $description = trim((string) ($row['desc_etape_plainte_action'] ?? ''));
            if ($description === '') {
                continue;
            }

            $actions[] = [
                'desc_etape_plainte_action' => $description,
                'is_active'                 => array_key_exists('is_active', $row)
                    ? $this->toBool($row['is_active'])
                    : true,
            ];
        }

        return $actions;
    }

    /**
     * @param array<string, mixed> $input
     * @return list<int>
     */
    private function extractProfilIds(array $input): array
    {
        $raw = $input['profil_ids'] ?? [];
        if (! is_array($raw)) {
            $raw = [$raw];
        }

        return array_values(array_unique(array_filter(array_map('intval', $raw), static fn (int $id): bool => $id > 0)));
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
