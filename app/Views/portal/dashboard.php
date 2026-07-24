<?= $this->extend('layouts/portal') ?>

<?= $this->section('content') ?>

<?php
$total      = count($cases ?? []);
$open       = 0;
$inProgress = 0;
$resolved   = 0;
foreach ($cases ?? [] as $case) {
    if (in_array($case['status'], ['submitted', 'verified'], true)) {
        $open++;
    } elseif (in_array($case['status'], ['hearing', 'appeal'], true)) {
        $inProgress++;
    } elseif (in_array($case['status'], ['judgment', 'closed'], true)) {
        $resolved++;
    }
}
?>

<section class="jh-dash-welcome">
    <div>
        <h1><?= lang('Portal.dash_welcome', [esc(explode(' ', $user['name'])[0] ?? $user['name'])]) ?></h1>
        <p><?= esc(lang('Portal.dash_subtitle')) ?></p>
    </div>
    <a class="btn btn-jh-primary jh-dash-cta" href="<?= site_url('portal/complaints/new') ?>">
        <span aria-hidden="true">+</span> <?= esc(lang('Portal.dash_cta')) ?>
    </a>
</section>

<section class="jh-stat-grid" aria-label="<?= esc(lang('Portal.dash_stats')) ?>">
    <article class="jh-stat-card">
        <div>
            <p class="jh-stat-label"><?= esc(lang('Portal.stat_total')) ?></p>
            <p class="jh-stat-value"><?= esc((string) $total) ?></p>
            <p class="jh-stat-hint"><?= esc(lang('Portal.stat_total_hint')) ?></p>
        </div>
        <span class="jh-stat-icon jh-stat-icon--total" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M12 3 3 7.5 12 12l9-4.5L12 3Zm-7.5 8.2V16L12 21l7.5-5v-4.8L12 16.5 4.5 11.2Z" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
        </span>
    </article>
    <article class="jh-stat-card">
        <div>
            <p class="jh-stat-label"><?= esc(lang('Portal.stat_open')) ?></p>
            <p class="jh-stat-value"><?= esc((string) $open) ?></p>
            <p class="jh-stat-hint"><?= esc(lang('Portal.stat_open_hint')) ?></p>
        </div>
        <span class="jh-stat-icon jh-stat-icon--open" aria-hidden="true">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M12 8v5l3 2" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
        </span>
    </article>
    <article class="jh-stat-card">
        <div>
            <p class="jh-stat-label"><?= esc(lang('Portal.stat_progress')) ?></p>
            <p class="jh-stat-value"><?= esc((string) $inProgress) ?></p>
            <p class="jh-stat-hint"><?= esc(lang('Portal.stat_progress_hint')) ?></p>
            <p class="jh-stat-trend"><?= esc(lang('Portal.stat_trend_up')) ?></p>
        </div>
        <span class="jh-stat-icon jh-stat-icon--progress" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M4 19V5m0 14h16M7 15l3-4 3 2 4-6" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span>
    </article>
    <article class="jh-stat-card">
        <div>
            <p class="jh-stat-label"><?= esc(lang('Portal.stat_resolved')) ?></p>
            <p class="jh-stat-value"><?= esc((string) $resolved) ?></p>
            <p class="jh-stat-hint"><?= esc(lang('Portal.stat_resolved_hint')) ?></p>
            <p class="jh-stat-trend"><?= esc(lang('Portal.stat_trend_done')) ?></p>
        </div>
        <span class="jh-stat-icon jh-stat-icon--resolved" aria-hidden="true">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="m8.5 12.5 2.5 2.5 4.5-5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span>
    </article>
</section>

<section class="jh-dash-mid">
    <article class="jh-panel jh-panel--tall">
        <div class="jh-panel-head">
            <h2><?= esc(lang('Portal.dash_recent')) ?></h2>
            <a href="<?= site_url('portal/complaints') ?>"><?= esc(lang('Portal.list_open')) ?></a>
        </div>

        <?php if (empty($cases)): ?>
            <div class="jh-empty-state">
                <p><?= esc(lang('Portal.dash_empty')) ?></p>
                <a class="btn btn-jh-primary btn-sm" href="<?= site_url('portal/complaints/new') ?>"><?= esc(lang('Portal.dash_empty_cta')) ?></a>
            </div>
        <?php else: ?>
            <div class="jh-table-wrap">
                <table class="table table-hover jh-table jh-datatable w-100"
                       data-page-length="5"
                       data-order-col="3"
                       data-order-dir="desc"
                       data-length-change="false">
                    <thead>
                        <tr>
                            <th><?= esc(lang('Portal.list_ref')) ?></th>
                            <th><?= esc(lang('Portal.list_subject')) ?></th>
                            <th><?= esc(lang('Portal.list_status')) ?></th>
                            <th><?= esc(lang('Portal.list_updated')) ?></th>
                            <th data-orderable="false" data-searchable="false"><?= esc(lang('Portal.list_actions')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cases as $case): ?>
                            <tr>
                                <td><span class="jh-case-ref"><?= esc($case['id']) ?></span></td>
                                <td>
                                    <?= esc($case['subject']) ?>
                                    <small class="d-block text-muted"><?= esc($case['court_label']) ?></small>
                                </td>
                                <td>
                                    <span class="jh-status <?= $case['status'] === 'judgment' ? 'is-judgment' : '' ?>">
                                        <?= esc($case['status_label']) ?>
                                    </span>
                                </td>
                                <td><?= esc($case['updated']) ?></td>
                                <td>
                                    <a class="jh-table-link" href="<?= site_url('portal/complaints/' . $case['id']) ?>">
                                        <?= esc(lang('Portal.list_open')) ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </article>

    <div class="jh-dash-side">
        <article class="jh-panel">
            <h2><?= esc(lang('Portal.dash_quick')) ?></h2>
            <div class="jh-quick-actions">
                <a class="jh-quick-btn" href="<?= site_url('portal/complaints/new') ?>">+ <?= esc(lang('Portal.nav_new')) ?></a>
                <a class="jh-quick-btn" href="<?= site_url('portal/complaints') ?>"><?= esc(lang('Portal.nav_complaints')) ?></a>
                <a class="jh-quick-btn" href="<?= site_url('portal/appeals/provincial') ?>"><?= esc(lang('Portal.nav_provincial')) ?></a>
            </div>
        </article>

        <article class="jh-panel">
            <h2><?= esc(lang('Portal.dash_deadlines')) ?></h2>
            <?php if (! empty($appealCase) && ! empty($appealCase['appeal_days'])): ?>
                <div class="jh-deadline-soft">
                    <p><?= lang('Portal.dash_deadline_text', [(string) $appealCase['appeal_days'], $appealCase['id']]) ?></p>
                    <a class="btn btn-jh-primary btn-sm" href="<?= site_url('portal/appeals/provincial') ?>"><?= esc(lang('Portal.dash_deadline_cta')) ?></a>
                </div>
            <?php else: ?>
                <div class="jh-map-placeholder">
                    <p><?= esc(lang('Portal.dash_no_deadline')) ?></p>
                </div>
            <?php endif; ?>
        </article>
    </div>
</section>

<?= $this->endSection() ?>
