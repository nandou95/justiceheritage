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
?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_summons')) ?></p>
        <h1><?= esc(lang('Backoffice.sum_details_title')) ?></h1>
        <p><code class="bo-route-code"><?= esc($record['numero_dossier'] ?? '') ?></code> — <?= esc($record['objet'] ?? '') ?></p>
    </div>
    <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/summons') ?>"><i class="bi bi-arrow-left"></i> <?= esc(lang('Backoffice.sum_back_list')) ?></a>
</section>

<section class="bo-panel bo-crud-panel">
    <div class="bo-detail-grid">
        <article>
            <h2><?= esc(lang('Backoffice.sum_section_summons')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.sum_col_hearing')) ?></dt><dd><?= esc($hearingAt !== '' ? $hearingAt : '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.sum_field_court')) ?></dt><dd><?= esc(trim(($record['desc_niveau_juridiction'] ?? '') . ' / ' . ($record['nom_juridiction'] ?? ''), ' /') ?: '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.sum_col_location')) ?></dt><dd><?= esc($location !== '' ? $location : '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.sum_field_venue')) ?></dt><dd><?= esc($record['lieu_audience'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.sum_col_status')) ?></dt><dd><?= esc($record['description_statut_convocation'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.sum_field_issued_on')) ?></dt><dd><?= esc($record['emise_le'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.sum_field_issued_by')) ?></dt><dd><?= esc(trim((string) ($record['issued_by_name'] ?? '')) !== '' ? $record['issued_by_name'] : '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.sum_field_observations')) ?></dt><dd><?= esc($record['observations'] ?? '—') ?></dd></div>
            </dl>
        </article>
        <article>
            <h2><?= esc(lang('Backoffice.sum_section_complaint')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.sum_col_case')) ?></dt><dd><?= esc($record['numero_dossier'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.sum_col_subject')) ?></dt><dd><?= esc($record['objet'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.sum_field_description')) ?></dt><dd><?= esc($record['plainte_description'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.sum_col_court')) ?></dt><dd><?= esc(trim(($record['plainte_niveau'] ?? '') . ' / ' . ($record['plainte_juridiction'] ?? ''), ' /') ?: '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.sum_col_filing')) ?></dt><dd><?= esc($record['date_depot'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.sum_col_stage')) ?></dt><dd><?= esc($record['description_etape_plainte'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.sum_col_complaint_status')) ?></dt><dd><?= esc($record['description_statut_plainte'] ?? '—') ?></dd></div>
            </dl>
        </article>
    </div>
</section>

<section class="bo-panel bo-crud-panel mt-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h2 class="h5 mb-0"><?= esc(lang('Backoffice.sum_section_recipients')) ?></h2>
        <label class="bo-table-search"><i class="bi bi-search"></i>
            <input type="search" class="form-control" id="sum-recipients-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>">
        </label>
    </div>
    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="sum-recipients-table" data-page-length="10" data-dom="lrtip">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.people_col_name')) ?></th>
                    <th><?= esc(lang('Backoffice.sum_col_role')) ?></th>
                    <th><?= esc(lang('Backoffice.people_col_cni')) ?></th>
                    <th><?= esc(lang('Backoffice.people_field_phone')) ?></th>
                    <th><?= esc(lang('Backoffice.people_field_email')) ?></th>
                    <th><?= esc(lang('Backoffice.sum_col_status')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($recipients as $r): ?>
                <tr>
                    <td><?= esc(trim(($r['prenom_personne'] ?? '') . ' ' . ($r['nom_personne'] ?? ''))) ?></td>
                    <td><?= esc($r['description_role_personne'] ?? '—') ?></td>
                    <td><?= esc($r['numero_cni'] ?? '—') ?></td>
                    <td><?= esc($r['telephone'] ?? '—') ?></td>
                    <td><?= esc($r['email'] ?? '—') ?></td>
                    <td><?= esc($r['description_statut_convocation'] ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('sum-recipients-table-search');
  const id = 'sum-recipients-table';
  if (!input || typeof DataTable === 'undefined') return;
  input.addEventListener('input', () => {
    if (window.jQuery && window.jQuery.fn.dataTable && window.jQuery.fn.dataTable.isDataTable('#' + id)) {
      window.jQuery('#' + id).DataTable().search(input.value).draw();
    }
  });
});
</script>
<?= $this->endSection() ?>
