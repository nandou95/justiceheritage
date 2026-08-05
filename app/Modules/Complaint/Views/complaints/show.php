<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_complaints_list')) ?></p>
        <h1><?= esc(lang('Backoffice.cmp_details_title')) ?></h1>
        <p><code class="bo-route-code"><?= esc($record['numero_dossier'] ?? '') ?></code> — <?= esc($record['objet'] ?? '') ?></p>
    </div>
    <div class="bo-crud-head-actions">
        <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/complaints') ?>"><i class="bi bi-arrow-left"></i> <?= esc(lang('Backoffice.cmp_back_list')) ?></a>
        <a class="btn btn-bo-primary" href="<?= site_url('backoffice/complaints/' . (int) $record['plainte_id'] . '/edit') ?>"><i class="bi bi-pencil-square"></i> <?= esc(lang('Backoffice.cmp_action_edit')) ?></a>
    </div>
</section>

<section class="bo-panel bo-crud-panel">
    <div class="bo-detail-grid">
        <article>
            <h2><?= esc(lang('Backoffice.cmp_section_general')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.cmp_col_case')) ?></dt><dd><?= esc($record['numero_dossier'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.cmp_field_objet')) ?></dt><dd><?= esc($record['objet'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.cmp_field_description')) ?></dt><dd><?= esc($record['description'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.cmp_col_filing')) ?></dt><dd><?= esc($record['date_depot'] ?? '—') ?></dd></div>
            </dl>
        </article>
        <article>
            <h2><?= esc(lang('Backoffice.cmp_section_status')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.cmp_col_status')) ?></dt><dd><?= esc($record['description_statut_plainte'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.cmp_col_stage')) ?></dt><dd><?= esc($record['description_etape_plainte'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.cmp_field_level')) ?></dt><dd><?= esc($record['desc_niveau_juridiction'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.cmp_field_court')) ?></dt><dd><?= esc($record['nom_juridiction'] ?? '—') ?></dd></div>
            </dl>
        </article>
    </div>
</section>

