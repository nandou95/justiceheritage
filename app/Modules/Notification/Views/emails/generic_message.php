<?= $this->extend('Modules\Notification\Views\emails\layout') ?>

<?= $this->section('content') ?>

<p><?= esc(lang('Mail.hello', [$name ?? ''])) ?></p>

<?php if (! empty($subject)): ?>
    <p><strong><?= esc($subject) ?></strong></p>
<?php endif; ?>

<div class="jh-mail-message">
    <?= nl2br(esc((string) ($body ?? ''))) ?>
</div>

<?= $this->endSection() ?>
