<?= $this->extend('layouts/backoffice') ?>

<?= $this->section('content') ?>

<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_notifications')) ?></p>
        <h1><?= esc(lang('Backoffice.ntf_user_details_title')) ?></h1>
        <p><?= esc(lang('Backoffice.ntf_details_lead')) ?></p>
    </div>
    <div class="bo-crud-head-actions">
        <form method="post" action="<?= site_url('backoffice/notifications/users/' . $record['id'] . '/resend') ?>" onsubmit="return confirm('<?= esc(lang('Backoffice.ntf_resend_confirm'), 'js') ?>');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-bo-primary">
                <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
                <?= esc(lang('Backoffice.ntf_action_resend')) ?>
            </button>
        </form>
        <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/notifications/users') ?>">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            <?= esc(lang('Backoffice.ntf_back_list')) ?>
        </a>
    </div>
</section>

<section class="bo-panel bo-crud-panel">
    <h2 class="bo-form-section-title"><?= esc(lang('Backoffice.ntf_section_user')) ?></h2>
    <dl class="bo-detail-list">
        <div><dt><?= esc(lang('Backoffice.ntf_col_recipient')) ?></dt><dd><?= esc($record['recipient']) ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.ntf_col_profile')) ?></dt><dd><?= esc($record['profile_name']) ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.ntf_col_court')) ?></dt><dd><?= esc($record['court_name']) ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.users_col_matricule')) ?></dt><dd><?= esc($record['recipient_matricule'] ?: '—') ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.users_col_contact')) ?></dt><dd><?= esc($record['recipient_email'] ?: '—') ?><br><small><?= esc($record['recipient_phone'] ?: '') ?></small></dd></div>
    </dl>

    <h2 class="bo-form-section-title mt-4"><?= esc(lang('Backoffice.ntf_section_message')) ?></h2>
    <dl class="bo-detail-list">
        <div><dt><?= esc(lang('Backoffice.ntf_col_channel')) ?></dt><dd><?= esc($record['channel']) ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.ntf_col_status')) ?></dt><dd><?= esc($record['status_label']) ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.ntf_col_sent')) ?></dt><dd><?= esc($record['sent_at']) ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.ntf_col_read')) ?></dt><dd><?= esc($record['read_at']) ?></dd></div>
        <div class="bo-detail-span"><dt><?= esc(lang('Backoffice.ntf_col_subject')) ?></dt><dd><?= esc($record['subject']) ?></dd></div>
    </dl>
    <div class="bo-message-body mt-3">
        <h3 class="h6"><?= esc(lang('Backoffice.ntf_field_body')) ?></h3>
        <div class="bo-json-block"><?= nl2br(esc($record['body'])) ?></div>
    </div>
</section>

<?= $this->endSection() ?>
