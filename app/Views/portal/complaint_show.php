<?= $this->extend('layouts/portal') ?>

<?= $this->section('content') ?>

<?php
$doneCount = 0;
foreach ($case['timeline'] as $step) {
    if (! empty($step['done'])) {
        $doneCount++;
    }
}
$progress = (int) round(($doneCount / max(count($case['timeline']), 1)) * 100);
$currentLabel = $case['status_label'];
foreach ($case['timeline'] as $step) {
    if (! empty($step['current'])) {
        $currentLabel = $step['label'];
        break;
    }
}
?>

<div class="jh-case-top">
    <a class="jh-case-back" href="<?= site_url('portal/complaints') ?>">← <?= esc(lang('Portal.case_back')) ?></a>

    <div class="jh-case-hero">
        <div class="jh-case-hero-main">
            <div class="jh-case-hero-tags">
                <span class="jh-case-ref"><?= esc($case['id']) ?></span>
                <span class="jh-status <?= $case['status'] === 'judgment' ? 'is-judgment' : '' ?>"><?= esc($case['status_label']) ?></span>
            </div>
            <h1><?= esc($case['subject']) ?></h1>
            <p><?= esc($case['summary']) ?></p>
            <div class="jh-case-hero-actions">
                <?php if (! empty($case['appeal_days']) && $case['court'] === 'provincial'): ?>
                    <a class="btn btn-jh-primary" href="<?= site_url('portal/appeals/regional') ?>"><?= esc(lang('Portal.case_appeal_reg')) ?></a>
                <?php elseif (! empty($case['appeal_days'])): ?>
                    <a class="btn btn-jh-primary" href="<?= site_url('portal/appeals/provincial') ?>"><?= esc(lang('Portal.case_appeal_prov')) ?></a>
                <?php endif; ?>
                <?php if (! empty($case['hearing'])): ?>
                    <span class="jh-case-chip"><?= esc(lang('Portal.case_hearing')) ?>: <?= esc($case['hearing']) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="jh-case-progress-card">
            <p class="jh-stat-label"><?= esc(lang('Portal.case_progress')) ?></p>
            <p class="jh-case-progress-value"><?= esc((string) $progress) ?>%</p>
            <div class="jh-case-progress-bar" aria-hidden="true">
                <span style="width: <?= esc((string) $progress) ?>%"></span>
            </div>
            <p class="jh-stat-hint"><?= esc(lang('Portal.case_current_step')) ?>: <?= esc($currentLabel) ?></p>
        </div>
    </div>
</div>

<section class="jh-case-facts" aria-label="<?= esc(lang('Portal.case_overview')) ?>">
    <article>
        <span><?= esc(lang('Portal.case_filed')) ?></span>
        <strong><?= esc($case['filed']) ?></strong>
    </article>
    <article>
        <span><?= esc(lang('Portal.case_updated')) ?></span>
        <strong><?= esc($case['updated']) ?></strong>
    </article>
    <article>
        <span><?= esc(lang('Portal.case_court')) ?></span>
        <strong><?= esc($case['court_label']) ?></strong>
    </article>
    <article>
        <span><?= esc(lang('Portal.case_magistrate')) ?></span>
        <strong><?= esc($case['magistrate']) ?></strong>
    </article>
</section>

