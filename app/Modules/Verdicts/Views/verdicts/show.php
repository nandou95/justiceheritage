<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<?php
$appealed = filter_var($record['recours_exerce'] ?? false, FILTER_VALIDATE_BOOLEAN);
$hasReport = ! empty($record['upload_rapport_verdict']);
$partyRow = static fn (array $r): array => [
    trim(($r['prenom_personne'] ?? '') . ' ' . ($r['nom_personne'] ?? '')),
    $r['description_role_personne'] ?? '—',
    $r['numero_cni'] ?? '—',
    $r['telephone'] ?? '—',
    $r['email'] ?? '—',
];
?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_verdicts')) ?></p>
        <h1><?= esc(lang('Backoffice.vrd_details_title')) ?></h1>
        <p><code class="bo-route-code"><?= esc($record['numero_dossier'] ?? '') ?></code> — <?= esc($record['description_type_verdict'] ?? '') ?></p>
    </div>
    <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/verdicts') ?>"><i class="bi bi-arrow-left"></i> <?= esc(lang('Backoffice.vrd_back_list')) ?></a>
</section>

<section class="bo-panel bo-crud-panel">
    <div class="bo-detail-grid">
        <article>
            <h2><?= esc(lang('Backoffice.vrd_section_verdict')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.vrd_field_type')) ?></dt><dd><?= esc($record['description_type_verdict'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.vrd_field_date')) ?></dt><dd><?= esc($record['date_verdict'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.vrd_field_court')) ?></dt><dd><?= esc($record['nom_juridiction'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.vrd_field_level')) ?></dt><dd><?= esc($record['desc_niveau_juridiction'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.vrd_field_resume')) ?></dt><dd><?= esc($record['resume'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.vrd_field_dispositif')) ?></dt><dd><?= esc($record['dispositif'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.vrd_field_deadline')) ?></dt><dd><?= esc($record['date_limite_recours'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.vrd_field_appealed')) ?></dt><dd><?= esc($appealed ? lang('Backoffice.yes') : lang('Backoffice.no')) ?></dd></div>
                <div>
                    <dt><?= esc(lang('Backoffice.vrd_field_report')) ?></dt>
                    <dd>
                        <?php if ($hasReport): ?>
                            <a class="btn btn-bo-icon" href="<?= site_url('backoffice/verdicts/' . (int) $record['verdict_id'] . '/report/view') ?>" target="_blank" rel="noopener" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.vrd_doc_view'), 'attr') ?>"><i class="bi bi-eye"></i></a>
                            <a class="btn btn-bo-icon" href="<?= site_url('backoffice/verdicts/' . (int) $record['verdict_id'] . '/report/download') ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.vrd_doc_download'), 'attr') ?>"><i class="bi bi-download"></i></a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </dd>
                </div>
            </dl>
        </article>
        <article>
            <h2><?= esc(lang('Backoffice.vrd_section_complaint')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.vrd_col_case')) ?></dt><dd><?= esc($record['numero_dossier'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.vrd_col_subject')) ?></dt><dd><?= esc($record['objet'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.vrd_field_description')) ?></dt><dd><?= esc($record['plainte_description'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.vrd_col_filing')) ?></dt><dd><?= esc($record['date_depot'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.vrd_col_stage')) ?></dt><dd><?= esc($record['description_etape_plainte'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.vrd_col_complaint_status')) ?></dt><dd><?= esc($record['description_statut_plainte'] ?? '—') ?></dd></div>
            </dl>
        </article>
        <article>
            <h2><?= esc(lang('Backoffice.vrd_section_hearing')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.vrd_col_hearing_date')) ?></dt><dd><?= esc(trim(($record['date_audience'] ?? '') . ' ' . substr((string) ($record['heure_audience'] ?? ''), 0, 5)) ?: '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.vrd_col_venue')) ?></dt><dd><?= esc($record['lieu_audience'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.vrd_col_hearing_report')) ?></dt><dd><?= esc($record['audience_plainte_rapport'] ?? ($record['audience_rapport'] ?? '—')) ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.vrd_col_hearing_status')) ?></dt><dd><?= esc($record['description_statut_audience'] ?? '—') ?></dd></div>
            </dl>
        </article>
    </div>
</section>

