<?php

namespace Modules\Transfer\Services;

use Modules\Administration\Models\AuditLogModel;
use Modules\Administration\Models\JuridictionModel;
use Modules\Administration\Models\UtilisateurModel;
use Modules\Complaint\Models\DocumentPlainteModel;
use Modules\Complaint\Models\PlainteModel;
use Modules\CourtJurisdiction\Models\ConfigurationJuridictionModel;
use Modules\CourtJurisdiction\Models\ConfigurationNiveauJuridictionModel;
use Modules\Notification\Services\NotificationMailer;
use Modules\Transfer\Models\StatutTransfertDossierModel;
use Modules\Transfer\Models\TransfertDossierModel;

class BackofficeTransferService
{
    private TransfertDossierModel $transfers;
    private StatutTransfertDossierModel $statuses;
    private PlainteModel $plaintes;
    private JuridictionModel $courts;
    private ConfigurationNiveauJuridictionModel $levelHierarchy;
    private ConfigurationJuridictionModel $courtHierarchy;
    private DocumentPlainteModel $documents;
    private UtilisateurModel $users;
    private NotificationMailer $mailer;
    private AuditLogModel $audit;

    public function __construct(
        ?TransfertDossierModel $transfers = null,
        ?StatutTransfertDossierModel $statuses = null,
        ?PlainteModel $plaintes = null,
        ?JuridictionModel $courts = null,
        ?ConfigurationNiveauJuridictionModel $levelHierarchy = null,
        ?ConfigurationJuridictionModel $courtHierarchy = null,
        ?DocumentPlainteModel $documents = null,
        ?UtilisateurModel $users = null,
        ?NotificationMailer $mailer = null,
        ?AuditLogModel $audit = null
    ) {
        $this->transfers       = $transfers ?? new TransfertDossierModel();
        $this->statuses        = $statuses ?? new StatutTransfertDossierModel();
        $this->plaintes        = $plaintes ?? new PlainteModel();
        $this->courts          = $courts ?? new JuridictionModel();
        $this->levelHierarchy  = $levelHierarchy ?? new ConfigurationNiveauJuridictionModel();
        $this->courtHierarchy  = $courtHierarchy ?? new ConfigurationJuridictionModel();
        $this->documents       = $documents ?? new DocumentPlainteModel();
        $this->users           = $users ?? new UtilisateurModel();
        $this->mailer          = $mailer ?? service('notifications');
        $this->audit           = $audit ?? new AuditLogModel();
    }

