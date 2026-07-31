<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<?php
$flowIcon = static function (string $name): string {
    $icons = [
        'file' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M14 3v5h5M9 13h6M9 17h4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
        'verify' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.25" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="m8.5 12.2 2.4 2.4 4.6-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'judge' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v3M8 21h8M7 9h10l-1.2 3.2a4.8 4.8 0 0 1-7.6 0L7 9Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9.5 12.5 8 21m6.5-8.5L16 21" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
        'calendar' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4v3M17 4v3M4 9h16M6 6h12a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
        'hearing' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5m0 14h16M7 15l3-4 3 2 4-6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'judgment' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v18M8 21h8M6 8h12M7.5 8 5 14h5L7.5 8Zm9 0L14 14h5l-2.5-6Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>',
        'appeal' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8 14 4-8 4 8M6 18h12" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
        'inbox' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 13h5l1.5 2h3L15 13h5v5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-5Zm0 0 2.5-7h11L20 13" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>',
        'assign' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1M9.5 10a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm7.5 1a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Zm1.5 8v-1a3.5 3.5 0 0 0-2.2-3.2" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
        'ministry' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h16M6 20V8l6-4 6 4v12M9 12h.01M12 12h.01M15 12h.01M9 16h.01M12 16h.01M15 16h.01" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
        'final' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v5c0 4.5 3 7.8 7 9 4-1.2 7-4.5 7-9V6l-7-3Z" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="m9 12 2.2 2.2L15.5 10" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'arrow' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h12m0 0-4-4m4 4-4 4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    ];

    return $icons[$name] ?? $icons['file'];
};

$communalSteps = [
    ['icon' => 'file', 'title' => lang('Site.home_path_c1_title'), 'text' => lang('Site.home_path_c1_text')],
    ['icon' => 'verify', 'title' => lang('Site.home_path_c2_title'), 'text' => lang('Site.home_path_c2_text')],
    ['icon' => 'judge', 'title' => lang('Site.home_path_c3_title'), 'text' => lang('Site.home_path_c3_text')],
    ['icon' => 'calendar', 'title' => lang('Site.home_path_c4_title'), 'text' => lang('Site.home_path_c4_text')],
    ['icon' => 'hearing', 'title' => lang('Site.home_path_c5_title'), 'text' => lang('Site.home_path_c5_text')],
    ['icon' => 'judgment', 'title' => lang('Site.home_path_c6_title'), 'text' => lang('Site.home_path_c6_text')],
];

$provincialSteps = [
    ['icon' => 'appeal', 'title' => lang('Site.home_path_p1_title'), 'text' => lang('Site.home_path_p1_text')],
    ['icon' => 'inbox', 'title' => lang('Site.home_path_p2_title'), 'text' => lang('Site.home_path_p2_text')],
    ['icon' => 'assign', 'title' => lang('Site.home_path_p3_title'), 'text' => lang('Site.home_path_p3_text')],
    ['icon' => 'hearing', 'title' => lang('Site.home_path_p4_title'), 'text' => lang('Site.home_path_p4_text')],
    ['icon' => 'judgment', 'title' => lang('Site.home_path_p5_title'), 'text' => lang('Site.home_path_p5_text')],
];

$regionalSteps = [
    ['icon' => 'appeal', 'title' => lang('Site.home_path_r1_title'), 'text' => lang('Site.home_path_r1_text')],
    ['icon' => 'inbox', 'title' => lang('Site.home_path_r2_title'), 'text' => lang('Site.home_path_r2_text')],
    ['icon' => 'ministry', 'title' => lang('Site.home_path_r3_title'), 'text' => lang('Site.home_path_r3_text')],
    ['icon' => 'hearing', 'title' => lang('Site.home_path_r4_title'), 'text' => lang('Site.home_path_r4_text')],
    ['icon' => 'final', 'title' => lang('Site.home_path_r5_title'), 'text' => lang('Site.home_path_r5_text')],
];

$renderFlow = static function (
    string $theme,
    string $level,
    string $title,
    string $lead,
    array $steps
) use ($flowIcon): void {
    ?>
    <article class="jh-flow jh-flow--<?= esc($theme) ?> jh-reveal" data-flow>
        <header class="jh-flow-head">
            <span class="jh-flow-badge"><?= esc($level) ?></span>
            <h3><?= esc($title) ?></h3>
            <p><?= esc($lead) ?></p>
        </header>

        <div class="jh-flow-track" role="list">
            <?php foreach ($steps as $index => $step): ?>
                <?php if ($index > 0): ?>
                    <div class="jh-flow-connector" aria-hidden="true">
                        <span class="jh-flow-connector-line"></span>
                        <span class="jh-flow-connector-arrow"><?= $flowIcon('arrow') ?></span>
                    </div>
                <?php endif; ?>

                <div class="card jh-flow-card" role="listitem" data-flow-card style="--step-i: <?= (int) $index ?>">
                    <div class="card-body">
                        <div class="jh-flow-card-top">
                            <span class="jh-flow-ico"><?= $flowIcon($step['icon']) ?></span>
                            <span class="jh-flow-num"><?= esc(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                        </div>
                        <h4 class="jh-flow-card-title"><?= esc($step['title']) ?></h4>
                        <p class="jh-flow-card-text"><?= esc($step['text']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
    <?php
};
?>

<section class="jh-hero" aria-label="<?= esc(lang('Site.home_hero_aria')) ?>">
    <div class="jh-hero-media">
        <img
            src="<?= public_asset('assets/img/hero.jpg') ?>"
            alt="<?= esc(lang('Site.home_hero_alt')) ?>"
            width="2000"
            height="1200"
            fetchpriority="high"
        >
    </div>
    <div class="jh-hero-shade" aria-hidden="true"></div>
    <div class="jh-hero-content">
        <p class="jh-hero-brand">JusticeHeritage</p>
        <h1><?= esc(lang('Site.home_hero_title')) ?></h1>
        <p class="jh-hero-lead"><?= esc(lang('Site.home_hero_lead')) ?></p>
        <div class="jh-hero-actions">
            <a class="btn btn-jh-primary" href="<?= site_url('register') ?>"><?= esc(lang('Site.home_cta_register')) ?></a>
            <a class="btn btn-jh-secondary" href="<?= site_url('dispute-management') ?>"><?= esc(lang('Site.home_cta_learn')) ?></a>
        </div>
    </div>
</section>

<section class="jh-section jh-section--flows" id="court-processes" aria-labelledby="home-pathways-title">
    <div class="jh-container">
        <div class="jh-section-head jh-reveal">
            <span class="jh-section-kicker"><?= esc(lang('Site.home_pathways_kicker')) ?></span>
            <h2 id="home-pathways-title"><?= esc(lang('Site.home_pathways_title')) ?></h2>
            <p><?= esc(lang('Site.home_pathways_lead')) ?></p>
        </div>

        <div class="jh-flow-legend jh-reveal" aria-hidden="true">
            <span class="is-communal"><?= esc(lang('Site.home_path_communal_level')) ?></span>
            <span class="is-provincial"><?= esc(lang('Site.home_path_provincial_level')) ?></span>
            <span class="is-regional"><?= esc(lang('Site.home_path_regional_level')) ?></span>
        </div>

        <div class="jh-flows">
            <?php
            $renderFlow(
                'communal',
                lang('Site.home_path_communal_level'),
                lang('Site.home_path_communal_title'),
                lang('Site.home_path_communal_lead'),
                $communalSteps
            );
            $renderFlow(
                'provincial',
                lang('Site.home_path_provincial_level'),
                lang('Site.home_path_provincial_title'),
                lang('Site.home_path_provincial_lead'),
                $provincialSteps
            );
            $renderFlow(
                'regional',
                lang('Site.home_path_regional_level'),
                lang('Site.home_path_regional_title'),
                lang('Site.home_path_regional_lead'),
                $regionalSteps
            );
            ?>
        </div>
    </div>
</section>

<section class="jh-section jh-section-alt">
    <div class="jh-container">
        <div class="jh-split">
            <div class="jh-reveal">
                <div class="jh-section-head">
                    <span class="jh-section-kicker">JusticeHeritage</span>
                    <h2><?= esc(lang('Site.home_built_title')) ?></h2>
                    <p><?= esc(lang('Site.home_built_lead')) ?></p>
                </div>
                <ul class="jh-list-check">
                    <li><?= esc(lang('Site.home_built_1')) ?></li>
                    <li><?= esc(lang('Site.home_built_2')) ?></li>
                    <li><?= esc(lang('Site.home_built_3')) ?></li>
                    <li><?= esc(lang('Site.home_built_4')) ?></li>
                </ul>
            </div>
            <div class="jh-split-visual jh-reveal" role="img" aria-label="<?= esc(lang('Site.home_built_img')) ?>"></div>
        </div>
    </div>
</section>

<section class="jh-cta-band">
    <div class="jh-container jh-reveal">
        <h2><?= esc(lang('Site.home_cta_title')) ?></h2>
        <p><?= esc(lang('Site.home_cta_text')) ?></p>
        <a class="btn btn-jh-gold" href="<?= site_url('register') ?>"><?= esc(lang('Site.home_cta_button')) ?></a>
    </div>
</section>

<?= $this->endSection() ?>
