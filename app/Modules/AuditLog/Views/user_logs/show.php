<?= $this->extend('layouts/backoffice') ?>

<?= $this->section('content') ?>

<?= view('Modules\AuditLog\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_logs')) ?></p>
        <h1><?= esc(lang('Backoffice.logs_user_details_title')) ?></h1>
        <p><?= esc(lang('Backoffice.logs_user_details_lead')) ?></p>
    </div>
    <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/system-logs/users') ?>">
        <i class="bi bi-arrow-left" aria-hidden="true"></i>
        <?= esc(lang('Backoffice.logs_back_list')) ?>
    </a>
</section>

<section class="bo-panel bo-crud-panel">
    <h2 class="bo-form-section-title"><?= esc(lang('Backoffice.logs_section_user')) ?></h2>
    <dl class="bo-detail-list">
        <div><dt><?= esc(lang('Backoffice.logs_col_user')) ?></dt><dd><?= esc($record['user_name']) ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.logs_col_profile')) ?></dt><dd><?= esc($record['profile_name']) ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.logs_col_court')) ?></dt><dd><?= esc($record['court_name']) ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.users_col_matricule')) ?></dt><dd><?= esc($record['user_matricule'] ?: '—') ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.users_col_contact')) ?></dt><dd><?= esc($record['user_email'] ?: '—') ?><br><small><?= esc($record['user_phone'] ?: '') ?></small></dd></div>
        <div><dt><?= esc(lang('Backoffice.logs_field_username')) ?></dt><dd><?= esc($record['user_username'] ?: '—') ?></dd></div>
    </dl>

    <h2 class="bo-form-section-title mt-4"><?= esc(lang('Backoffice.logs_section_event')) ?></h2>
    <dl class="bo-detail-list">
        <div><dt><?= esc(lang('Backoffice.logs_col_action')) ?></dt><dd><?= esc($record['action']) ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.logs_col_table')) ?></dt><dd><code class="bo-route-code"><?= esc($record['table_cible']) ?></code></dd></div>
        <div><dt><?= esc(lang('Backoffice.logs_col_record_id')) ?></dt><dd><?= esc($record['enregistrement_id'] !== null ? (string) $record['enregistrement_id'] : '—') ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.logs_col_datetime')) ?></dt><dd><?= esc($record['created_at']) ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.logs_col_ip')) ?></dt><dd><?= esc($record['adresse_ip'] ?: '—') ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.logs_col_browser')) ?></dt><dd><?= esc($record['user_agent'] ?: '—') ?></dd></div>
    </dl>

    <div class="row g-3 mt-2">
        <div class="col-12 col-lg-6">
            <h2 class="bo-form-section-title"><?= esc(lang('Backoffice.logs_field_old_values')) ?></h2>
            <pre class="bo-json-block"><?= esc($record['anciennes_valeurs'] !== '' ? $record['anciennes_valeurs'] : '—') ?></pre>
        </div>
        <div class="col-12 col-lg-6">
            <h2 class="bo-form-section-title"><?= esc(lang('Backoffice.logs_field_new_values')) ?></h2>
            <pre class="bo-json-block"><?= esc($record['nouvelles_valeurs'] !== '' ? $record['nouvelles_valeurs'] : '—') ?></pre>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
