<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<header class="jh-page-banner">
    <div class="jh-container">
        <h1><?= esc(lang('Site.dispute_h1')) ?></h1>
        <p><?= esc(lang('Site.dispute_lead')) ?></p>
    </div>
</header>

<section class="jh-section">
    <div class="jh-container">
        <article class="jh-prose">
            <p><?= esc(lang('Site.dispute_intro')) ?></p>

            <h2><?= esc(lang('Site.dispute_who_title')) ?></h2>
            <ul>
                <li><?= esc(lang('Site.dispute_who_1')) ?></li>
                <li><?= esc(lang('Site.dispute_who_2')) ?></li>
                <li><?= esc(lang('Site.dispute_who_3')) ?></li>
                <li><?= esc(lang('Site.dispute_who_4')) ?></li>
            </ul>

            <h2><?= esc(lang('Site.dispute_path_title')) ?></h2>
            <div class="jh-levels">
                <div class="jh-level">
                    <h3><?= esc(lang('Site.dispute_l1_title')) ?></h3>
                    <p><?= esc(lang('Site.dispute_l1_text')) ?></p>
                </div>
                <div class="jh-level">
                    <h3><?= esc(lang('Site.dispute_l2_title')) ?></h3>
                    <p><?= esc(lang('Site.dispute_l2_text')) ?></p>
                </div>
                <div class="jh-level">
                    <h3><?= esc(lang('Site.dispute_l3_title')) ?></h3>
                    <p><?= esc(lang('Site.dispute_l3_text')) ?></p>
                </div>
            </div>

            <h2><?= esc(lang('Site.dispute_visitor_h')) ?></h2>
            <p><?= esc(lang('Site.dispute_visitor_p')) ?></p>
            <p class="mt-4">
                <a class="btn btn-jh-primary" href="<?= site_url('register') ?>"><?= esc(lang('Site.home_cta_register')) ?></a>
            </p>
        </article>
    </div>
</section>

<?= $this->endSection() ?>
