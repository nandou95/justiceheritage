<?= $this->extend('Modules\Notification\Views\emails\layout') ?>



<?= $this->section('content') ?>

<p><?= esc(lang('Mail.hello', [$name])) ?></p>

<p><?= esc(lang('Mail.appeal_submitted_body', [$appealLevel, $caseNumber])) ?></p>

<p>

    <a class="jh-mail-btn" href="<?= esc($portalUrl, 'attr') ?>"><?= esc(lang('Mail.cta_portal')) ?></a>

</p>

<?= $this->endSection() ?>

