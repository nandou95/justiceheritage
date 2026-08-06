<?= $this->extend('Modules\Notification\Views\emails\layout') ?>

<?= $this->section('content') ?>

<?php
$loginId = trim((string) ($loginId ?? ($cni ?: ($matricule ?: $email))));
?>

<p><?= esc(lang('Mail.hello', [$name])) ?></p>

<p><?= esc(lang('Mail.bo_user_registration_body')) ?></p>

<table class="jh-mail-creds" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td><strong><?= esc(lang('Mail.label_login_identifier')) ?></strong></td>
        <td><?= esc($loginId) ?></td>
    </tr>
    <?php if (trim((string) ($cni ?? '')) !== ''): ?>
    <tr>
        <td><strong><?= esc(lang('Mail.label_login_cni')) ?></strong></td>
        <td><?= esc($cni) ?></td>
    </tr>
    <?php endif; ?>
    <?php if (trim((string) ($matricule ?? '')) !== ''): ?>
    <tr>
        <td><strong><?= esc(lang('Mail.label_login_matricule')) ?></strong></td>
        <td><?= esc($matricule) ?></td>
    </tr>
    <?php endif; ?>
    <tr>
        <td><strong><?= esc(lang('Mail.label_login_email')) ?></strong></td>
        <td><?= esc($email) ?></td>
    </tr>
    <tr>
        <td><strong><?= esc(lang('Mail.label_temp_password')) ?></strong></td>
        <td><?= esc($password) ?></td>
    </tr>
</table>

<p>
    <a class="jh-mail-btn" href="<?= esc($loginUrl, 'attr') ?>"><?= esc(lang('Mail.cta_bo_login')) ?></a>
</p>

<p><?= esc(lang('Mail.link_fallback')) ?><br>
    <a href="<?= esc($loginUrl, 'attr') ?>"><?= esc($loginUrl) ?></a>
</p>

<p><?= esc(lang('Mail.bo_user_registration_footer')) ?></p>

<?= $this->endSection() ?>
