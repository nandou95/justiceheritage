<?php

namespace Modules\Complaint\Services;

use Modules\Administration\Models\AuditLogModel;
use Modules\Complaint\Models\ConfigurationEtapePlainteModel;
use Modules\Complaint\Models\EtapePlainteActionModel;
use Modules\Complaint\Models\EtapePlainteModel;
use Modules\CourtJurisdiction\Models\NiveauJuridictionModel;

class ComplaintStageConfigService
{
    private ConfigurationEtapePlainteModel $configs;
    private EtapePlainteModel $stages;
    private EtapePlainteActionModel $actions;
    private NiveauJuridictionModel $levels;
    private AuditLogModel $audit;

    public function __construct(
        ?ConfigurationEtapePlainteModel $configs = null,
        ?EtapePlainteModel $stages = null,
        ?EtapePlainteActionModel $actions = null,
        ?NiveauJuridictionModel $levels = null,
        ?AuditLogModel $audit = null
    ) {
        $this->configs = $configs ?? new ConfigurationEtapePlainteModel();
        $this->stages  = $stages ?? new EtapePlainteModel();
        $this->actions = $actions ?? new EtapePlainteActionModel();
        $this->levels  = $levels ?? new NiveauJuridictionModel();
        $this->audit   = $audit ?? new AuditLogModel();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(?int $niveauActuelId = null, ?bool $isActive = null): array
    {
        try {
            $rows = $this->configs->listFiltered($niveauActuelId, $isActive);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list stage configs: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static function (array $row): array {
            $active = db_bool($row['is_active'] ?? false);

            return [
                'id'                => (int) $row['configuration_etape_plainte_id'],
                'etape_actuel_id'   => (int) $row['etape_plainte_actuel_id'],
                'etape_suivant_id'  => (int) $row['etape_plainte_suivant_id'],
                'action_id'         => (int) ($row['etape_plainte_action_id'] ?? 0),
                'etape_actuel'      => $row['etape_actuel'] ?? '',
                'etape_suivant'     => $row['etape_suivant'] ?? '',
                'action_actuel'     => $row['action_actuel'] ?? '—',
                'niveau_actuel_id'  => (int) ($row['niveau_actuel_id'] ?? 0),
                'niveau_suivant_id' => (int) ($row['niveau_suivant_id'] ?? 0),
                'niveau_actuel'     => $row['niveau_actuel'] ?? '—',
                'niveau_suivant'    => $row['niveau_suivant'] ?? '—',
                'url_route'         => $row['url_route'] ?? '',
                'is_active'         => $active,
                'status'            => $active ? lang('Backoffice.status_active') : lang('Backoffice.status_inactive'),
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
            $id = $this->configs->insert($data, true);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to create stage config: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.csc_err_save')]];
        }

        if (! $id) {
            return ['ok' => false, 'errors' => [lang('Backoffice.csc_err_save')]];
        }

        $this->audit->record('CREATE', 'plainte.configuration_etape_plainte', (int) $id, null, $data, $this->actorId());

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
            return ['ok' => false, 'errors' => [lang('Backoffice.csc_err_not_found')]];
        }

        $errors = $this->validate($input, $id);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $data = $this->mapWritable($input);

        try {
            $this->configs->update($id, $data);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.csc_err_save')]];
        }

        $this->audit->record('UPDATE', 'plainte.configuration_etape_plainte', $id, $existing, $data, $this->actorId());

        return ['ok' => true];
    }

    /**
     * @return array{ok:bool,errors?:list<string>,activated?:bool}
     */
    public function toggleStatus(int $id): array
    {
        $row = $this->configs->find($id);
        if (! $row) {
            return ['ok' => false, 'errors' => [lang('Backoffice.csc_err_not_found')]];
        }

        $isActive   = db_bool($row['is_active'] ?? false);
        $activating = ! $isActive;

        try {
            $this->configs->update($id, ['is_active' => $activating]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.csc_err_save')]];
        }

        $this->audit->record(
            $activating ? 'ACTIVATE' : 'DEACTIVATE',
            'plainte.configuration_etape_plainte',
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
        $errors = [];

        $niveauActuel  = (int) ($input['niveau_juridiction_actuel_id'] ?? 0);
        $niveauSuivant = (int) ($input['niveau_juridiction_suivant_id'] ?? 0);
        $actuelId      = (int) ($input['etape_plainte_actuel_id'] ?? 0);
        $suivantId     = (int) ($input['etape_plainte_suivant_id'] ?? 0);
        $actionId      = (int) ($input['etape_plainte_action_id'] ?? 0);
        $urlRoute      = trim((string) ($input['url_route'] ?? ''));

        if ($niveauActuel < 1 || ! $this->levels->find($niveauActuel)) {
            $errors[] = lang('Backoffice.csc_err_level_current');
        }
        if ($niveauSuivant < 1 || ! $this->levels->find($niveauSuivant)) {
            $errors[] = lang('Backoffice.csc_err_level_next');
        }

        $actuel  = $actuelId ? $this->stages->find($actuelId) : null;
        $suivant = $suivantId ? $this->stages->find($suivantId) : null;

        if (! $actuel) {
            $errors[] = lang('Backoffice.csc_err_stage_current');
        } elseif ((int) ($actuel['niveau_juridiction_id'] ?? 0) !== $niveauActuel) {
            $errors[] = lang('Backoffice.csc_err_stage_level_current');
        }

        if (! $suivant) {
            $errors[] = lang('Backoffice.csc_err_stage_next');
        } elseif ((int) ($suivant['niveau_juridiction_id'] ?? 0) !== $niveauSuivant) {
            $errors[] = lang('Backoffice.csc_err_stage_level_next');
        }

        if ($actionId < 1) {
            $errors[] = lang('Backoffice.csc_err_action');
        } elseif ($actuelId > 0 && ! $this->actions->belongsToEtape($actionId, $actuelId)) {
            $errors[] = lang('Backoffice.csc_err_action_stage');
        }

        if ($actuelId > 0 && $suivantId > 0 && $actuelId === $suivantId) {
            $errors[] = lang('Backoffice.csc_err_self');
        }

        if (
            $actuelId > 0
            && $actionId > 0
            && $suivantId > 0
            && $this->configs->transitionExists($actuelId, $actionId, $suivantId, $ignoreId)
        ) {
            $errors[] = lang('Backoffice.csc_err_duplicate');
        }

        if ($urlRoute === '') {
            $errors[] = lang('Backoffice.csc_err_route');
        }

        if ($niveauActuel > 0 && $niveauSuivant > 0 && $niveauSuivant < $niveauActuel) {
            $errors[] = lang('Backoffice.csc_err_workflow');
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
            'etape_plainte_actuel_id'  => (int) ($input['etape_plainte_actuel_id'] ?? 0),
            'etape_plainte_suivant_id' => (int) ($input['etape_plainte_suivant_id'] ?? 0),
            'etape_plainte_action_id'  => (int) ($input['etape_plainte_action_id'] ?? 0),
            'url_route'                => trim((string) ($input['url_route'] ?? '')),
        ];
    }

    private function actorId(): ?int
    {
        $id = session('backoffice_user_id');

        return $id ? (int) $id : null;
    }
}
