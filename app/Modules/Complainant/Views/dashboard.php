<?= $this->extend('layouts/portal') ?>

<?= $this->section('content') ?>

<?php
$stats                = $stats ?? ['total' => 0, 'communal' => 0, 'provincial' => 0, 'regional' => 0, 'ministry' => 0, 'resolved' => 0, 'pending' => 0];
$casesByLevel         = $casesByLevel ?? ['communal' => [], 'provincial' => [], 'regional' => []];
$communalComplaints   = $communalComplaints ?? [];
$provincialComplaints = $provincialComplaints ?? [];
$regionalComplaints   = $regionalComplaints ?? [];
$ministryComplaints   = $ministryComplaints ?? [];
$activity             = $activity ?? [];
$cases                = $cases ?? [];

$statusClass = static function (string $status): string {
    return match ($status) {
        'submitted', 'verified' => 'is-pending',
        'hearing', 'appeal'     => 'is-review',
        'judgment', 'closed'    => 'is-resolved',
        default                 => '',
    };
};

$levelBadge = static function (string $court): string {
    return match ($court) {
        'provincial' => 'jh-level-badge is-provincial',
        'regional'   => 'jh-level-badge is-regional',
        default      => 'jh-level-badge is-communal',
    };
};

$activityIconSvg = static function (string $icon): string {
    $paths = [
        'submitted' => '<path d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M14 3v5h5" fill="none" stroke="currentColor" stroke-width="1.7"/>',
        'verified'  => '<circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="m8.5 12.5 2.5 2.5 4.5-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>',
        'hearing'   => '<path d="M4 19V5m0 14h16M7 15l3-4 3 2 4-6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>',
        'judgment'  => '<path d="M12 3v18M8 7h8M7 21h10M9 11h6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
        'appeal'    => '<path d="m7 14 5-5 5 5M5 20h14" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>',
        'regional'  => '<path d="M12 3 4 9v11h6v-6h4v6h6V9l-8-6Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>',
        'assigned'  => '<circle cx="12" cy="8" r="3.2" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M5 19a7 7 0 0 1 14 0" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
    ];

    $inner = $paths[$icon] ?? $paths['submitted'];

    return '<svg viewBox="0 0 24 24" aria-hidden="true">' . $inner . '</svg>';
};

$firstName = esc(explode(' ', $user['name'])[0] ?? $user['name']);

