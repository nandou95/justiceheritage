<?php
/**
 * Shared Details page header: breadcrumb, title, Back / Edit / Print actions.
 *
 * @var list<array{label:string,url?:string|null}> $breadcrumb
 * @var string $title
 * @var string|null $subtitle
 * @var string|null $backUrl
 * @var string|null $backLabel
 * @var string|null $editUrl
 * @var string|null $editLabel
 * @var bool $showPrint
 * @var string|null $extraActions  Raw HTML for additional buttons
 */
$breadcrumb  = $breadcrumb ?? [];
$title       = $title ?? '';
$subtitle    = $subtitle ?? null;
$backUrl     = $backUrl ?? null;
$backLabel   = $backLabel ?? lang('Backoffice.btn_back');
$editUrl     = $editUrl ?? null;
$editLabel   = $editLabel ?? lang('Backoffice.btn_edit');
$showPrint   = $showPrint ?? true;
$extraActions = $extraActions ?? null;
?>

<?= view('Modules\Administration\Views\partials\flash') ?>

<nav class="bo-detail-breadcrumb" aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= site_url('backoffice') ?>"><?= esc(lang('Backoffice.nav_dashboard')) ?></a>
        </li>
        <?php foreach ($breadcrumb as $crumb): ?>
            <?php if (! empty($crumb['url'])): ?>
                <li class="breadcrumb-item">
                    <a href="<?= esc($crumb['url'], 'attr') ?>"><?= esc($crumb['label']) ?></a>
                </li>
            <?php else: ?>
                <li class="breadcrumb-item active" aria-current="page"><?= esc($crumb['label']) ?></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>
</nav>

<section class="bo-crud-head bo-detail-head">
    <div>
        <h1><?= esc($title) ?></h1>
        <?php if ($subtitle !== null && $subtitle !== ''): ?>
            <p class="bo-detail-subtitle"><?= $subtitle ?></p>
        <?php endif; ?>
    </div>
    <div class="bo-crud-head-actions bo-detail-actions">
        <?php if ($backUrl): ?>
            <a class="btn btn-bo-secondary" href="<?= esc($backUrl, 'attr') ?>">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                <?= esc($backLabel) ?>
            </a>
        <?php endif; ?>
        <?php if ($editUrl): ?>
            <a class="btn btn-bo-primary" href="<?= esc($editUrl, 'attr') ?>">
                <i class="bi bi-pencil-square" aria-hidden="true"></i>
                <?= esc($editLabel) ?>
            </a>
        <?php endif; ?>
        <?= $extraActions ?>
        <?php if ($showPrint): ?>
            <button class="btn btn-outline-secondary" type="button" onclick="window.print()">
                <i class="bi bi-printer" aria-hidden="true"></i>
                <?= esc(lang('Backoffice.export_print')) ?>
            </button>
        <?php endif; ?>
    </div>
</section>
