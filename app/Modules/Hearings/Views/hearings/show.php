<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<?php
$hearingAt = trim(($record['date_audience'] ?? '') . ' ' . substr((string) ($record['heure_audience'] ?? ''), 0, 5));
$location = implode(' / ', array_filter([
    $record['province_name'] ?? null,
    $record['commune_name'] ?? null,
    $record['zone_name'] ?? null,
    $record['colline_name'] ?? null,
]));
$period = trim(substr((string) ($record['heure_debut'] ?? ''), 0, 5) . ' / ' . substr((string) ($record['heure_fin'] ?? ''), 0, 5), ' /');
?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_hearings')) ?></p>
        <h1><?= esc(lang('Backoffice.hrg_details_title')) ?></h1>
        <p><?= esc($hearingAt) ?> — <?= esc($record['nom_juridiction'] ?? '') ?></p>
    </div>
    <div class="bo-crud-head-actions">
        <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/hearings') ?>"><i class="bi bi-arrow-left"></i> <?= esc(lang('Backoffice.hrg_back_list')) ?></a>
        <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/hearings/' . (int) $record['audience_id'] . '/assignments') ?>"><i class="bi bi-people"></i> <?= esc(lang('Backoffice.hrg_action_assign')) ?></a>
        <a class="btn btn-bo-primary" href="<?= site_url('backoffice/hearings/' . (int) $record['audience_id'] . '/process') ?>"><i class="bi bi-clipboard-check"></i> <?= esc(lang('Backoffice.hrg_action_process')) ?></a>
    </div>
</section>

<section class="bo-panel bo-crud-panel">
    <h2 class="h5"><?= esc(lang('Backoffice.hrg_section_hearing_info')) ?></h2>
    <dl class="bo-detail-list">
        <div><dt><?= esc(lang('Backoffice.hrg_col_court')) ?></dt><dd><?= esc(trim(($record['desc_niveau_juridiction'] ?? '') . ' / ' . ($record['nom_juridiction'] ?? ''), ' /') ?: '—') ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.hrg_col_hearing')) ?></dt><dd><?= esc($hearingAt !== '' ? $hearingAt : '—') ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.hrg_col_location')) ?></dt><dd><?= esc($location !== '' ? $location : '—') ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.hrg_col_venue')) ?></dt><dd><?= esc($record['lieu_audience'] ?? '—') ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.hrg_col_period')) ?></dt><dd><?= esc($period !== '' ? $period : '—') ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.hrg_col_status')) ?></dt><dd><?= esc($record['description_statut_audience'] ?? '—') ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.hrg_field_actual_date')) ?></dt><dd><?= esc($record['date_tenue'] ?? '—') ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.hrg_field_postpone_reason')) ?></dt><dd><?= esc($record['motif_report'] ?? '—') ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.hrg_field_report')) ?></dt><dd><?= esc($record['rapport'] ?? '—') ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.hrg_field_report_validated')) ?></dt><dd><?= esc(filter_var($record['rapport_valide'] ?? false, FILTER_VALIDATE_BOOLEAN) ? lang('Backoffice.yes') : lang('Backoffice.no')) ?></dd></div>
    </dl>
</section>

