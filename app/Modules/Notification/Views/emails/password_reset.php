<?= $this->extend('Modules\Notification\Views\emails\layout') ?>



<?= $this->section('content') ?>

<p><?= esc(lang('Mail.hello', [$name])) ?></p>

<p><?= esc(lang('Mail.password_reset_body')) ?></p>

<p>

    <a class="jh-mail-btn" href="<?= esc($resetUrl, 'attr') ?>"><?= esc(lang('Mail.cta_reset')) ?></a>

</p>

<p><?= esc(lang('Mail.link_fallback')) ?><br>

    <a href="<?= esc($resetUrl, 'attr') ?>"><?= esc($resetUrl) ?></a>

</p>

<p><?= esc(lang('Mail.password_reset_footer')) ?></p>

<?= $this->endSection() ?>

