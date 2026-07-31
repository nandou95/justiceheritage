<?php
/**
 * List-page statistics cards (Total / Pending / In progress / Resolved).
 *
 * @var array{total?:int,pending?:int,in_progress?:int,resolved?:int} $listStats
 */
$listStats = array_merge([
    'total'       => 0,
    'pending'     => 0,
    'in_progress' => 0,
    'resolved'    => 0,
], $listStats ?? []);
?>

<section class="jh-list-stats" aria-label="<?= esc(lang('Portal.list_stats_label')) ?>">
    <article class="jh-list-stat jh-list-stat--total">
        <span class="jh-list-stat-ico" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M14 3v5h5M9 13h6M9 17h4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
        </span>
        <div>
            <p class="jh-list-stat-label"><?= esc(lang('Portal.list_stat_total')) ?></p>
            <p class="jh-list-stat-value"><?= esc((string) $listStats['total']) ?></p>
        </div>
    </article>

    <article class="jh-list-stat jh-list-stat--pending">
        <span class="jh-list-stat-ico" aria-hidden="true">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M12 8v5l3 2" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
        </span>
        <div>
            <p class="jh-list-stat-label"><?= esc(lang('Portal.list_stat_pending')) ?></p>
            <p class="jh-list-stat-value"><?= esc((string) $listStats['pending']) ?></p>
        </div>
    </article>

    <article class="jh-list-stat jh-list-stat--progress">
        <span class="jh-list-stat-ico" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M4 19V5m0 14h16M7 15l3-4 3 2 4-6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span>
        <div>
            <p class="jh-list-stat-label"><?= esc(lang('Portal.list_stat_in_progress')) ?></p>
            <p class="jh-list-stat-value"><?= esc((string) $listStats['in_progress']) ?></p>
        </div>
    </article>

    <article class="jh-list-stat jh-list-stat--resolved">
        <span class="jh-list-stat-ico" aria-hidden="true">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="m8.5 12.5 2.5 2.5 4.5-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span>
        <div>
            <p class="jh-list-stat-label"><?= esc(lang('Portal.list_stat_resolved')) ?></p>
            <p class="jh-list-stat-value"><?= esc((string) $listStats['resolved']) ?></p>
        </div>
    </article>
</section>
