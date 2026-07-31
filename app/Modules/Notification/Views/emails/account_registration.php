<?= $this->extend('Modules\Notification\Views\emails\layout') ?>



<?= $this->section('content') ?>

<p><?= esc(lang('Mail.hello', [$name])) ?></p>

<p><?= esc(lang('Mail.registration_body')) ?></p>

<table class="jh-mail-creds" cellpadding="0" cellspacing="0" role="presentation">

    <tr>

        <td><strong><?= esc(lang('Mail.label_username')) ?></strong></td>

        <td><?= esc($username) ?></td>

    </tr>

    <tr>

        <td><strong><?= esc(lang('Mail.label_password')) ?></strong></td>

        <td><?= esc($password) ?></td>

    </tr>

</table>

<p>

    <a class="jh-mail-btn" href="<?= esc($loginUrl, 'attr') ?>"><?= esc(lang('Mail.cta_login')) ?></a>

</p>

<p><?= esc(lang('Mail.link_fallback')) ?><br>

    <a href="<?= esc($loginUrl, 'attr') ?>"><?= esc($loginUrl) ?></a>

</p>

<p><?= esc(lang('Mail.registration_footer')) ?></p>

<?= $this->endSection() ?>