<?php
$tables = [
    ['id' => 'hrg-staff-table', 'title' => lang('Backoffice.hrg_section_staff'), 'headers' => [lang('Backoffice.hrg_assign_col_name'), lang('Backoffice.hrg_assign_col_profile'), lang('Backoffice.hrg_assign_col_status'), lang('Backoffice.hrg_assign_col_by'), lang('Backoffice.hrg_assign_col_date')], 'rows' => array_map(static fn ($r) => [
        trim((string) ($r['assignee_name'] ?? '')) ?: '—',
        $r['libelle_profil'] ?? '—',
        filter_var($r['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN) ? lang('Backoffice.status_active') : lang('Backoffice.status_inactive'),
        trim((string) ($r['assigned_by_name'] ?? '')) ?: '—',
        $r['create_at'] ?? '—',
    ], $staff)],
    ['id' => 'hrg-complaints-table', 'title' => lang('Backoffice.hrg_section_complaints'), 'headers' => [lang('Backoffice.hrg_col_case'), lang('Backoffice.hrg_col_subject'), lang('Backoffice.hrg_col_court'), lang('Backoffice.hrg_col_stage'), lang('Backoffice.hrg_col_complaint_status'), lang('Backoffice.hrg_col_filing')], 'rows' => array_map(static fn ($r) => [
        $r['numero_dossier'] ?? '—',
        $r['objet'] ?? '—',
        trim(($r['desc_niveau_juridiction'] ?? '') . ' / ' . ($r['nom_juridiction'] ?? ''), ' /') ?: '—',
        $r['description_etape_plainte'] ?? '—',
        $r['description_statut_plainte'] ?? '—',
        $r['date_depot'] ?? '—',
    ], $complaints)],
    ['id' => 'hrg-attendance-table', 'title' => lang('Backoffice.hrg_section_attendance'), 'headers' => [lang('Backoffice.hrg_col_case'), lang('Backoffice.people_col_name'), lang('Backoffice.hrg_col_role'), lang('Backoffice.hrg_field_present'), lang('Backoffice.hrg_field_observations')], 'rows' => array_map(static fn ($r) => [
        $r['numero_dossier'] ?? '—',
        trim(($r['prenom_personne'] ?? '') . ' ' . ($r['nom_personne'] ?? '')),
        $r['description_role_personne'] ?? '—',
        filter_var($r['present'] ?? false, FILTER_VALIDATE_BOOLEAN) ? lang('Backoffice.yes') : lang('Backoffice.no'),
        $r['observations'] ?? '—',
    ], $attendance)],
    ['id' => 'hrg-docs-table', 'title' => lang('Backoffice.hrg_section_documents'), 'headers' => [lang('Backoffice.hrg_col_case'), lang('Backoffice.hrg_field_doc_description'), lang('Backoffice.hrg_field_doc_party'), lang('Backoffice.hrg_field_doc_by'), lang('Backoffice.hrg_field_doc_date')], 'rows' => array_map(static function ($r) {
        $obs = (string) ($r['observation'] ?? '');
        $obs = preg_replace('/\n__FILE__:.*$/', '', $obs) ?? $obs;
        return [
            $r['numero_dossier'] ?? '—',
            $obs !== '' ? $obs : '—',
            trim((string) ($r['party_name'] ?? '')) ?: '—',
            trim((string) ($r['uploaded_by_name'] ?? '')) ?: '—',
            $r['enregistre_le'] ?? '—',
        ];
    }, $documents)],
    ['id' => 'hrg-reports-table', 'title' => lang('Backoffice.hrg_section_reports'), 'headers' => [lang('Backoffice.hrg_col_case'), lang('Backoffice.hrg_field_report'), lang('Backoffice.hrg_field_report_validated'), lang('Backoffice.hrg_field_postpone_reason')], 'rows' => array_map(static fn ($r) => [
        $r['numero_dossier'] ?? '—',
        $r['rapport'] ?? ($record['rapport'] ?? '—'),
        filter_var($r['rapport_valide'] ?? false, FILTER_VALIDATE_BOOLEAN) ? lang('Backoffice.yes') : lang('Backoffice.no'),
        $r['motif_report'] ?? '—',
    ], $complaints)],
    ['id' => 'hrg-history-table', 'title' => lang('Backoffice.hrg_section_history'), 'headers' => [lang('Backoffice.hrg_col_action'), lang('Backoffice.hrg_assign_col_by'), lang('Backoffice.hrg_assign_col_date')], 'rows' => array_map(static fn ($r) => [
        $r['action'] ?? '—',
        trim((string) ($r['actor_name'] ?? '')) ?: '—',
        $r['created_at'] ?? '—',
    ], $history)],
    ['id' => 'hrg-summons-table', 'title' => lang('Backoffice.hrg_section_summons'), 'headers' => [lang('Backoffice.hrg_col_case'), lang('Backoffice.hrg_col_hearing'), lang('Backoffice.hrg_col_venue'), lang('Backoffice.hrg_col_status')], 'rows' => array_map(static fn ($r) => [
        $r['numero_dossier'] ?? '—',
        trim(($r['date_audience'] ?? '') . ' ' . substr((string) ($r['heure_audience'] ?? ''), 0, 5)),
        $r['lieu_audience'] ?? '—',
        $r['description_statut_convocation'] ?? '—',
    ], $summons)],
    ['id' => 'hrg-verdicts-table', 'title' => lang('Backoffice.hrg_section_verdicts'), 'headers' => [lang('Backoffice.hrg_col_case'), lang('Backoffice.hrg_col_filing'), lang('Backoffice.hrg_field_report')], 'rows' => array_map(static fn ($r) => [
        $r['numero_dossier'] ?? '—',
        $r['date_verdict'] ?? '—',
        trim(($r['description_type_verdict'] ?? '') . ' — ' . ($r['resume'] ?? ''), ' —') ?: '—',
    ], $verdicts)],
];
$tableIds = array_column($tables, 'id');
?>

<?php foreach ($tables as $table): ?>
<section class="bo-panel bo-crud-panel mt-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h2 class="h5 mb-0"><?= esc($table['title']) ?></h2>
        <label class="bo-table-search"><i class="bi bi-search"></i>
            <input type="search" class="form-control" id="<?= esc($table['id']) ?>-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>">
        </label>
    </div>
    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="<?= esc($table['id']) ?>" data-page-length="5" data-dom="lrtip">
            <thead><tr><?php foreach ($table['headers'] as $h): ?><th><?= esc($h) ?></th><?php endforeach; ?></tr></thead>
            <tbody>
            <?php foreach ($table['rows'] as $row): ?>
                <tr><?php foreach ($row as $cell): ?><td><?= esc((string) $cell) ?></td><?php endforeach; ?></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php endforeach; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
  <?= json_encode($tableIds) ?>.forEach((id) => {
    const input = document.getElementById(id + '-search');
    if (!input) return;
    input.addEventListener('input', () => {
      if (window.jQuery && window.jQuery.fn.dataTable && window.jQuery.fn.dataTable.isDataTable('#' + id)) {
        window.jQuery('#' + id).DataTable().search(input.value).draw();
      }
    });
  });
});
</script>
<?= $this->endSection() ?>
