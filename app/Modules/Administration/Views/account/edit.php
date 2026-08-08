<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.account_kicker')) ?></p>
        <h1><?= esc(lang('Backoffice.account_edit_title')) ?></h1>
        <p><?= esc(lang('Backoffice.account_edit_lead')) ?></p>
    </div>
    <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/my/profile') ?>">
        <i class="bi bi-arrow-left"></i> <?= esc(lang('Backoffice.account_back_profile')) ?>
    </a>
</section>

<section class="bo-panel bo-crud-panel">
    <form method="post" action="<?= site_url('backoffice/my/profile') ?>" class="bo-form needs-validation" novalidate>
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="email"><?= esc(lang('Backoffice.users_field_email')) ?> *</label>
                <input class="form-control" type="email" id="email" name="email" required maxlength="150"
                       value="<?= esc(old('email') ?? ($record['email'] ?? '')) ?>">
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="telephone"><?= esc(lang('Backoffice.users_field_phone')) ?> *</label>
                <input class="form-control" type="text" id="telephone" name="telephone" required maxlength="20"
                       value="<?= esc(old('telephone') ?? ($record['telephone'] ?? '')) ?>">
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
        </div>
        <div class="bo-form-actions mt-4">
            <button class="btn btn-bo-primary" type="submit"><?= esc(lang('Backoffice.account_save')) ?></button>
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
