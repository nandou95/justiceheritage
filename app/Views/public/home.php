<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

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

<section class="jh-section">
    <div class="jh-container">
        <div class="jh-section-head jh-reveal">
            <span class="jh-section-kicker"><?= esc(lang('Site.nav_dispute')) ?></span>
            <h2><?= esc(lang('Site.home_process_title')) ?></h2>
            <p><?= esc(lang('Site.home_process_lead')) ?></p>
        </div>

        <ol class="jh-process">
            <li class="jh-process-step">
                <span class="jh-process-num" aria-hidden="true"></span>
                <div>
                    <h3><?= esc(lang('Site.home_step1_title')) ?></h3>
                    <p><?= esc(lang('Site.home_step1_text')) ?></p>
                </div>
            </li>
            <li class="jh-process-step">
                <span class="jh-process-num" aria-hidden="true"></span>
                <div>
                    <h3><?= esc(lang('Site.home_step2_title')) ?></h3>
                    <p><?= esc(lang('Site.home_step2_text')) ?></p>
                </div>
            </li>
            <li class="jh-process-step">
                <span class="jh-process-num" aria-hidden="true"></span>
                <div>
                    <h3><?= esc(lang('Site.home_step3_title')) ?></h3>
                    <p><?= esc(lang('Site.home_step3_text')) ?></p>
                </div>
            </li>
            <li class="jh-process-step">
                <span class="jh-process-num" aria-hidden="true"></span>
                <div>
                    <h3><?= esc(lang('Site.home_step4_title')) ?></h3>
                    <p><?= esc(lang('Site.home_step4_text')) ?></p>
                </div>
            </li>
        </ol>
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
