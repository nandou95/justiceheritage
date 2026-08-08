<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.account_kicker')) ?></p>
        <h1><?= esc(lang('Backoffice.account_password_title')) ?></h1>
        <p><?= esc(lang('Backoffice.account_password_lead')) ?></p>
    </div>
    <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/my/profile') ?>">
        <i class="bi bi-arrow-left"></i> <?= esc(lang('Backoffice.account_back_profile')) ?>
    </a>
</section>

<section class="bo-panel bo-crud-panel">
    <form method="post" action="<?= site_url('backoffice/my/password') ?>" class="bo-form needs-validation" novalidate>
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="current_password"><?= esc(lang('Backoffice.account_current_password')) ?> *</label>
                <input class="form-control" type="password" id="current_password" name="current_password" required autocomplete="current-password">
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-6">
                <label class="form-label" for="new_password"><?= esc(lang('Backoffice.account_new_password')) ?> *</label>
                <input class="form-control" type="password" id="new_password" name="new_password" required minlength="8" autocomplete="new-password">
                <div class="form-text"><?= esc(lang('Backoffice.people_hint_password')) ?></div>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="new_password_confirm"><?= esc(lang('Backoffice.account_confirm_password')) ?> *</label>
                <input class="form-control" type="password" id="new_password_confirm" name="new_password_confirm" required minlength="8" autocomplete="new-password">
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
        </div>
        <div class="bo-form-actions mt-4">
            <button class="btn btn-bo-primary" type="submit"><?= esc(lang('Backoffice.account_save_password')) ?></button>
            <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/my/profile') ?>"><?= esc(lang('Backoffice.btn_cancel')) ?></a>
        </div>
    </form>
</section>
<script>
document.querySelector('.needs-validation')?.addEventListener('submit', function (e) {
  if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
  this.classList.add('was-validated');
});
</script>
<?= $this->endSection() ?>
