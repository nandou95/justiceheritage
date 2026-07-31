<?= $this->extend('Modules\Notification\Views\emails\layout') ?>



<?= $this->section('content') ?>

<p><?= esc(lang('Mail.hello', [$name])) ?></p>

<p><?= esc(lang('Mail.complaint_submitted_body', [$complaintNumber])) ?></p>

<p><strong><?= esc($complaintTitle) ?></strong></p>

<p>

    <a class="jh-mail-btn" href="<?= esc($portalUrl, 'attr') ?>"><?= esc(lang('Mail.cta_view_complaints')) ?></a>

</p>

<?= $this->endSection() ?>

