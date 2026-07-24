<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<section class="jh-auth" aria-label="<?= esc(lang('Site.login_h1')) ?>">
    <div class="jh-auth-visual">
        <img
            src="<?= public_asset('assets/img/hero.jpg') ?>"
            alt=""
            width="1600"
            height="1200"
            aria-hidden="true"
        >
        <div class="jh-auth-visual-shade"></div>
        <div class="jh-auth-visual-copy">
            <p class="jh-auth-brand">JusticeHeritage</p>
            <h2><?= esc(lang('Site.login_visual_title')) ?></h2>
            <p><?= esc(lang('Site.login_visual_text')) ?></p>
            <ul class="jh-auth-trust">
                <li><?= esc(lang('Site.trust_secure')) ?></li>
                <li><?= esc(lang('Site.footer_communal')) ?></li>
                <li><?= esc(lang('Site.footer_provincial')) ?></li>
            </ul>
        </div>
    </div>

    <div class="jh-auth-panel">
        <div class="jh-auth-panel-inner">
            <p class="jh-auth-welcome"><?= esc(lang('Site.login_welcome')) ?></p>
            <h1><?= esc(lang('Site.login_panel_title')) ?></h1>
            <p class="jh-auth-lead"><?= esc(lang('Site.login_panel_lead')) ?></p>

            <ol class="jh-auth-steps" aria-label="Authentication steps">
                <li class="is-active"><?= esc(lang('Site.login_step_password')) ?></li>
                <li><?= esc(lang('Site.login_step_2fa')) ?></li>
            </ol>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="jh-auth-alert" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>

            <form class="jh-auth-form" method="post" action="<?= site_url('login') ?>" novalidate>
                <?= csrf_field() ?>

                <div class="jh-field">
                    <label class="form-label" for="login"><?= esc(lang('Site.login_identifier')) ?></label>
                    <input class="form-control" type="text" id="login" name="login" required autocomplete="username" placeholder=" ">
                </div>

                <div class="jh-field">
                    <label class="form-label" for="password"><?= esc(lang('Site.label_password')) ?></label>
                    <div class="jh-password">
                        <input class="form-control" type="password" id="password" name="password" required autocomplete="current-password">
                        <button
                            class="jh-password-toggle"
                            type="button"
                            data-password-toggle
                            data-target="password"
                            data-show-label="<?= esc(lang('Site.login_show_password'), 'attr') ?>"
                            data-hide-label="<?= esc(lang('Site.login_hide_password'), 'attr') ?>"
                            aria-label="<?= esc(lang('Site.login_show_password')) ?>"
                        >
                            <?= esc(lang('Site.login_show_password')) ?>
                        </button>
                    </div>
                </div>

                <button class="btn btn-jh-primary jh-auth-submit" type="submit"><?= esc(lang('Site.login_submit')) ?></button>
            </form>

            <p class="jh-auth-secure"><?= esc(lang('Site.login_secure_badge')) ?></p>
            <p class="jh-auth-note"><?= esc(lang('Site.login_note')) ?></p>

            <p class="jh-auth-footer">
                <?= esc(lang('Site.login_no_account')) ?>
                <a href="<?= site_url('register') ?>"><?= esc(lang('Site.home_cta_register')) ?></a>
            </p>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
