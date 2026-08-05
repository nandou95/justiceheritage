<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>
<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_court_jurisdictions')) ?></p>
        <h1><?= esc(lang('Backoffice.cj_details_title')) ?></h1>
        <p><?= esc($record['nom_juridiction'] ?? '') ?></p>
    </div>
    <div class="bo-crud-head-actions">
        <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/court-jurisdictions') ?>"><i class="bi bi-arrow-left"></i> <?= esc(lang('Backoffice.cj_back_list')) ?></a>
        <a class="btn btn-bo-primary" href="<?= site_url('backoffice/court-jurisdictions/' . (int) $record['juridiction_id'] . '/edit') ?>"><i class="bi bi-pencil-square"></i> <?= esc(lang('Backoffice.cj_action_edit')) ?></a>
    </div>
</section>
<section class="bo-panel bo-crud-panel">
    <div class="bo-detail-grid">
        <article>
            <h2><?= esc(lang('Backoffice.cj_section_identity')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.cj_field_code')) ?></dt><dd><code class="bo-route-code"><?= esc($record['code_juridiction'] ?? '—') ?></code></dd></div>
                <div><dt><?= esc(lang('Backoffice.cj_field_name')) ?></dt><dd><?= esc($record['nom_juridiction'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.cj_field_level')) ?></dt><dd><?= esc($record['desc_niveau_juridiction'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.cj_col_status')) ?></dt><dd><span class="bo-status-pill <?= ! empty($record['is_active']) ? 'is-active' : 'is-inactive' ?>"><?= esc($record['status'] ?? '—') ?></span></dd></div>
            </dl>
        </article>
        <article>
            <h2><?= esc(lang('Backoffice.cj_section_contact')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.cj_field_phone')) ?></dt><dd><?= esc($record['telephone'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.cj_field_email')) ?></dt><dd><?= esc($record['email'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.cj_field_address')) ?></dt><dd><?= esc($record['adresse'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.users_field_created')) ?></dt><dd><?= esc($record['created_at'] ?? '—') ?></dd></div>
            </dl>
        </article>
        <article>
            <h2><?= esc(lang('Backoffice.cj_section_location')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.cj_field_province')) ?></dt><dd><?= esc($record['province_name'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.cj_field_commune')) ?></dt><dd><?= esc($record['commune_name'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.cj_field_zone')) ?></dt><dd><?= esc($record['zone_name'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.cj_field_colline')) ?></dt><dd><?= esc($record['colline_name'] ?? '—') ?></dd></div>
            </dl>
        </article>
    </div>
</section>
<?= $this->endSection() ?>
