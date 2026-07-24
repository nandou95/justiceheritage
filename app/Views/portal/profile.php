<?= $this->extend('layouts/portal') ?>

<?= $this->section('content') ?>

<div class="jh-portal-head">
    <h1><?= esc(lang('Portal.profile_h1')) ?></h1>
    <p><?= esc(lang('Portal.profile_lead')) ?></p>
</div>

<div class="jh-panel" style="max-width:40rem;">
    <p class="jh-profile-badge mb-3"><?= esc(lang('Portal.profile_2fa')) ?> · <?= esc(lang('Portal.profile_2fa_on')) ?></p>

    <form class="jh-portal-form" method="post" action="<?= site_url('portal/profile') ?>">
        <?= csrf_field() ?>
        <div class="jh-auth-grid">
            <div class="jh-field">
                <label class="form-label" for="first_name"><?= esc(lang('Site.label_first_name')) ?></label>
                <input class="form-control" type="text" id="first_name" name="first_name" value="<?= esc(explode(' ', $user['name'])[0] ?? '') ?>">
            </div>
            <div class="jh-field">
                <label class="form-label" for="last_name"><?= esc(lang('Site.label_last_name')) ?></label>
                <input class="form-control" type="text" id="last_name" name="last_name" value="<?= esc(explode(' ', $user['name'], 2)[1] ?? '') ?>">
            </div>
            <div class="jh-field jh-field--full">
                <label class="form-label" for="email"><?= esc(lang('Site.label_email')) ?></label>
                <input class="form-control" type="email" id="email" name="email" value="<?= esc($user['email']) ?>">
            </div>
            <div class="jh-field">
                <label class="form-label" for="phone"><?= esc(lang('Site.label_phone')) ?></label>
                <input class="form-control" type="tel" id="phone" name="phone" value="<?= esc($user['phone']) ?>">
            </div>
            <div class="jh-field">
                <label class="form-label" for="national_id"><?= esc(lang('Site.label_national_id')) ?></label>
                <input class="form-control" type="text" id="national_id" name="national_id" value="<?= esc($user['id']) ?>" readonly>
            </div>
        </div>
        <div class="jh-portal-actions mt-3">
            <button class="btn btn-jh-primary" type="submit"><?= esc(lang('Portal.profile_save')) ?></button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>