<?php
$tables = [
    ['id' => 'cmp-parcels-table', 'title' => lang('Backoffice.cmp_section_parcels'), 'headers' => [lang('Backoffice.cmp_field_localisation'), lang('Backoffice.cmp_field_superficie'), lang('Backoffice.people_col_birthplace')], 'rows' => array_map(static fn ($r) => [
        $r['localisation_parcelle'] ?? '—',
        $r['superficie_maitre_carreau'] ?? '—',
        implode(' / ', array_filter([$r['province_name'] ?? null, $r['commune_name'] ?? null, $r['zone_name'] ?? null, $r['colline_name'] ?? null])),
    ], $parcels)],
    ['id' => 'cmp-complainants-table', 'title' => lang('Backoffice.cmp_field_complainants'), 'headers' => [lang('Backoffice.people_col_name'), lang('Backoffice.people_col_cni'), lang('Backoffice.people_col_contact')], 'rows' => array_map(static fn ($r) => [
        trim(($r['prenom_personne'] ?? '') . ' ' . ($r['nom_personne'] ?? '')),
        $r['numero_cni'] ?? '—',
        trim(($r['email'] ?? '') . ' / ' . ($r['telephone'] ?? ''), ' /'),
    ], $complainants)],
    ['id' => 'cmp-defendants-table', 'title' => lang('Backoffice.cmp_field_defendants'), 'headers' => [lang('Backoffice.people_col_name'), lang('Backoffice.people_col_cni'), lang('Backoffice.people_col_contact')], 'rows' => array_map(static fn ($r) => [
        trim(($r['prenom_personne'] ?? '') . ' ' . ($r['nom_personne'] ?? '')),
        $r['numero_cni'] ?? '—',
        trim(($r['email'] ?? '') . ' / ' . ($r['telephone'] ?? ''), ' /'),
    ], $defendants)],
    ['id' => 'cmp-witnesses-table', 'title' => lang('Backoffice.cmp_field_witnesses'), 'headers' => [lang('Backoffice.people_col_name'), lang('Backoffice.people_col_cni'), lang('Backoffice.people_col_contact')], 'rows' => array_map(static fn ($r) => [
        trim(($r['prenom_personne'] ?? '') . ' ' . ($r['nom_personne'] ?? '')),
        $r['numero_cni'] ?? '—',
        trim(($r['email'] ?? '') . ' / ' . ($r['telephone'] ?? ''), ' /'),
    ], $witnesses)],
    ['id' => 'cmp-docs-table', 'title' => lang('Backoffice.cmp_section_documents'), 'headers' => [lang('Backoffice.dt_col_code'), lang('Backoffice.dt_col_description'), lang('Backoffice.cmp_col_filing')], 'rows' => array_map(static fn ($r) => [
        $r['code_type_document'] ?? '—',
        $r['libelle_type_document'] ?? ($r['nom_fichier'] ?? '—'),
        $r['date_depot'] ?? '—',
    ], $documents)],
    ['id' => 'cmp-summons-table', 'title' => lang('Backoffice.cmp_section_summons'), 'headers' => [lang('Backoffice.cmp_col_filing'), lang('Backoffice.cmp_field_court'), lang('Backoffice.cmp_col_status')], 'rows' => array_map(static fn ($r) => [
        trim(($r['emise_le'] ?? '') . ' / ' . ($r['date_audience'] ?? ''), ' /'),
        $r['nom_juridiction'] ?? ($r['lieu_audience'] ?? '—'),
        $r['description_statut_convocation'] ?? '—',
    ], $summons)],
    ['id' => 'cmp-hearings-table', 'title' => lang('Backoffice.cmp_section_hearings'), 'headers' => [lang('Backoffice.cmp_col_filing'), lang('Backoffice.cmp_field_court'), lang('Backoffice.cmp_col_status')], 'rows' => array_map(static fn ($r) => [
        trim(($r['date_audience'] ?? '') . ' ' . ($r['heure_audience'] ?? '')),
        $r['nom_juridiction'] ?? ($r['lieu_audience'] ?? '—'),
        $r['description_statut_audience'] ?? '—',
    ], $hearings)],
    ['id' => 'cmp-verdicts-table', 'title' => lang('Backoffice.cmp_section_verdicts'), 'headers' => [lang('Backoffice.cmp_col_filing'), lang('Backoffice.cmp_field_court'), lang('Backoffice.cmp_field_description')], 'rows' => array_map(static fn ($r) => [
        $r['date_verdict'] ?? '—',
        $r['nom_juridiction'] ?? '—',
        $r['resume'] ?? ($r['description_type_verdict'] ?? '—'),
    ], $verdicts)],
    ['id' => 'cmp-appeals-table', 'title' => lang('Backoffice.cmp_section_appeals'), 'headers' => [lang('Backoffice.cmp_col_filing'), lang('Backoffice.cmp_field_level'), lang('Backoffice.cmp_col_case')], 'rows' => array_map(static fn ($r) => [
        $r['date_recours'] ?? '—',
        $r['desc_niveau_juridiction'] ?? '—',
        $r['nouvelle_plainte_numero'] ?? '—',
    ], $appeals)],
    ['id' => 'cmp-transfers-table', 'title' => lang('Backoffice.cmp_section_transfers'), 'headers' => [lang('Backoffice.cmp_col_filing'), lang('Backoffice.cmp_field_court'), lang('Backoffice.cmp_col_case')], 'rows' => array_map(static fn ($r) => [
        $r['date_transfert'] ?? '—',
        trim(($r['juridiction_source'] ?? '') . ' → ' . ($r['juridiction_dest'] ?? ''), ' →'),
        $r['numero_dossier_dest'] ?? '—',
    ], $transfers)],
    ['id' => 'cmp-history-table', 'title' => lang('Backoffice.cmp_section_history'), 'headers' => [lang('Backoffice.cmp_col_filing'), 'Action', 'Table'], 'rows' => array_map(static fn ($r) => [
        $r['created_at'] ?? '—',
        $r['action'] ?? '—',
        $r['table_cible'] ?? '—',
    ], $history)],
];
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
  [
    'cmp-parcels-table','cmp-complainants-table','cmp-defendants-table','cmp-witnesses-table','cmp-docs-table',
    'cmp-summons-table','cmp-hearings-table','cmp-verdicts-table','cmp-appeals-table','cmp-transfers-table','cmp-history-table'
  ].forEach((id) => {
    const input = document.getElementById(id + '-search');
    const table = document.getElementById(id);
    if (!input || !table || typeof DataTable === 'undefined') return;
    const dt = DataTable.getInstance?.(table) || (typeof DataTable.isDataTable === 'function' && DataTable.isDataTable(table) ? new DataTable.Api(table) : null);
    // Search bound via backoffice.js bindTableSearch when present; fallback:
    input.addEventListener('input', () => {
      if (window.jQuery && window.jQuery.fn.dataTable && window.jQuery.fn.dataTable.isDataTable('#' + id)) {
        window.jQuery('#' + id).DataTable().search(input.value).draw();
      }
    });
  });
});
</script>
<?= $this->endSection() ?>