    /**
     * @param array<string, mixed> $query
     * @return list<array<string, mixed>>
     */
    public function list(array $query = []): array
    {
        $db = db_connect();

        try {
            $builder = $db->table('transfert.transfert_dossier t')
                ->select("
                    t.transfert_dossier_id AS id,
                    t.plainte_id,
                    t.date_transfert,
                    t.date_reception,
                    t.statut_transfert_dossier_id,
                    pl.numero_dossier AS case_number,
                    pl.objet AS subject,
                    js.nom_juridiction AS source_court,
                    njs.desc_niveau_juridiction AS source_level,
                    jd.nom_juridiction AS dest_court,
                    njd.desc_niveau_juridiction AS dest_level,
                    st.description_statut_transfert_dossier AS status_label,
                    js.province_id AS source_province_id,
                    js.commune_id AS source_commune_id,
                    js.niveau_juridiction_id AS source_niveau_id,
                    jd.province_id AS dest_province_id,
                    jd.commune_id AS dest_commune_id,
                    jd.niveau_juridiction_id AS dest_niveau_id
                ", false)
                ->join('plainte.plainte pl', 'pl.plainte_id = t.plainte_id', 'left')
                ->join('juridiction.juridiction js', 'js.juridiction_id = t.juridiction_source_id', 'left')
                ->join('juridiction.niveau_juridiction njs', 'njs.niveau_juridiction_id = js.niveau_juridiction_id', 'left')
                ->join('juridiction.juridiction jd', 'jd.juridiction_id = t.juridiction_dest_id', 'left')
                ->join('juridiction.niveau_juridiction njd', 'njd.niveau_juridiction_id = jd.niveau_juridiction_id', 'left')
                ->join('transfert.statut_transfert_dossier st', 'st.statut_transfert_dossier_id = t.statut_transfert_dossier_id', 'left');

            if (! empty($query['niveau_juridiction_source_id'])) {
                $builder->where('js.niveau_juridiction_id', (int) $query['niveau_juridiction_source_id']);
            }
            if (! empty($query['province_source_id'])) {
                $builder->where('js.province_id', (int) $query['province_source_id']);
            }
            if (! empty($query['commune_source_id'])) {
                $builder->where('js.commune_id', (int) $query['commune_source_id']);
            }
            if (! empty($query['juridiction_source_id'])) {
                $builder->where('t.juridiction_source_id', (int) $query['juridiction_source_id']);
            }
            if (! empty($query['niveau_juridiction_dest_id'])) {
                $builder->where('jd.niveau_juridiction_id', (int) $query['niveau_juridiction_dest_id']);
            }
            if (! empty($query['province_dest_id'])) {
                $builder->where('jd.province_id', (int) $query['province_dest_id']);
            }
            if (! empty($query['commune_dest_id'])) {
                $builder->where('jd.commune_id', (int) $query['commune_dest_id']);
            }
            if (! empty($query['juridiction_dest_id'])) {
                $builder->where('t.juridiction_dest_id', (int) $query['juridiction_dest_id']);
            }
            if (! empty($query['date_transfert'])) {
                $builder->where('t.date_transfert', $query['date_transfert']);
            }
            if (! empty($query['date_reception'])) {
                $builder->where('t.date_reception', $query['date_reception']);
            }
            if (! empty($query['statut_transfert_dossier_id'])) {
                $builder->where('t.statut_transfert_dossier_id', (int) $query['statut_transfert_dossier_id']);
            }

            $rows = $builder->orderBy('t.date_transfert', 'DESC')->orderBy('t.transfert_dossier_id', 'DESC')->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list transfers: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(function (array $row): array {
            $pending = $this->isPendingStatus((string) ($row['status_label'] ?? ''));

            return [
                'id'            => (int) $row['id'],
                'plainte_id'    => (int) ($row['plainte_id'] ?? 0),
                'case_number'   => (string) ($row['case_number'] ?? '—'),
                'subject'       => (string) ($row['subject'] ?? '—'),
                'source_label'  => trim(($row['source_level'] ?? '') . ' / ' . ($row['source_court'] ?? ''), ' /') ?: '—',
                'dest_label'    => trim(($row['dest_level'] ?? '') . ' / ' . ($row['dest_court'] ?? ''), ' /') ?: '—',
                'date_transfert'=> $this->formatDate($row['date_transfert'] ?? null),
                'date_reception'=> $this->formatDate($row['date_reception'] ?? null, true),
                'status_label'  => (string) ($row['status_label'] ?? '—'),
                'is_pending'    => $pending,
                'can_process'   => $pending,
            ];
        }, $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function details(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        $db = db_connect();

        try {
            $row = $db->query("
                SELECT
                    t.*,
                    pl.numero_dossier,
                    pl.objet,
                    pl.description AS plainte_description,
                    pl.date_depot,
                    pl.juridiction_id AS plainte_juridiction_id,
                    pl.niveau_juridiction_id AS plainte_niveau_id,
                    ep.description_etape_plainte AS stage_label,
                    sp.description_statut_plainte AS complaint_status,
                    js.nom_juridiction AS source_court,
                    js.code_juridiction AS source_code,
                    js.province_id AS source_province_id,
                    js.commune_id AS source_commune_id,
                    njs.desc_niveau_juridiction AS source_level,
                    ps.province_name AS source_province,
                    cs.commune_name AS source_commune,
                    jd.nom_juridiction AS dest_court,
                    jd.code_juridiction AS dest_code,
                    jd.province_id AS dest_province_id,
                    jd.commune_id AS dest_commune_id,
                    njd.desc_niveau_juridiction AS dest_level,
                    pd.province_name AS dest_province,
                    cd.commune_name AS dest_commune,
                    st.description_statut_transfert_dossier AS status_label,
                    TRIM(CONCAT(COALESCE(ut.prenom_utilisateur, ''), ' ', COALESCE(ut.nom_utilisateur, ''))) AS transferred_by,
                    TRIM(CONCAT(COALESCE(ur.prenom_utilisateur, ''), ' ', COALESCE(ur.nom_utilisateur, ''))) AS received_by
                FROM transfert.transfert_dossier t
                LEFT JOIN plainte.plainte pl ON pl.plainte_id = t.plainte_id
                LEFT JOIN plainte.etape_plainte ep ON ep.etape_plainte_id = pl.etape_plainte_id
                LEFT JOIN plainte.statut_plainte sp ON sp.statut_plainte_id = pl.statut_plainte_id
                LEFT JOIN juridiction.juridiction js ON js.juridiction_id = t.juridiction_source_id
                LEFT JOIN juridiction.niveau_juridiction njs ON njs.niveau_juridiction_id = js.niveau_juridiction_id
                LEFT JOIN localite.localite_province ps ON ps.province_id = js.province_id
                LEFT JOIN localite.localite_commune cs ON cs.commune_id = js.commune_id
                LEFT JOIN juridiction.juridiction jd ON jd.juridiction_id = t.juridiction_dest_id
                LEFT JOIN juridiction.niveau_juridiction njd ON njd.niveau_juridiction_id = jd.niveau_juridiction_id
                LEFT JOIN localite.localite_province pd ON pd.province_id = jd.province_id
                LEFT JOIN localite.localite_commune cd ON cd.commune_id = jd.commune_id
                LEFT JOIN transfert.statut_transfert_dossier st ON st.statut_transfert_dossier_id = t.statut_transfert_dossier_id
                LEFT JOIN administration.utilisateur ut ON ut.utilisateur_id = t.transfere_par
                LEFT JOIN administration.utilisateur ur ON ur.utilisateur_id = t.recu_par
                WHERE t.transfert_dossier_id = ?
            ", [$id])->getFirstRow('array');
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load transfer {id}: {message}', ['id' => $id, 'message' => $e->getMessage()]);

            return null;
        }

        if (! $row) {
            return null;
        }

        $pending = $this->isPendingStatus((string) ($row['status_label'] ?? ''));
        $docs    = [];
        try {
            $docs = $this->documents->listByPlainte((int) $row['plainte_id']);
        } catch (\Throwable $e) {
            $docs = [];
        }

        return [
            'id'                   => (int) $row['transfert_dossier_id'],
            'plainte_id'           => (int) $row['plainte_id'],
            'case_number'          => (string) ($row['numero_dossier'] ?? '—'),
            'subject'              => (string) ($row['objet'] ?? '—'),
            'description'          => (string) ($row['plainte_description'] ?? '—'),
            'filing_date'          => $this->formatDate($row['date_depot'] ?? null),
            'stage_label'          => (string) ($row['stage_label'] ?? '—'),
            'complaint_status'     => (string) ($row['complaint_status'] ?? '—'),
            'source_level'         => (string) ($row['source_level'] ?? '—'),
            'source_court'         => (string) ($row['source_court'] ?? '—'),
            'source_province'      => (string) ($row['source_province'] ?? '—'),
            'source_commune'       => (string) ($row['source_commune'] ?? '—'),
            'dest_level'           => (string) ($row['dest_level'] ?? '—'),
            'dest_court'           => (string) ($row['dest_court'] ?? '—'),
            'dest_province'        => (string) ($row['dest_province'] ?? '—'),
            'dest_commune'         => (string) ($row['dest_commune'] ?? '—'),
            'transfer_number'      => 'TRF-' . str_pad((string) $row['transfert_dossier_id'], 6, '0', STR_PAD_LEFT),
            'numero_dossier_dest'  => (string) ($row['numero_dossier_dest'] ?? '—'),
            'date_transfert'       => $this->formatDate($row['date_transfert'] ?? null),
            'date_reception'       => $this->formatDate($row['date_reception'] ?? null, true),
            'status_label'         => (string) ($row['status_label'] ?? '—'),
            'transferred_by'       => trim((string) ($row['transferred_by'] ?? '')) ?: '—',
            'received_by'          => trim((string) ($row['received_by'] ?? '')) ?: '—',
            'observations'         => (string) ($row['observations'] ?? ''),
            'is_pending'           => $pending,
            'can_process'          => $pending,
            'already_received'     => ! $pending,
            'juridiction_source_id'=> (int) $row['juridiction_source_id'],
            'juridiction_dest_id'  => (int) $row['juridiction_dest_id'],
            'documents'            => array_map(static fn (array $d): array => [
                'id'       => (int) $d['document_plainte_id'],
                'name'     => (string) ($d['nom_fichier'] ?? '—'),
                'type'     => (string) ($d['libelle_type_document'] ?? ($d['code_type_document'] ?? '—')),
                'date'     => isset($d['date_depot']) ? date('Y-m-d H:i', strtotime((string) $d['date_depot'])) : '—',
                'uploader' => (string) ($d['uploaded_by_name'] ?? '—'),
            ], $docs),
            'audit_history'        => $this->auditHistory((int) $row['transfert_dossier_id']),
        ];
    }

    /**
     * @return list<array{id:int,label:string,niveau_juridiction_id:int}>
     */
    public function eligibleComplaints(int $sourceCourtId): array
    {
        if ($sourceCourtId < 1) {
            return [];
        }

        $db = db_connect();

        try {
            $rows = $db->query("
                SELECT pl.plainte_id, pl.numero_dossier, pl.objet, pl.niveau_juridiction_id,
                       sp.description_statut_plainte
                FROM plainte.plainte pl
                LEFT JOIN plainte.statut_plainte sp ON sp.statut_plainte_id = pl.statut_plainte_id
                WHERE pl.juridiction_id = ?
                  AND (
                        sp.description_statut_plainte IS NULL
                     OR (
                            LOWER(sp.description_statut_plainte) NOT LIKE '%annul%'
                        AND LOWER(sp.description_statut_plainte) NOT LIKE '%clotur%'
                        AND LOWER(sp.description_statut_plainte) NOT LIKE '%clos%'
                     )
                  )
                  AND NOT EXISTS (
                        SELECT 1
                        FROM transfert.transfert_dossier t
                        INNER JOIN transfert.statut_transfert_dossier s
                            ON s.statut_transfert_dossier_id = t.statut_transfert_dossier_id
                        WHERE t.plainte_id = pl.plainte_id
                          AND (
                                LOWER(s.description_statut_transfert_dossier) LIKE '%transit%'
                             OR LOWER(s.description_statut_transfert_dossier) LIKE '%attente%'
                             OR LOWER(s.description_statut_transfert_dossier) LIKE '%pending%'
                          )
                  )
                ORDER BY pl.numero_dossier ASC
            ", [$sourceCourtId])->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list eligible transfer complaints: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static fn (array $row): array => [
            'id'                    => (int) $row['plainte_id'],
            'label'                 => trim(($row['numero_dossier'] ?? '') . ' — ' . ($row['objet'] ?? '')),
            'niveau_juridiction_id' => (int) ($row['niveau_juridiction_id'] ?? 0),
        ], $rows);
    }

    public function nextLevelFor(int $niveauId): ?int
    {
        return $this->levelHierarchy->parentLevelId($niveauId);
    }

    /**
     * Destination courts for a source court (configured parent or courts at next level).
     *
     * @return array{next_niveau_id:?int,options:list<array{id:int,label:string,niveau_juridiction_id:int,province_id:mixed,commune_id:mixed}>}
     */
    public function destinationCourtsFor(int $sourceCourtId, array $filters = []): array
    {
        $source = $this->courts->find($sourceCourtId);
        if (! $source) {
            return ['next_niveau_id' => null, 'options' => []];
        }

        $sourceNiveau = (int) ($source['niveau_juridiction_id'] ?? 0);
        $nextNiveau   = $this->nextLevelFor($sourceNiveau);
        if (! $nextNiveau) {
            return ['next_niveau_id' => null, 'options' => []];
        }

        // Prefer explicit court hierarchy when configured.
        try {
            $configured = $this->courtHierarchy->builder()
                ->select('juridiction_parent_id')
                ->where('juridiction_id', $sourceCourtId)
                ->where('(is_active IS NULL OR is_active = TRUE)', null, false)
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            $configured = [];
        }

        $parentIds = array_values(array_filter(array_map(
            static fn (array $r): int => (int) ($r['juridiction_parent_id'] ?? 0),
            $configured
        )));

        $optsFilters = [
            'niveau_juridiction_id' => $nextNiveau,
            'province_id'           => ! empty($filters['province_id']) ? (int) $filters['province_id'] : null,
            'commune_id'            => ! empty($filters['commune_id']) ? (int) $filters['commune_id'] : null,
        ];

        $options = $this->courts->options($optsFilters);

        if ($parentIds) {
            $options = array_values(array_filter(
                $options,
                static fn (array $opt): bool => in_array((int) $opt['id'], $parentIds, true)
            ));
        }

        // Never allow the source court itself.
        $options = array_values(array_filter(
            $options,
            static fn (array $opt): bool => (int) $opt['id'] !== $sourceCourtId
        ));

        return [
            'next_niveau_id' => $nextNiveau,
            'options'        => array_map(static fn (array $opt): array => [
                'id'                    => (int) $opt['id'],
                'label'                 => (string) $opt['label'],
                'niveau_juridiction_id' => (int) ($opt['niveau_juridiction_id'] ?? $nextNiveau),
                'province_id'           => $opt['province_id'] ?? null,
                'commune_id'            => $opt['commune_id'] ?? null,
            ], $options),
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>,id?:int}
     */
    public function create(array $input): array
    {
        $errors = $this->validateCreate($input);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $actorId = $this->actorId();
        if (! $actorId) {
            return ['ok' => false, 'errors' => [lang('Backoffice.trf_err_actor')]];
        }

        $pendingStatusId = $this->statusIdByKeywords(['transit', 'attente', 'pending']);
        if (! $pendingStatusId) {
            return ['ok' => false, 'errors' => [lang('Backoffice.trf_err_status_pending')]];
        }

        $plainteId = (int) $input['plainte_id'];
        $sourceId  = (int) $input['juridiction_source_id'];
        $destId    = (int) $input['juridiction_dest_id'];

        $data = [
            'plainte_id'                   => $plainteId,
            'juridiction_source_id'        => $sourceId,
            'juridiction_dest_id'          => $destId,
            'numero_dossier_dest'          => null,
            'date_transfert'               => date('Y-m-d'),
            'transfere_par'                => $actorId,
            'recu_par'                     => null,
            'date_reception'               => null,
            'statut_transfert_dossier_id'  => $pendingStatusId,
            'observations'                 => trim((string) ($input['observations'] ?? '')) ?: null,
            'created_at'                   => date('Y-m-d H:i:s'),
        ];

        try {
            $id = $this->transfers->insert($data, true);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to create transfer: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.trf_err_save')]];
        }

        if (! $id) {
            return ['ok' => false, 'errors' => [lang('Backoffice.trf_err_save')]];
        }

        $this->audit->record('CREATE', 'transfert.transfert_dossier', (int) $id, null, $data, $actorId);
        $this->notifyCourts((int) $id, 'created');

        return ['ok' => true, 'id' => (int) $id];
    }

    /**
     * @return array{ok:bool,errors?:list<string>}
     */
    public function receive(int $id): array
    {
        $details = $this->details($id);
        if (! $details) {
            return ['ok' => false, 'errors' => [lang('Backoffice.trf_err_not_found')]];
        }

        if (! ($details['can_process'] ?? false)) {
            return ['ok' => false, 'errors' => [lang('Backoffice.trf_err_already_received')]];
        }

        $actorId = $this->actorId();
        if (! $actorId) {
            return ['ok' => false, 'errors' => [lang('Backoffice.trf_err_actor')]];
        }

        $receivedStatusId = $this->statusIdByKeywords(['recu', 'reçu', 'received']);
        if (! $receivedStatusId) {
            return ['ok' => false, 'errors' => [lang('Backoffice.trf_err_status_received')]];
        }

        $transfer = $this->transfers->find($id);
        $plainte  = $this->plaintes->find((int) $transfer['plainte_id']);
        $destCourt = $this->courts->find((int) $transfer['juridiction_dest_id']);
        if (! $transfer || ! $plainte || ! $destCourt) {
            return ['ok' => false, 'errors' => [lang('Backoffice.trf_err_not_found')]];
        }

        $destNiveau = (int) ($destCourt['niveau_juridiction_id'] ?? 0);
        $newNumber  = $this->nextDestCaseNumber($destCourt);
        $nowDate    = date('Y-m-d');

        $oldTransfer = $transfer;
        $oldPlainte  = $plainte;

        $db = db_connect();
        $db->transStart();

        try {
            $this->transfers->update($id, [
                'recu_par'                    => $actorId,
                'date_reception'              => $nowDate,
                'statut_transfert_dossier_id' => $receivedStatusId,
                'numero_dossier_dest'         => $newNumber,
            ]);

            $this->plaintes->update((int) $plainte['plainte_id'], [
                'juridiction_id'        => (int) $transfer['juridiction_dest_id'],
                'niveau_juridiction_id' => $destNiveau,
                'numero_dossier'        => $newNumber,
                'updated_at'            => date('Y-m-d H:i:s'),
            ]);

            $db->transComplete();
            if (! $db->transStatus()) {
                throw new \RuntimeException('transfer receive transaction failed');
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Failed to receive transfer {id}: {message}', [
                'id'      => $id,
                'message' => $e->getMessage(),
            ]);

            return ['ok' => false, 'errors' => [lang('Backoffice.trf_err_receive')]];
        }

        $newTransfer = $this->transfers->find($id);
        $newPlainte  = $this->plaintes->find((int) $plainte['plainte_id']);

        $this->audit->record('RECEIVE', 'transfert.transfert_dossier', $id, $oldTransfer, $newTransfer, $actorId);
        $this->audit->record('UPDATE', 'plainte.plainte', (int) $plainte['plainte_id'], $oldPlainte, $newPlainte, $actorId);
        $this->notifyCourts($id, 'received');

        return ['ok' => true];
    }

    /**
     * @param array<string, mixed> $input
     * @return list<string>
     */
    private function validateCreate(array $input): array
    {
        $errors = [];

        $sourceId  = (int) ($input['juridiction_source_id'] ?? 0);
        $destId    = (int) ($input['juridiction_dest_id'] ?? 0);
        $plainteId = (int) ($input['plainte_id'] ?? 0);

        if ($sourceId < 1) {
            $errors[] = lang('Backoffice.trf_err_source_required');
        }
        if ($plainteId < 1) {
            $errors[] = lang('Backoffice.trf_err_complaint_required');
        }
        if ($destId < 1) {
            $errors[] = lang('Backoffice.trf_err_dest_required');
        }
        if ($sourceId > 0 && $destId > 0 && $sourceId === $destId) {
            $errors[] = lang('Backoffice.trf_err_same_court');
        }

        if ($errors) {
            return $errors;
        }

        $source = $this->courts->find($sourceId);
        $dest   = $this->courts->find($destId);
        $plainte = $this->plaintes->find($plainteId);

        if (! $source || ! $dest || ! $plainte) {
            return [lang('Backoffice.trf_err_invalid_refs')];
        }

        if ((int) ($plainte['juridiction_id'] ?? 0) !== $sourceId) {
            return [lang('Backoffice.trf_err_complaint_court')];
        }

        if ($this->transfers->hasPendingTransfer($plainteId)) {
            return [lang('Backoffice.trf_err_duplicate_pending')];
        }

        $statusLabel = '';
        try {
            $st = db_connect()->table('plainte.statut_plainte')
                ->select('description_statut_plainte')
                ->where('statut_plainte_id', (int) ($plainte['statut_plainte_id'] ?? 0))
                ->get()
                ->getFirstRow('array');
            $statusLabel = mb_strtolower((string) ($st['description_statut_plainte'] ?? ''));
        } catch (\Throwable $e) {
            $statusLabel = '';
        }

        if ($statusLabel !== '' && (str_contains($statusLabel, 'annul') || str_contains($statusLabel, 'clotur') || str_contains($statusLabel, 'clos'))) {
            return [lang('Backoffice.trf_err_complaint_status')];
        }

        $sourceNiveau = (int) ($source['niveau_juridiction_id'] ?? 0);
        $destNiveau   = (int) ($dest['niveau_juridiction_id'] ?? 0);
        $nextNiveau   = $this->nextLevelFor($sourceNiveau);

        if (! $nextNiveau || $destNiveau !== $nextNiveau) {
            return [lang('Backoffice.trf_err_level_hierarchy')];
        }

        $destPack = $this->destinationCourtsFor($sourceId);
        $allowedIds = array_map(static fn (array $o): int => (int) $o['id'], $destPack['options']);
        if ($allowedIds && ! in_array($destId, $allowedIds, true)) {
            return [lang('Backoffice.trf_err_dest_workflow')];
        }

        return [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function auditHistory(int $transferId): array
    {
        try {
            $rows = db_connect()->table('audit_log.audit_log a')
                ->select("
                    a.audit_log_id,
                    a.action,
                    a.created_at,
                    a.adresse_ip::text AS adresse_ip,
                    TRIM(CONCAT(COALESCE(u.prenom_utilisateur, ''), ' ', COALESCE(u.nom_utilisateur, ''))) AS user_name
                ", false)
                ->join('administration.utilisateur u', 'u.utilisateur_id = a.utilisateur_id', 'left')
                ->where('a.table_cible', 'transfert.transfert_dossier')
                ->where('a.enregistrement_id', $transferId)
                ->orderBy('a.created_at', 'ASC')
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            return [];
        }

        return array_map(static function (array $row): array {
            return [
                'action'     => (string) ($row['action'] ?? ''),
                'user_name'  => trim((string) ($row['user_name'] ?? '')) ?: '—',
                'created_at' => isset($row['created_at']) ? date('Y-m-d H:i:s', strtotime((string) $row['created_at'])) : '—',
                'ip'         => (string) ($row['adresse_ip'] ?? ''),
            ];
        }, $rows);
    }

    private function notifyCourts(int $transferId, string $event): void
    {
        $details = $this->details($transferId);
        if (! $details) {
            return;
        }

        $caseNumber = (string) $details['case_number'];
        if ($event === 'received' && ($details['numero_dossier_dest'] ?? '') !== '' && ($details['numero_dossier_dest'] ?? '') !== '—') {
            $caseNumber = (string) $details['numero_dossier_dest'];
        }

        if ($event === 'received') {
            $subject = lang('Mail.subject_transfer_received', [$caseNumber]);
            $body    = lang('Mail.transfer_received_body', [
                $caseNumber,
                $details['source_court'],
                $details['dest_court'],
                $details['date_reception'],
            ]);
        } else {
            $subject = lang('Mail.subject_transfer_created', [$caseNumber]);
            $body    = lang('Mail.transfer_created_body', [
                $caseNumber,
                $details['source_court'],
                $details['dest_court'],
                $details['date_transfert'],
            ]);
        }

        $canalId  = $this->lookupId('notification.canal_notification', 'canal_notification_id', 'description_canal_notification', ['email', 'e-mail', 'mail']);
        $statutId = $this->lookupId('notification.statut_notification', 'statut_notification_id', 'description_statut_notification', ['envoyé', 'envoye', 'sent', 'delivered']);

        foreach ([(int) $details['juridiction_source_id'], (int) $details['juridiction_dest_id']] as $courtId) {
            if ($courtId < 1) {
                continue;
            }

            try {
                $courtUsers = $this->users->listWithRelations([
                    'juridiction_id' => $courtId,
                    'account_active' => true,
                ]);
            } catch (\Throwable $e) {
                $courtUsers = [];
            }

            foreach ($courtUsers as $user) {
                $userId = (int) ($user['utilisateur_id'] ?? 0);
                $name   = trim(($user['prenom_utilisateur'] ?? '') . ' ' . ($user['nom_utilisateur'] ?? ''));
                $email  = trim((string) ($user['email'] ?? ''));

                $this->insertUserNotification($userId, $canalId, $statutId, $subject, $body);

                if ($email !== '') {
                    try {
                        $this->mailer->send(
                            'case_transfer_' . $event,
                            $email,
                            $name,
                            $subject,
                            'Modules\Notification\Views\emails\generic_message',
                            ['name' => $name, 'subject' => $subject, 'body' => $body]
                        );
                    } catch (\Throwable $e) {
                        log_message('error', 'Transfer notification email failed: {message}', ['message' => $e->getMessage()]);
                    }
                }
            }
        }
    }

    private function insertUserNotification(int $utilisateurId, ?int $canalId, ?int $statutId, string $subject, string $body): void
    {
        if ($utilisateurId < 1 || ! $canalId || ! $statutId) {
            return;
        }

        try {
            db_connect()->table('notification.notification_utilisateur')->insert([
                'utilisateur_id'         => $utilisateurId,
                'canal_notification_id'  => $canalId,
                'sujet'                  => $subject,
                'corps'                  => $body,
                'statut_notification_id' => $statutId,
                'envoye_le'              => date('Y-m-d H:i:s'),
                'created_at'             => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to insert transfer notification_utilisateur: {message}', ['message' => $e->getMessage()]);
        }
    }

    /**
     * @param list<string> $needles
     */
    private function lookupId(string $table, string $idCol, string $labelCol, array $needles): ?int
    {
        try {
            $rows = db_connect()->table($table)->select("{$idCol}, {$labelCol}")->get()->getResultArray();
        } catch (\Throwable $e) {
            return null;
        }

        foreach ($rows as $row) {
            $label = mb_strtolower(trim((string) ($row[$labelCol] ?? '')));
            foreach ($needles as $needle) {
                if ($label !== '' && str_contains($label, mb_strtolower($needle))) {
                    return (int) $row[$idCol];
                }
            }
        }

        return null;
    }

    private function statusIdByKeywords(array $needles): ?int
    {
        foreach ($this->statuses->listAll() as $row) {
            $label = mb_strtolower((string) ($row['description_statut_transfert_dossier'] ?? ''));
            foreach ($needles as $needle) {
                if ($label !== '' && str_contains($label, mb_strtolower((string) $needle))) {
                    return (int) $row['statut_transfert_dossier_id'];
                }
            }
        }

        return null;
    }

    private function isPendingStatus(string $label): bool
    {
        $l = mb_strtolower($label);

        return str_contains($l, 'transit') || str_contains($l, 'attente') || str_contains($l, 'pending');
    }

    /**
     * @param array<string, mixed> $destCourt
     */
    private function nextDestCaseNumber(array $destCourt): string
    {
        $raw  = strtoupper((string) ($destCourt['code_juridiction'] ?? 'JH'));
        $code = preg_replace('/[^A-Z0-9]/', '', $raw) ?: 'JH';
        $year = date('Y');
        $like = $code . '-' . $year . '-%';

        try {
            $row = db_connect()->query(
                "SELECT COUNT(*)::int AS total FROM plainte.plainte WHERE numero_dossier LIKE ?",
                [$like]
            )->getRowArray();
            $seq = ((int) ($row['total'] ?? 0)) + 1;
        } catch (\Throwable $e) {
            $seq = 1;
        }

        return sprintf('%s-%s-%05d', $code, $year, $seq);
    }

    private function formatDate(mixed $value, bool $allowEmpty = false): string
    {
        if ($value === null || $value === '') {
            return $allowEmpty ? '—' : '—';
        }

        $ts = strtotime((string) $value);

        return $ts ? date('Y-m-d', $ts) : (string) $value;
    }

    private function actorId(): ?int
    {
        $id = session('backoffice_user_id');

        return $id ? (int) $id : null;
    }
}
