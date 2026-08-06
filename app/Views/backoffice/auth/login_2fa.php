<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<section class="jh-auth jh-auth-bo" aria-label="<?= esc(lang('Backoffice.login_2fa_h1')) ?>">
    <div class="jh-auth-visual">
        <img src="<?= public_asset('assets/img/hero.jpg') ?>" alt="" width="1600" height="1200" aria-hidden="true">
        <div class="jh-auth-visual-shade"></div>
        <div class="jh-auth-visual-copy">
            <p class="jh-auth-brand">JusticeHeritage</p>
            <h2><?= esc(lang('Backoffice.login_visual_title')) ?></h2>
            <p><?= esc(lang('Backoffice.login_visual_text')) ?></p>
        </div>
    </div>

    <div class="jh-auth-panel">
        <div class="jh-auth-panel-inner">
            <p class="jh-auth-welcome jh-auth-welcome-bo"><?= esc(lang('Backoffice.login_welcome')) ?></p>
            <h1><?= esc(lang('Backoffice.login_2fa_h1')) ?></h1>
            <p class="jh-auth-lead">
                <?= esc(lang('Backoffice.login_2fa_lead', [$emailMasked ?? '***'])) ?>
            </p>

            <ol class="jh-auth-steps" aria-label="<?= esc(lang('Backoffice.login_steps_label')) ?>">
                <li class="is-done"><?= esc(lang('Backoffice.login_step_password')) ?></li>
                <li class="is-active"><?= esc(lang('Backoffice.login_step_2fa')) ?></li>
            </ol>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="jh-auth-alert" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('success')): ?>
                <div class="jh-auth-info" role="status"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>

            <form class="jh-auth-form" method="post" action="<?= site_url('backoffice/login/2fa') ?>" novalidate autocomplete="one-time-code">
                <?= csrf_field() ?>
                <div class="jh-field">
                    <label class="form-label" for="code"><?= esc(lang('Backoffice.login_2fa_code')) ?></label>
                    <input class="form-control" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                           id="code" name="code" required autofocus
                           autocomplete="one-time-code">
                </div>
                <button class="btn btn-jh-primary jh-auth-submit" type="submit"><?= esc(lang('Backoffice.login_2fa_submit')) ?></button>
            </form>

            <form method="post" action="<?= site_url('backoffice/login/2fa/resend') ?>" class="mt-3">
                <?= csrf_field() ?>
                <button class="btn btn-jh-secondary w-100" type="submit"><?= esc(lang('Backoffice.login_2fa_resend')) ?></button>
            </form>

            <p class="jh-auth-footer mt-3">
                <a href="<?= site_url('backoffice/login') ?>"><?= esc(lang('Backoffice.login_2fa_back')) ?></a>
            </p>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
