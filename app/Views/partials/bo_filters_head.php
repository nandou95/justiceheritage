<?php
/**
 * Shared list-filter header: title, lead, active count, clear link.
 *
 * @var array<string, mixed> $filters
 * @var list<string>         $filterKeys
 * @var string               $resetUrl
 * @var string|null          $lead
 * @var bool                 $showMeta  When false, hide active pill / reset (client-only filters).
 */
$filters     = $filters ?? [];
$filterKeys  = $filterKeys ?? [];
$resetUrl    = $resetUrl ?? current_url();
$lead        = $lead ?? lang('Backoffice.filters_lead');
$showMeta    = $showMeta ?? true;

$activeFilterCount = 0;
foreach ($filterKeys as $fk) {
    if (trim((string) ($filters[$fk] ?? '')) !== '') {
        $activeFilterCount++;
    }
}
?>
<div class="bo-filters-head">
    <div>
        <p class="bo-filters-kicker"><i class="bi bi-funnel" aria-hidden="true"></i> <?= esc(lang('Backoffice.filters_heading')) ?></p>
        <p class="bo-filters-lead"><?= esc($lead) ?></p>
    </div>
    <?php if ($showMeta): ?>
    <div class="bo-filters-head-meta">
        <?php if ($activeFilterCount > 0): ?>
            <span class="bo-filters-active-pill"><?= esc(lang('Backoffice.filters_active_count', [$activeFilterCount])) ?></span>
            <a class="btn btn-bo-secondary btn-sm" href="<?= esc($resetUrl, 'attr') ?>">
                <i class="bi bi-x-circle" aria-hidden="true"></i>
                <?= esc(lang('Backoffice.filter_reset')) ?>
            </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
