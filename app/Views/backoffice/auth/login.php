<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<section class="jh-auth jh-auth-bo" aria-label="<?= esc(lang('Backoffice.login_h1')) ?>">
    <div class="jh-auth-visual">
        <img src="<?= public_asset('assets/img/hero.jpg') ?>" alt="" width="1600" height="1200" aria-hidden="true">
        <div class="jh-auth-visual-shade"></div>
        <div class="jh-auth-visual-copy">
            <p class="jh-auth-brand">JusticeHeritage</p>
            <h2><?= esc(lang('Backoffice.login_visual_title')) ?></h2>
            <p><?= esc(lang('Backoffice.login_visual_text')) ?></p>
            <ul class="jh-auth-trust">
                <li><?= esc(lang('Backoffice.login_trust_2fa')) ?></li>
                <li><?= esc(lang('Backoffice.login_trust_staff')) ?></li>
            </ul>
        </div>
    </div>

    <div class="jh-auth-panel">
        <div class="jh-auth-panel-inner">
            <p class="jh-auth-welcome jh-auth-welcome-bo">
                <span class="jh-bo-shield" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M12 2 4 5v6c0 5 3.4 9.4 8 11 4.6-1.6 8-6 8-11V5l-8-3zm0 2.2 6 2.25V11c0 3.9-2.5 7.4-6 8.8-3.5-1.4-6-4.9-6-8.8V6.45l6-2.25z"/><path fill="currentColor" d="M11 11.6V8h2v3.6l2.2 2.2-1.4 1.4L12 13.4l-1.8 1.8-1.4-1.4z"/></svg>
                </span>
                <?= esc(lang('Backoffice.login_welcome')) ?>
            </p>
            <h1><?= esc(lang('Backoffice.login_panel_title')) ?></h1>
            <p class="jh-auth-lead"><?= esc(lang('Backoffice.login_panel_lead')) ?></p>

            <ol class="jh-auth-steps" aria-label="<?= esc(lang('Backoffice.login_steps_label')) ?>">
                <li class="is-active"><?= esc(lang('Backoffice.login_step_password')) ?></li>
                <li><?= esc(lang('Backoffice.login_step_2fa')) ?></li>
            </ol>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="jh-auth-alert" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('success')): ?>
                <div class="jh-auth-info" role="status"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>

            <form class="jh-auth-form" method="post" action="<?= site_url('backoffice/login') ?>" novalidate>
                <?= csrf_field() ?>

                <div class="jh-field">
                    <label class="form-label" for="username"><?= esc(lang('Backoffice.login_identifier')) ?></label>
                    <input class="form-control" type="text" id="username" name="username" required
                           autocomplete="username" value="<?= esc(old('username')) ?>"
                           placeholder="<?= esc(lang('Backoffice.login_identifier_hint'), 'attr') ?>">
                </div>

                <div class="jh-field">
                    <label class="form-label" for="password"><?= esc(lang('Backoffice.login_password')) ?></label>
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

                <button class="btn btn-jh-primary jh-auth-submit" type="submit"><?= esc(lang('Backoffice.login_submit')) ?></button>
            </form>

            <p class="jh-auth-secure"><?= esc(lang('Backoffice.login_secure_badge')) ?></p>
            <p class="jh-auth-footer">
                <a href="<?= site_url('login') ?>"><?= esc(lang('Backoffice.login_complainant_link')) ?></a>
            </p>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
