<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.notifications')) ?></p>
        <h1><?= esc(lang('Backoffice.inbox_details_title')) ?></h1>
        <p><?= esc(lang('Backoffice.ntf_details_lead')) ?></p>
    </div>
    <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/my/notifications') ?>">
        <i class="bi bi-arrow-left"></i> <?= esc(lang('Backoffice.inbox_back')) ?>
    </a>
</section>

<section class="bo-panel bo-crud-panel">
    <dl class="bo-detail-list">
        <div><dt><?= esc(lang('Backoffice.ntf_col_subject')) ?></dt><dd><?= esc($record['subject']) ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.ntf_col_channel')) ?></dt><dd><?= esc($record['channel']) ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.ntf_col_status')) ?></dt><dd><?= esc($record['status'] ?: '—') ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.inbox_col_date')) ?></dt><dd><?= esc($record['created_fmt']) ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.ntf_col_sent')) ?></dt><dd><?= esc($record['sent_at']) ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.ntf_col_read')) ?></dt><dd><?= esc($record['read_at']) ?></dd></div>
    </dl>
    <div class="bo-message-body mt-3">
        <h3 class="h6"><?= esc(lang('Backoffice.ntf_field_body')) ?></h3>
        <div class="bo-json-block"><?= nl2br(esc($record['body'])) ?></div>
    </div>
</section>
<?= $this->endSection() ?>