<section class="jh-case-layout">
    <div class="jh-case-maincol">
        <article class="jh-panel">
            <div class="jh-panel-head">
                <h2><?= esc(lang('Portal.case_timeline')) ?></h2>
            </div>
            <ol class="jh-timeline jh-timeline--rich">
                <?php foreach ($case['timeline'] as $step): ?>
                    <li class="<?= ! empty($step['done']) ? 'is-done' : '' ?> <?= ! empty($step['current']) ? 'is-current' : '' ?>">
                        <div class="jh-timeline-card">
                            <div class="jh-timeline-head">
                                <strong><?= esc($step['label']) ?></strong>
                                <?php if (! empty($step['date'])): ?>
                                    <time datetime="<?= esc($step['date']) ?>"><?= esc($step['date']) ?></time>
                                <?php else: ?>
                                    <span class="jh-timeline-soon">—</span>
                                <?php endif; ?>
                            </div>
                            <?php if (! empty($step['note'])): ?>
                                <p><?= esc($step['note']) ?></p>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
        </article>

        <article class="jh-panel">
            <div class="jh-panel-head">
                <h2><?= esc(lang('Portal.case_details')) ?></h2>
            </div>
            <dl class="jh-case-dl">
                <div>
                    <dt><?= esc(lang('Portal.case_location')) ?></dt>
                    <dd><?= esc($case['location']) ?></dd>
                </div>
                <div>
                    <dt><?= esc(lang('Portal.case_respondents')) ?></dt>
                    <dd><?= esc($case['respondents']) ?></dd>
                </div>
                <div class="jh-case-dl-full">
                    <dt><?= esc(lang('Portal.case_summary')) ?></dt>
                    <dd><?= esc($case['summary']) ?></dd>
                </div>
            </dl>
        </article>

        <article class="jh-panel">
            <div class="jh-panel-head">
                <h2><?= esc(lang('Portal.case_docs')) ?></h2>
            </div>
            <div class="jh-table-wrap">
                <table class="table table-hover jh-table jh-datatable w-100"
                       data-page-length="5"
                       data-order-col="0"
                       data-order-dir="asc"
                       data-length-change="false">
                    <thead>
                        <tr>
                            <th><?= esc(lang('Portal.doc_name')) ?></th>
                            <th><?= esc(lang('Portal.doc_type')) ?></th>
                            <th><?= esc(lang('Portal.doc_size')) ?></th>
                            <th data-orderable="false" data-searchable="false"><?= esc(lang('Portal.list_actions')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($case['documents'] as $doc): ?>
                            <tr>
                                <td><?= esc($doc['name']) ?></td>
                                <td><span class="jh-doc-ico jh-doc-ico--inline"><?= esc($doc['type']) ?></span></td>
                                <td><?= esc($doc['size']) ?></td>
                                <td>
                                    <button class="jh-doc-link" type="button"><?= esc(lang('Portal.case_download')) ?></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </article>
    </div>

    <aside class="jh-case-sidecol">
        <?php if (! empty($case['appeal_days'])): ?>
            <?php
            $appealHref  = $case['court'] === 'provincial' ? site_url('portal/appeals/regional') : site_url('portal/appeals/provincial');
            $appealLabel = $case['court'] === 'provincial' ? lang('Portal.case_appeal_reg') : lang('Portal.case_appeal_prov');
            ?>
            <article class="jh-panel jh-case-deadline">
                <h2><?= esc(lang('Portal.case_deadline_title')) ?></h2>
                <p><?= esc(lang('Portal.case_deadline_body', [(string) $case['appeal_days']])) ?></p>
                <a class="btn btn-jh-gold btn-sm" href="<?= $appealHref ?>"><?= esc($appealLabel) ?></a>
            </article>
        <?php endif; ?>

        <article class="jh-panel">
            <h2><?= esc(lang('Portal.case_hearing')) ?></h2>
            <?php if (! empty($case['hearing'])): ?>
                <p class="jh-case-hearing-date"><?= esc($case['hearing']) ?></p>
                <?php if (! empty($case['hearing_place'])): ?>
                    <p class="jh-stat-hint mb-0"><?= esc(lang('Portal.case_place')) ?>: <?= esc($case['hearing_place']) ?></p>
                <?php endif; ?>
            <?php else: ?>
                <p class="jh-stat-hint mb-0"><?= esc(lang('Portal.case_none')) ?></p>
            <?php endif; ?>
        </article>

        <article class="jh-panel">
            <h2><?= esc(lang('Portal.case_actions')) ?></h2>
            <div class="jh-quick-actions">
                <?php if (! empty($case['appeal_days'])): ?>
                    <a class="jh-quick-btn" href="<?= $case['court'] === 'provincial' ? site_url('portal/appeals/regional') : site_url('portal/appeals/provincial') ?>">
                        <?= esc($case['court'] === 'provincial' ? lang('Portal.case_appeal_reg') : lang('Portal.case_appeal_prov')) ?>
                    </a>
                <?php else: ?>
                    <p class="jh-stat-hint mb-0"><?= esc(lang('Portal.case_no_action')) ?></p>
                <?php endif; ?>
            </div>
        </article>
    </aside>
</section>

<?= $this->endSection() ?>