<?php
$tables = [
    ['id' => 'vrd-judges-table', 'title' => lang('Backoffice.vrd_section_judges'), 'headers' => [lang('Backoffice.people_col_name'), lang('Backoffice.vrd_col_profile'), lang('Backoffice.vrd_col_court')], 'rows' => array_map(static fn ($r) => [
        trim((string) ($r['full_name'] ?? '')) ?: '—',
        $r['libelle_profil'] ?? '—',
        $r['nom_juridiction'] ?? '—',
    ], $judges)],
    ['id' => 'vrd-staff-table', 'title' => lang('Backoffice.vrd_section_staff'), 'headers' => [lang('Backoffice.people_col_name'), lang('Backoffice.vrd_col_profile'), lang('Backoffice.vrd_col_status')], 'rows' => array_map(static fn ($r) => [
        trim((string) ($r['assignee_name'] ?? '')) ?: '—',
        $r['libelle_profil'] ?? '—',
        filter_var($r['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN) ? lang('Backoffice.status_active') : lang('Backoffice.status_inactive'),
    ], $staff)],
    ['id' => 'vrd-complainants-table', 'title' => lang('Backoffice.vrd_field_complainants'), 'headers' => [lang('Backoffice.people_col_name'), lang('Backoffice.vrd_col_role'), lang('Backoffice.people_col_cni'), lang('Backoffice.people_field_phone'), lang('Backoffice.people_field_email')], 'rows' => array_map($partyRow, $complainants)],
    ['id' => 'vrd-defendants-table', 'title' => lang('Backoffice.vrd_field_defendants'), 'headers' => [lang('Backoffice.people_col_name'), lang('Backoffice.vrd_col_role'), lang('Backoffice.people_col_cni'), lang('Backoffice.people_field_phone'), lang('Backoffice.people_field_email')], 'rows' => array_map($partyRow, $defendants)],
    ['id' => 'vrd-witnesses-table', 'title' => lang('Backoffice.vrd_field_witnesses'), 'headers' => [lang('Backoffice.people_col_name'), lang('Backoffice.vrd_col_role'), lang('Backoffice.people_col_cni'), lang('Backoffice.people_field_phone'), lang('Backoffice.people_field_email')], 'rows' => array_map($partyRow, $witnesses)],
    ['id' => 'vrd-hearing-docs-table', 'title' => lang('Backoffice.vrd_section_hearing_docs'), 'headers' => [lang('Backoffice.vrd_col_case'), lang('Backoffice.vrd_col_doc_desc'), lang('Backoffice.vrd_col_doc_by'), lang('Backoffice.vrd_col_doc_date')], 'rows' => array_map(static function ($r) {
        $obs = preg_replace('/\n__FILE__:.*$/', '', (string) ($r['observation'] ?? '')) ?? '';
        return [
            $r['numero_dossier'] ?? '—',
            $obs !== '' ? $obs : '—',
            trim((string) ($r['uploaded_by_name'] ?? '')) ?: '—',
            $r['enregistre_le'] ?? '—',
        ];
    }, $hearing_docs)],
    ['id' => 'vrd-complaint-docs-table', 'title' => lang('Backoffice.vrd_section_complaint_docs'), 'headers' => [lang('Backoffice.vrd_col_doc_type'), lang('Backoffice.vrd_col_filename'), lang('Backoffice.vrd_col_doc_date')], 'rows' => array_map(static fn ($r) => [
        $r['libelle_type_document'] ?? ($r['code_type_document'] ?? '—'),
        $r['nom_fichier'] ?? '—',
        $r['date_depot'] ?? '—',
    ], $complaint_docs)],
    ['id' => 'vrd-appeals-table', 'title' => lang('Backoffice.vrd_section_appeals'), 'headers' => [lang('Backoffice.vrd_col_appeal_number'), lang('Backoffice.vrd_col_appeal_date'), lang('Backoffice.vrd_col_court'), lang('Backoffice.vrd_col_appeal_status')], 'rows' => array_map(static fn ($r) => [
        $r['nouvelle_plainte_numero'] ?? ('#' . ($r['recours_id'] ?? '')),
        $r['date_recours'] ?? '—',
        trim(($r['desc_niveau_juridiction'] ?? '') . ' / ' . ($r['nom_juridiction'] ?? ''), ' /') ?: '—',
        filter_var($r['dans_les_delais'] ?? false, FILTER_VALIDATE_BOOLEAN) ? lang('Backoffice.apl_field_within_deadline') : '—',
    ], $appeals)],
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
