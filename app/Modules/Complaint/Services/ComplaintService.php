<?php

namespace Modules\Complaint\Services;

use Modules\Complaint\Models\PlainteModel;

class ComplaintService
{
    private PlainteModel $plaintes;

    public function __construct(?PlainteModel $plaintes = null)
    {
        $this->plaintes = $plaintes ?? new PlainteModel();
    }

    /**
     * Communal court complaints for dashboard and My Complaints lists.
     *
     * @return list<array<string, mixed>>
     */
    public function listCommunalComplaints(): array
    {
        try {
            $rows = $this->plaintes->listByJurisdictionLevel(1);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load communal complaints: {message}', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }

        return array_map([$this, 'mapListRow'], $rows);
    }

    /**
     * Provincial court complaints for dashboard and provincial appeal list.
     *
     * @return list<array<string, mixed>>
     */
    public function listProvincialComplaints(): array
    {
        try {
            $rows = $this->plaintes->listProvincialWithParent();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load provincial complaints: {message}', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }

        return array_map([$this, 'mapAppealListRow'], $rows);
    }

    /**
     * Regional court complaints for dashboard and regional appeal list.
     *
     * @return list<array<string, mixed>>
     */
    public function listRegionalComplaints(): array
    {
        try {
            $rows = $this->plaintes->listRegionalWithParent();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load regional complaints: {message}', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }

        return array_map([$this, 'mapAppealListRow'], $rows);
    }

    /**
     * Ministry of Justice complaints for dashboard and ministry appeal list.
     *
     * @return list<array<string, mixed>>
     */
    public function listMinistryComplaints(): array
    {
        try {
            $rows = $this->plaintes->listMinistryWithParent();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load ministry complaints: {message}', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }

        return array_map([$this, 'mapAppealListRow'], $rows);
    }

    /**
     * Summary statistics for a jurisdiction level (1 communal … 4 ministry).
     *
     * @return array{total:int,pending:int,in_progress:int,resolved:int}
     */
    public function statsForJurisdictionLevel(int $niveauJuridictionId): array
    {
        $stats = [
            'total'       => 0,
            'pending'     => 0,
            'in_progress' => 0,
            'resolved'    => 0,
        ];

        try {
            $rows = $this->plaintes->statusCountsByJurisdictionLevel($niveauJuridictionId);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load complaint stats for level {level}: {message}', [
                'level'   => (string) $niveauJuridictionId,
                'message' => $e->getMessage(),
            ]);

            return $stats;
        }

        foreach ($rows as $row) {
            $count  = (int) ($row['total'] ?? 0);
            $bucket = $this->statusBucket((string) ($row['description_statut_plainte'] ?? ''));
            $stats['total'] += $count;
            $stats[$bucket] += $count;
        }

        return $stats;
    }

    private function statusBucket(string $description): string
    {
        $hay = mb_strtolower(trim($description));

        if ($hay === '') {
            return 'pending';
        }

        if (preg_match('/jugement|judgment|clos|closed|r[eé]solu|resolved|termin[eé]|cl[oô]tur|d[eé]cid|archived|archivé/u', $hay)) {
            return 'resolved';
        }

        if (preg_match('/en\s*cours|in\s*progress|audience|hearing|v[eé]rif|review|instruction|examen|traitement|assign|affect/u', $hay)) {
            return 'in_progress';
        }

        if (preg_match('/en\s*attente|pending|soumis|submitted|d[eé]pos|nouveau|new|re[cç]u|enregistr|ouvert|open|initi/u', $hay)) {
            return 'pending';
        }

        return 'in_progress';
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findCommunalComplaint(string $id): ?array
    {
        if (! ctype_digit($id)) {
            return null;
        }

        try {
            $row = $this->plaintes->findCommunalById((int) $id);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load complaint {id}: {message}', [
                'id'      => $id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        return $row === null ? null : $this->mapListRow($row);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findProvincialComplaint(string $id): ?array
    {
        if (! ctype_digit($id)) {
            return null;
        }

        try {
            $row = $this->plaintes->findProvincialById((int) $id);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load provincial complaint {id}: {message}', [
                'id'      => $id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        return $row === null ? null : $this->mapAppealListRow($row);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findRegionalComplaint(string $id): ?array
    {
        if (! ctype_digit($id)) {
            return null;
        }

        try {
            $row = $this->plaintes->findRegionalById((int) $id);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load regional complaint {id}: {message}', [
                'id'      => $id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        return $row === null ? null : $this->mapAppealListRow($row);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findMinistryComplaint(string $id): ?array
    {
        if (! ctype_digit($id)) {
            return null;
        }

        try {
            $row = $this->plaintes->findMinistryById((int) $id);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load ministry complaint {id}: {message}', [
                'id'      => $id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        return $row === null ? null : $this->mapAppealListRow($row);
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function mapListRow(array $row): array
    {
        return [
            'id'                 => (string) ($row['plainte_id'] ?? ''),
            'case_number'        => (string) ($row['numero_dossier'] ?? ''),
            'subject'            => (string) ($row['objet'] ?? ''),
            'description'        => (string) ($row['description'] ?? ''),
            'court_jurisdiction' => (string) ($row['nom_juridiction'] ?? ''),
            'stage'              => (string) ($row['description_etape_plainte'] ?? ''),
            'status'             => (string) ($row['description_statut_plainte'] ?? ''),
            'submission_date'    => $this->formatDate($row['date_depot'] ?? null),
            'created_at'         => $this->formatDateTime($row['created_at'] ?? null),
            'submission_sort'    => $this->sortableDate($row['date_depot'] ?? null),
            'created_sort'       => $this->sortableDate($row['created_at'] ?? null),
        ];
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function mapAppealListRow(array $row): array
    {
        $base = $this->mapListRow($row);

        $base['old_case_number'] = (string) ($row['numero_dossier_ancien'] ?? '');
        $base['old_description'] = (string) ($row['description_ancien'] ?? '');

        return $base;
    }

    private function formatDate(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        $ts = strtotime((string) $value);

        return $ts === false ? (string) $value : date('Y-m-d', $ts);
    }

    private function formatDateTime(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        $ts = strtotime((string) $value);

        return $ts === false ? (string) $value : date('Y-m-d H:i', $ts);
    }

    private function sortableDate(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $ts = strtotime((string) $value);

        return $ts === false ? (string) $value : date('Y-m-d H:i:s', $ts);
    }

    public function listSampleCases(): array
    {
        return [];
    }

    public function submitComplaint(array $data): array
    {
        return [
            'ok'     => true,
            'number' => 'JH-DEMO',
        ];
    }
}
