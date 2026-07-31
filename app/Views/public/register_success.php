<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<section class="jh-auth" aria-label="<?= esc(lang('Site.success_h1')) ?>">
    <div class="jh-auth-visual">
        <img src="<?= public_asset('assets/img/hero.jpg') ?>" alt="" width="1600" height="1200" aria-hidden="true">
        <div class="jh-auth-visual-shade"></div>
        <div class="jh-auth-visual-copy">
            <p class="jh-auth-brand">JusticeHeritage</p>
            <h2><?= esc(lang('Site.success_h1')) ?></h2>
            <p><?= esc(lang('Site.success_lead')) ?></p>
        </div>
    </div>

    <div class="jh-auth-panel">
        <div class="jh-auth-panel-inner">
            <p class="jh-auth-welcome"><?= esc(lang('Site.register_welcome')) ?></p>
            <h1><?= esc(lang('Site.success_h1')) ?></h1>
            <p class="jh-auth-lead"><strong><?= esc($message ?? lang('Site.success_completed')) ?></strong></p>
            <p><?= lang('Site.success_thanks', ['<strong>' . esc($name ?? lang('Site.complainant')) . '</strong>']) ?></p>

            <?php if (array_key_exists('mailOk', get_defined_vars())): ?>
                <?php if (! empty($mailOk)): ?>
                    <div class="jh-auth-info" role="status">
                        <?= esc(lang('Site.success_mail_ok')) ?>
                    </div>
                <?php else: ?>
                    <div class="jh-auth-alert" role="alert">
                        <p class="mb-1"><?= esc(lang('Site.success_mail_fail')) ?></p>
                        <?php if (! empty($mailError)): ?>
                            <p class="mb-0"><small><?= esc($mailError) ?></small></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <ol class="jh-auth-steps jh-auth-steps--three">
                <li class="is-active"><?= esc(lang('Site.success_step1')) ?></li>
                <li><?= esc(lang('Site.success_step2')) ?></li>
                <li><?= esc(lang('Site.success_step3')) ?></li>
            </ol>

            <div class="jh-auth-actions" style="display:flex;flex-wrap:wrap;gap:.75rem;">
                <a class="btn btn-jh-primary" href="<?= site_url('login') ?>"><?= esc(lang('Site.success_signin')) ?></a>
                <a class="btn btn-jh-secondary" href="<?= site_url('/') ?>"><?= esc(lang('Site.success_home')) ?></a>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