$renderCaseTable = static function (array $rows, string $emptyMessage, callable $statusClass, callable $levelBadge): void {
    if ($rows === []) {
        echo '<div class="jh-empty-state jh-empty-state--compact"><p>' . esc($emptyMessage) . '</p></div>';

        return;
    }
    ?>
    <div class="jh-table-wrap">
        <table class="table table-hover jh-table jh-datatable w-100"
               data-page-length="5"
               data-order-col="3"
               data-order-dir="desc"
               data-length-change="false">
            <thead>
                <tr>
                    <th><?= esc(lang('Portal.col_case_number')) ?></th>
                    <th><?= esc(lang('Portal.col_case_title')) ?></th>
                    <th><?= esc(lang('Portal.col_court_jurisdiction')) ?></th>
                    <th><?= esc(lang('Portal.col_submission_date')) ?></th>
                    <th><?= esc(lang('Portal.col_current_status')) ?></th>
                    <th><?= esc(lang('Portal.col_magistrate')) ?></th>
                    <th><?= esc(lang('Portal.col_next_hearing')) ?></th>
                    <th data-orderable="false" data-searchable="false"><?= esc(lang('Portal.list_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $case): ?>
                    <tr>
                        <td><span class="jh-case-ref"><?= esc($case['id']) ?></span></td>
                        <td><?= esc($case['subject']) ?></td>
                        <td><?= esc($case['court_name'] ?? $case['court_label']) ?></td>
                        <td><?= esc($case['filed']) ?></td>
                        <td>
                            <span class="jh-status <?= esc($statusClass((string) $case['status'])) ?>">
                                <?= esc($case['status_label']) ?>
                            </span>
                        </td>
                        <td><?= esc($case['magistrate'] ?: '—') ?></td>
                        <td><?= esc(! empty($case['hearing_date']) ? $case['hearing_date'] : (! empty($case['hearing']) ? $case['hearing'] : '—')) ?></td>
                        <td>
                            <div class="jh-action-group">
                                <a class="jh-action-btn"
                                   href="<?= site_url('portal/complaints/' . $case['id']) ?>"
                                   data-bs-toggle="tooltip"
                                   data-bs-placement="top"
                                   title="<?= esc(lang('Portal.dash_view_details')) ?>"
                                   aria-label="<?= esc(lang('Portal.dash_view_details')) ?>">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6.5 9.5-6.5S21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z" fill="none" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="2.6" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
};
?>

<section class="jh-dash-hero">
    <div class="jh-dash-hero-copy">
        <p class="jh-dash-kicker"><?= esc(lang('Portal.dash_kicker')) ?></p>
        <h1><?= lang('Portal.dash_welcome', [$firstName]) ?></h1>
        <p><?= esc(lang('Portal.dash_subtitle_full')) ?></p>
    </div>
    <div class="jh-dash-hero-meta" aria-hidden="true">
        <span class="jh-dash-hero-date"><?= esc(date('D, j M Y')) ?></span>
    </div>
</section>

<section class="jh-dash-stats jh-dash-stats--seven" aria-label="<?= esc(lang('Portal.dash_stats')) ?>">
    <article class="jh-dash-stat jh-dash-stat--total">
        <span class="jh-dash-stat-ico" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M14 3v5h5M9 13h6M9 17h4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
        </span>
        <div>
            <p class="jh-dash-stat-label"><?= esc(lang('Portal.stat_total')) ?></p>
            <p class="jh-dash-stat-value"><?= esc((string) $stats['total']) ?></p>
        </div>
    </article>
    <article class="jh-dash-stat jh-dash-stat--communal">
        <span class="jh-dash-stat-ico" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M4 20h16M6 20V10l6-4 6 4v10M10 20v-5h4v5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
        </span>
        <div>
            <p class="jh-dash-stat-label"><?= esc(lang('Portal.stat_communal')) ?></p>
            <p class="jh-dash-stat-value"><?= esc((string) $stats['communal']) ?></p>
        </div>
    </article>
    <article class="jh-dash-stat jh-dash-stat--provincial">
        <span class="jh-dash-stat-ico" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="m7 14 5-5 5 5M5 20h14" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span>
        <div>
            <p class="jh-dash-stat-label"><?= esc(lang('Portal.stat_provincial')) ?></p>
            <p class="jh-dash-stat-value"><?= esc((string) $stats['provincial']) ?></p>
        </div>
    </article>
    <article class="jh-dash-stat jh-dash-stat--regional">
        <span class="jh-dash-stat-ico" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M12 3 4 9v11h6v-6h4v6h6V9l-8-6Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
        </span>
        <div>
            <p class="jh-dash-stat-label"><?= esc(lang('Portal.stat_regional')) ?></p>
            <p class="jh-dash-stat-value"><?= esc((string) $stats['regional']) ?></p>
        </div>
    </article>
    <article class="jh-dash-stat jh-dash-stat--ministry">
        <span class="jh-dash-stat-ico" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M4 20h16M6 20V10l6-5 6 5v10M9 20v-5h6v5M10 10h.01M14 10h.01M12 13h.01" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span>
        <div>
            <p class="jh-dash-stat-label"><?= esc(lang('Portal.stat_ministry')) ?></p>
            <p class="jh-dash-stat-value"><?= esc((string) ($stats['ministry'] ?? 0)) ?></p>
        </div>
    </article>
    <article class="jh-dash-stat jh-dash-stat--resolved">
        <span class="jh-dash-stat-ico" aria-hidden="true">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="m8.5 12.5 2.5 2.5 4.5-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span>
        <div>
            <p class="jh-dash-stat-label"><?= esc(lang('Portal.stat_resolved_cases')) ?></p>
            <p class="jh-dash-stat-value"><?= esc((string) $stats['resolved']) ?></p>
        </div>
    </article>
    <article class="jh-dash-stat jh-dash-stat--pending">
        <span class="jh-dash-stat-ico" aria-hidden="true">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M12 8v5l3 2" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
        </span>
        <div>
            <p class="jh-dash-stat-label"><?= esc(lang('Portal.stat_pending_cases')) ?></p>
            <p class="jh-dash-stat-value"><?= esc((string) $stats['pending']) ?></p>
        </div>
    </article>
</section>

<div class="jh-dash-cases">
    <section class="jh-dash-panel" id="communal-cases">
        <div class="jh-dash-panel-head">
            <div>
                <span class="jh-level-badge is-communal"><?= esc(lang('Portal.level_communal')) ?></span>
                <h2><?= esc(lang('Portal.dash_sec_communal')) ?></h2>
                <p><?= esc(lang('Portal.dash_sec_communal_lead')) ?></p>
            </div>
        </div>
        <?php
        echo view('Modules\Complainant\Views\partials\complaint_list_table', [
            'complaints'   => $communalComplaints,
            'emptyMessage' => lang('Portal.list_empty_message'),
            'pageLength'   => 5,
        ]);
        ?>
    </section>

    <section class="jh-dash-panel" id="provincial-cases">
        <div class="jh-dash-panel-head">
            <div>
                <span class="jh-level-badge is-provincial"><?= esc(lang('Portal.level_provincial')) ?></span>
                <h2><?= esc(lang('Portal.dash_sec_provincial')) ?></h2>
                <p><?= esc(lang('Portal.dash_sec_provincial_lead')) ?></p>
            </div>
        </div>
        <?php
        echo view('Modules\Complainant\Views\partials\provincial_complaint_list_table', [
            'complaints'   => $provincialComplaints,
            'emptyMessage' => lang('Portal.list_empty_message'),
            'pageLength'   => 5,
        ]);
        ?>
    </section>

    <section class="jh-dash-panel" id="regional-cases">
        <div class="jh-dash-panel-head">
            <div>
                <span class="jh-level-badge is-regional"><?= esc(lang('Portal.level_regional')) ?></span>
                <h2><?= esc(lang('Portal.dash_sec_regional')) ?></h2>
                <p><?= esc(lang('Portal.dash_sec_regional_lead')) ?></p>
            </div>
        </div>
        <?php
        echo view('Modules\Complainant\Views\partials\provincial_complaint_list_table', [
            'complaints'   => $regionalComplaints,
            'emptyMessage' => lang('Portal.list_empty_message'),
            'pageLength'   => 5,
        ]);
        ?>
    </section>

    <section class="jh-dash-panel" id="ministry-cases">
        <div class="jh-dash-panel-head">
            <div>
                <span class="jh-level-badge is-ministry"><?= esc(lang('Portal.level_ministry')) ?></span>
                <h2><?= esc(lang('Portal.dash_sec_ministry')) ?></h2>
                <p><?= esc(lang('Portal.dash_sec_ministry_lead')) ?></p>
            </div>
        </div>
        <?php
        echo view('Modules\Complainant\Views\partials\provincial_complaint_list_table', [
            'complaints'   => $ministryComplaints,
            'emptyMessage' => lang('Portal.list_empty_message'),
            'pageLength'   => 5,
        ]);
        ?>
    </section>
</div>

<section class="jh-dash-panel jh-dash-activity" aria-label="<?= esc(lang('Portal.dash_activity')) ?>">
    <div class="jh-dash-panel-head">
        <div>
            <h2><?= esc(lang('Portal.dash_activity')) ?></h2>
            <p><?= esc(lang('Portal.dash_activity_lead')) ?></p>
        </div>
    </div>

    <?php if ($activity === []): ?>
        <div class="jh-empty-state jh-empty-state--compact">
            <p><?= esc(lang('Portal.dash_activity_empty')) ?></p>
        </div>
    <?php else: ?>
        <ol class="jh-activity-timeline">
            <?php foreach ($activity as $item): ?>
                <?php
                $iconKey = (string) ($item['icon'] ?? 'submitted');
                $badgeClass = (string) ($item['status_class'] ?? 'is-pending');
                $badgeLabel = (string) ($item['status_label'] ?? '');
                ?>
                <li class="jh-activity-item">
                    <span class="jh-activity-icon is-<?= esc($iconKey) ?>" aria-hidden="true">
                        <?= $activityIconSvg($iconKey) ?>
                    </span>
                    <div class="jh-activity-body">
                        <div class="jh-activity-top">
                            <h3><?= esc($item['title'] ?? $item['text'] ?? '') ?></h3>
                            <?php if ($badgeLabel !== ''): ?>
                                <span class="jh-status <?= esc($badgeClass) ?>"><?= esc($badgeLabel) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (! empty($item['description'] ?? $item['note'] ?? '')): ?>
                            <p><?= esc($item['description'] ?? $item['note']) ?></p>
                        <?php endif; ?>
                        <div class="jh-activity-meta">
                            <time datetime="<?= esc($item['datetime'] ?? $item['date'] ?? '') ?>">
                                <?= esc(($item['date'] ?? '') . (! empty($item['time']) ? ' · ' . $item['time'] : '')) ?>
                            </time>
                            <?php if (! empty($item['ref'])): ?>
                                <a href="<?= site_url('portal/complaints/' . $item['ref']) ?>"><?= esc($item['ref']) ?></a>
                            <?php endif; ?>
                            <span class="<?= esc($levelBadge((string) ($item['court'] ?? 'communal'))) ?>">
                                <?= esc(lang('Portal.level_' . (($item['court'] ?? 'communal') === 'provincial' ? 'provincial' : (($item['court'] ?? '') === 'regional' ? 'regional' : 'communal')))) ?>
                            </span>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
</section>

<?php if ($cases !== []): ?>
<section class="jh-dash-progress" aria-label="<?= esc(lang('Portal.dash_progress_title')) ?>">
    <div class="jh-dash-panel-head">
        <div>
            <h2><?= esc(lang('Portal.dash_progress_title')) ?></h2>
            <p><?= esc(lang('Portal.dash_progress_lead')) ?></p>
        </div>
    </div>

    <div class="jh-dash-progress-grid">
        <?php foreach ($cases as $case): ?>
            <?php
            $doneCount = 0;
            foreach ($case['timeline'] as $step) {
                if (! empty($step['done'])) {
                    $doneCount++;
                }
            }
            $pct = (int) round(($doneCount / max(count($case['timeline']), 1)) * 100);
            $currentLabel = $case['status_label'];
            foreach ($case['timeline'] as $step) {
                if (! empty($step['current'])) {
                    $currentLabel = $step['label'];
                    break;
                }
            }
            ?>
            <article class="jh-dash-progress-card">
                <header class="jh-dash-progress-head">
                    <div>
                        <span class="<?= esc($levelBadge((string) $case['court'])) ?>">
                            <?= esc($case['level_label'] ?? $case['court_label']) ?>
                        </span>
                        <span class="jh-case-ref"><?= esc($case['id']) ?></span>
                        <h3><?= esc($case['subject']) ?></h3>
                    </div>
                    <span class="jh-status <?= esc($statusClass((string) $case['status'])) ?>"><?= esc($case['status_label']) ?></span>
                </header>

                <div class="jh-dash-progress-meta">
                    <span><?= esc($case['court_name'] ?? $case['court_label']) ?></span>
                    <span><?= esc(lang('Portal.dash_current_stage')) ?>: <strong><?= esc($currentLabel) ?></strong></span>
                    <span><?= esc((string) $pct) ?>%</span>
                </div>

                <div class="jh-dash-progress-bar" aria-hidden="true">
                    <span style="width: <?= esc((string) $pct) ?>%"></span>
                </div>

                <ol class="jh-dash-timeline">
                    <?php foreach ($case['timeline'] as $step): ?>
                        <li class="<?= ! empty($step['done']) ? 'is-done' : '' ?> <?= ! empty($step['current']) ? 'is-current' : '' ?>">
                            <span class="jh-dash-timeline-dot" aria-hidden="true"></span>
                            <strong><?= esc($step['label']) ?></strong>
                            <?php if (! empty($step['date'])): ?>
                                <time datetime="<?= esc($step['date']) ?>"><?= esc($step['date']) ?></time>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>

                <a class="jh-dash-progress-link" href="<?= site_url('portal/complaints/' . $case['id']) ?>">
                    <?= esc(lang('Portal.dash_view_details')) ?> →
                </a>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?= $this->endSection() ?>
