<?php
/**
 * Document preview + download block for Details pages.
 *
 * @var string|null $label
 * @var string|null $viewUrl
 * @var string|null $downloadUrl
 * @var string|null $filename
 * @var bool $isImage
 */
$label       = $label ?? lang('Backoffice.detail_document');
$viewUrl     = $viewUrl ?? null;
$downloadUrl = $downloadUrl ?? null;
$filename    = $filename ?? null;
$isImage     = $isImage ?? false;

if (! $viewUrl && ! $downloadUrl) {
    echo '—';

    return;
}
?>

<div class="bo-detail-document">
    <?php if ($isImage && $viewUrl): ?>
        <a href="<?= esc($viewUrl, 'attr') ?>" target="_blank" rel="noopener" class="bo-detail-document-preview">
            <img src="<?= esc($viewUrl, 'attr') ?>" alt="<?= esc($filename ?: $label) ?>" loading="lazy">
        </a>
    <?php endif; ?>
    <div class="bo-detail-document-meta">
        <?php if ($filename): ?>
            <div class="bo-detail-document-name"><?= esc($filename) ?></div>
        <?php endif; ?>
        <div class="bo-detail-document-actions">
            <?php if ($viewUrl): ?>
                <a class="btn btn-sm btn-bo-secondary" href="<?= esc($viewUrl, 'attr') ?>" target="_blank" rel="noopener">
                    <i class="bi bi-eye" aria-hidden="true"></i>
                    <?= esc(lang('Backoffice.detail_view_file')) ?>
                </a>
            <?php endif; ?>
            <?php if ($downloadUrl): ?>
                <a class="btn btn-sm btn-bo-primary" href="<?= esc($downloadUrl, 'attr') ?>">
                    <i class="bi bi-download" aria-hidden="true"></i>
                    <?= esc(lang('Backoffice.detail_download_file')) ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
