<?= $this->extend('Modules\Notification\Views\emails\layout') ?>

<?= $this->section('content') ?>
<p><?= esc(lang('Mail.hello', [$name])) ?></p>
<p><?= esc($bodyText) ?></p>
<p><strong><?= esc($complaintNumber) ?></strong></p>
<p>
    <a class="jh-mail-btn" href="<?= esc($portalUrl, 'attr') ?>"><?= esc(lang('Mail.cta_view_case')) ?></a>
</p>
<?= $this->endSection() ?>
