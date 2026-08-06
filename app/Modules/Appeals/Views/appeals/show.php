<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<?php
$withinLabel = db_bool($record['dans_les_delais'] ?? false)
    ? lang('Backoffice.yes')
    : lang('Backoffice.no');
$verdictLabel = trim(
    ($record['description_type_verdict'] ?? '') . ' — ' . ($record['verdict_resume'] ?? ''),
    ' —'
);
$partyRow = static fn (array $r): array => [
    trim(($r['prenom_personne'] ?? '') . ' ' . ($r['nom_personne'] ?? '')),
    $r['description_role_personne'] ?? '—',
    $r['numero_cni'] ?? '—',
    $r['telephone'] ?? '—',
    $r['email'] ?? '—',
];
$presentLabel = static fn ($present): string => filter_var($present, FILTER_VALIDATE_BOOLEAN)
    ? lang('Backoffice.yes')
    : lang('Backoffice.no');
?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_appeals')) ?></p>
        <h1><?= esc(lang('Backoffice.apl_details_title')) ?></h1>
        <p><code class="bo-route-code"><?= esc($record['appeal_number'] ?? '') ?></code> — <?= esc($record['appeal_objet'] ?? ($record['objet'] ?? '')) ?></p>
    </div>
    <div class="bo-crud-head-actions">
        <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/appeals') ?>"><i class="bi bi-arrow-left"></i> <?= esc(lang('Backoffice.apl_back_list')) ?></a>
        <?php if (can_access('backoffice/appeals/edit')): ?>
        <a class="btn btn-bo-primary" href="<?= site_url('backoffice/appeals/' . (int) $record['recours_id'] . '/edit') ?>"><i class="bi bi-pencil-square"></i> <?= esc(lang('Backoffice.apl_action_edit')) ?></a>
        <?php endif; ?>
    </div>
</section>

