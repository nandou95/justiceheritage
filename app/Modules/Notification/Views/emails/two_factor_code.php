<?= $this->extend('Modules\Notification\Views\emails\layout') ?>



<?= $this->section('content') ?>

<p><?= esc(lang('Mail.hello', [$name])) ?></p>

<p><?= esc(lang('Mail.two_factor_body')) ?></p>

<p style="font-size:28px;letter-spacing:0.35em;font-weight:700;color:#0a3227;margin:18px 0;text-align:center;"><?= esc($code) ?></p>

<p><strong><?= esc(lang('Mail.two_factor_validity')) ?></strong></p>

<p><?= esc(lang('Mail.two_factor_warning')) ?></p>

<p><?= esc(lang('Mail.two_factor_footer')) ?></p>

<?= $this->endSection() ?>

