<?= $this->extend('Modules\Notification\Views\emails\layout') ?>



<?= $this->section('content') ?>

<p><?= esc(lang('Mail.hello', [$name])) ?></p>

<p><?= esc(lang('Mail.verification_body')) ?></p>

<p>

    <a class="jh-mail-btn" href="<?= esc($verifyUrl, 'attr') ?>"><?= esc(lang('Mail.cta_verify')) ?></a>

</p>

<p><?= esc(lang('Mail.link_fallback')) ?><br>

    <a href="<?= esc($verifyUrl, 'attr') ?>"><?= esc($verifyUrl) ?></a>

</p>

<?= $this->endSection() ?>