<section class="bo-panel bo-crud-panel">
    <div class="bo-detail-grid">
        <article>
            <h2><?= esc(lang('Backoffice.apl_section_appeal')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.apl_col_number')) ?></dt><dd><?= esc($record['appeal_number'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.apl_col_date')) ?></dt><dd><?= esc($record['date_recours'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.apl_field_court')) ?></dt><dd><?= esc($record['nom_juridiction'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.apl_field_level')) ?></dt><dd><?= esc($record['desc_niveau_juridiction'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.apl_field_within_deadline')) ?></dt><dd><?= esc($withinLabel) ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.apl_col_status')) ?></dt><dd><?= esc($record['description_statut_plainte'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.apl_col_stage')) ?></dt><dd><?= esc($record['description_etape_plainte'] ?? '—') ?></dd></div>
            </dl>
        </article>
        <article>
            <h2><?= esc(lang('Backoffice.apl_section_parent')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.apl_col_previous')) ?></dt><dd><?= esc($record['parent_case_number'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.apl_field_objet')) ?></dt><dd><?= esc($record['parent_objet'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.apl_field_description')) ?></dt><dd><?= esc($record['parent_description'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.apl_field_court')) ?></dt><dd><?= esc($record['parent_juridiction'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.apl_field_level')) ?></dt><dd><?= esc($record['parent_niveau'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.apl_parent_filing')) ?></dt><dd><?= esc($record['parent_date_depot'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.apl_col_verdict')) ?></dt><dd><?= esc($verdictLabel !== '' ? $verdictLabel : '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.apl_verdict_date')) ?></dt><dd><?= esc($record['date_verdict'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.apl_field_deadline')) ?></dt><dd><?= esc($record['date_limite_recours'] ?? '—') ?></dd></div>
            </dl>
        </article>
    </div>
</section>

<?php
$tables = [
    ['id' => 'apl-parcels-table', 'title' => lang('Backoffice.apl_section_parcels'), 'headers' => [
        lang('Backoffice.apl_field_localisation'),
        lang('Backoffice.apl_field_superficie'),
        lang('Backoffice.apl_field_province'),
        lang('Backoffice.apl_field_commune'),
        lang('Backoffice.people_field_zone'),
        lang('Backoffice.people_field_colline'),
    ], 'rows' => array_map(static fn ($r) => [
        $r['localisation_parcelle'] ?? '—',
        $r['superficie_maitre_carreau'] ?? '—',
        $r['province_name'] ?? '—',
        $r['commune_name'] ?? '—',
        $r['zone_name'] ?? '—',
        $r['colline_name'] ?? '—',
    ], $parcels)],
    ['id' => 'apl-complainants-table', 'title' => lang('Backoffice.apl_field_complainants'), 'headers' => [lang('Backoffice.people_col_name'), lang('Backoffice.apl_col_role'), lang('Backoffice.people_col_cni'), lang('Backoffice.people_field_phone'), lang('Backoffice.people_field_email')], 'rows' => array_map($partyRow, $complainants)],
    ['id' => 'apl-defendants-table', 'title' => lang('Backoffice.apl_field_defendants'), 'headers' => [lang('Backoffice.people_col_name'), lang('Backoffice.apl_col_role'), lang('Backoffice.people_col_cni'), lang('Backoffice.people_field_phone'), lang('Backoffice.people_field_email')], 'rows' => array_map($partyRow, $defendants)],
    ['id' => 'apl-witnesses-table', 'title' => lang('Backoffice.apl_field_witnesses'), 'headers' => [lang('Backoffice.people_col_name'), lang('Backoffice.apl_col_role'), lang('Backoffice.people_col_cni'), lang('Backoffice.people_field_phone'), lang('Backoffice.people_field_email')], 'rows' => array_map($partyRow, $witnesses)],
    ['id' => 'apl-summons-table', 'title' => lang('Backoffice.apl_section_summons'), 'headers' => [lang('Backoffice.apl_col_filing'), lang('Backoffice.apl_field_court'), lang('Backoffice.apl_col_status')], 'rows' => array_map(static fn ($r) => [
        trim(($r['emise_le'] ?? '') . ' / ' . ($r['date_audience'] ?? ''), ' /'),
        $r['nom_juridiction'] ?? ($r['lieu_audience'] ?? '—'),
        $r['description_statut_convocation'] ?? '—',
    ], $summons)],
    ['id' => 'apl-hearings-table', 'title' => lang('Backoffice.apl_section_hearings'), 'headers' => [lang('Backoffice.apl_col_filing'), lang('Backoffice.apl_field_court'), lang('Backoffice.apl_col_status')], 'rows' => array_map(static fn ($r) => [
        trim(($r['date_audience'] ?? '') . ' ' . ($r['heure_audience'] ?? '')),
        $r['nom_juridiction'] ?? ($r['lieu_audience'] ?? '—'),
        $r['description_statut_audience'] ?? '—',
    ], $hearings)],
    ['id' => 'apl-attendance-table', 'title' => lang('Backoffice.apl_section_attendance'), 'headers' => [lang('Backoffice.apl_col_present'), lang('Backoffice.people_col_name'), lang('Backoffice.apl_col_role'), lang('Backoffice.apl_col_filing')], 'rows' => array_map(static fn ($r) => [
        $presentLabel($r['present'] ?? false),
        trim(($r['prenom_personne'] ?? '') . ' ' . ($r['nom_personne'] ?? '')),
        $r['description_role_personne'] ?? '—',
        $r['date_audience'] ?? '—',
    ], $attendance)],
    ['id' => 'apl-verdicts-table', 'title' => lang('Backoffice.apl_section_verdicts'), 'headers' => [lang('Backoffice.apl_col_filing'), lang('Backoffice.apl_field_court'), lang('Backoffice.apl_field_description')], 'rows' => array_map(static fn ($r) => [
        $r['date_verdict'] ?? '—',
        $r['nom_juridiction'] ?? '—',
        $r['resume'] ?? ($r['description_type_verdict'] ?? '—'),
    ], $verdicts)],
    ['id' => 'apl-notifications-table', 'title' => lang('Backoffice.apl_section_notifications'), 'headers' => [lang('Backoffice.apl_col_subject'), lang('Backoffice.apl_col_filing'), lang('Backoffice.apl_col_status')], 'rows' => array_map(static fn ($r) => [
        $r['sujet'] ?? '—',
        $r['envoye_le'] ?? '—',
        $r['description_statut_notification'] ?? '—',
    ], $notifications)],
    ['id' => 'apl-transfers-table', 'title' => lang('Backoffice.apl_section_transfers'), 'headers' => [lang('Backoffice.apl_col_filing'), lang('Backoffice.apl_field_court'), lang('Backoffice.apl_col_number')], 'rows' => array_map(static fn ($r) => [
        $r['date_transfert'] ?? '—',
        trim(($r['juridiction_source'] ?? '') . ' → ' . ($r['juridiction_dest'] ?? ''), ' →'),
        $r['numero_dossier_dest'] ?? '—',
    ], $transfers)],
];
$tableIds = array_column($tables, 'id');
$tableIds[] = 'apl-docs-table';
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

<section class="bo-panel bo-crud-panel mt-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h2 class="h5 mb-0"><?= esc(lang('Backoffice.apl_section_documents')) ?></h2>
        <label class="bo-table-search"><i class="bi bi-search"></i>
            <input type="search" class="form-control" id="apl-docs-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>">
        </label>
    </div>
    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="apl-docs-table" data-page-length="5" data-dom="lrtip">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.apl_col_doc_type')) ?></th>
                    <th><?= esc(lang('Backoffice.apl_col_filename')) ?></th>
                    <th><?= esc(lang('Backoffice.apl_col_filing')) ?></th>
                    <th><?= esc(lang('Backoffice.apl_col_uploaded_by')) ?></th>
                    <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($documents as $doc): ?>
                <?php $docId = (int) ($doc['document_plainte_id'] ?? 0); ?>
                <tr>
                    <td><?= esc($doc['libelle_type_document'] ?? ($doc['code_type_document'] ?? '—')) ?></td>
                    <td><?= esc($doc['nom_fichier'] ?? '—') ?></td>
                    <td><?= esc($doc['date_depot'] ?? '—') ?></td>
                    <td><?= esc(trim((string) ($doc['uploaded_by_name'] ?? '')) !== '' ? $doc['uploaded_by_name'] : '—') ?></td>
                    <td>
                        <div class="bo-action-group">
                            <?php if (can_access('backoffice/appeals/show')): ?>
                            <a class="btn btn-bo-icon" href="<?= site_url('backoffice/appeals/documents/' . $docId . '/view') ?>" target="_blank" rel="noopener" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.apl_doc_view'), 'attr') ?>"><i class="bi bi-eye"></i></a>
                            <a class="btn btn-bo-icon" href="<?= site_url('backoffice/appeals/documents/' . $docId . '/download') ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.apl_doc_download'), 'attr') ?>"><i class="bi bi-download"></i></a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
  <?= json_encode($tableIds) ?>.forEach((id) => {
    const input = document.getElementById(id + '-search');
    const table = document.getElementById(id);
    if (!input || !table || typeof DataTable === 'undefined') return;
    input.addEventListener('input', () => {
      if (window.jQuery && window.jQuery.fn.dataTable && window.jQuery.fn.dataTable.isDataTable('#' + id)) {
        window.jQuery('#' + id).DataTable().search(input.value).draw();
      }
    });
  });
});
</script>
<?= $this->endSection() ?>
