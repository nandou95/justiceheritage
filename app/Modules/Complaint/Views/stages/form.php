<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<?php
$isEdit = ($mode ?? 'create') === 'edit';
$action = $isEdit
    ? site_url('backoffice/complaint-stages/' . (int) ($record['etape_plainte_id'] ?? 0))
    : site_url('backoffice/complaint-stages');
$val = static function (array $record, string $key) {
    $old = old($key);
    if ($old !== null && $old !== '') {
        return $old;
    }
    return $record[$key] ?? '';
};
$selectedProfiles = old('profil_ids');
if (! is_array($selectedProfiles)) {
    $selectedProfiles = $record['profil_ids'] ?? [];
}
$selectedProfiles = array_map('strval', (array) $selectedProfiles);
$isConvocation = old('is_convocation') !== null ? (bool) old('is_convocation') : filter_var($record['is_convocation'] ?? false, FILTER_VALIDATE_BOOLEAN);
$isAudience = old('is_audience') !== null ? (bool) old('is_audience') : filter_var($record['is_audience'] ?? false, FILTER_VALIDATE_BOOLEAN);
?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_complaint_stages')) ?></p>
        <h1><?= esc($isEdit ? lang('Backoffice.cs_edit_title') : lang('Backoffice.cs_create_title')) ?></h1>
        <p><?= esc(lang('Backoffice.cs_form_lead')) ?></p>
    </div>
    <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/complaint-stages') ?>">
        <i class="bi bi-arrow-left"></i> <?= esc(lang('Backoffice.cs_back_list')) ?>
    </a>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-form needs-validation" method="post" action="<?= esc($action) ?>" novalidate>
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label" for="description_etape_plainte"><?= esc(lang('Backoffice.cs_field_description')) ?> *</label>
                <input class="form-control" type="text" id="description_etape_plainte" name="description_etape_plainte" value="<?= esc($val($record, 'description_etape_plainte')) ?>" required>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="niveau_juridiction_id"><?= esc(lang('Backoffice.cs_field_level')) ?> *</label>
                <select class="form-select" id="niveau_juridiction_id" name="niveau_juridiction_id" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($levels as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'niveau_juridiction_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12">
                <label class="form-label" for="profil_ids"><?= esc(lang('Backoffice.cs_field_profiles')) ?> *</label>
                <select class="form-select" id="profil_ids" name="profil_ids[]" multiple size="8" required>
                    <?php foreach ($profiles as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= in_array((string) $opt['id'], $selectedProfiles, true) ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text"><?= esc(lang('Backoffice.cs_hint_profiles')) ?></div>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_convocation" name="is_convocation" value="1" <?= $isConvocation ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_convocation"><?= esc(lang('Backoffice.cs_field_convocation')) ?></label>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_audience" name="is_audience" value="1" <?= $isAudience ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_audience"><?= esc(lang('Backoffice.cs_field_audience')) ?></label>
                </div>
            </div>
        </div>
        <div class="bo-form-actions mt-4">
            <button class="btn btn-bo-primary" type="submit">
                <i class="bi bi-check-lg"></i> <?= esc($isEdit ? lang('Backoffice.cs_save') : lang('Backoffice.cs_create')) ?>
            </button>
            <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/complaint-stages') ?>"><?= esc(lang('Backoffice.btn_cancel')) ?></a>
        </div>
    </form>
</section>
<script>
document.querySelector('.bo-form.needs-validation')?.addEventListener('submit', function (event) {
    if (!this.checkValidity()) { event.preventDefault(); event.stopPropagation(); }
    this.classList.add('was-validated');
});
</script>
<?= $this->endSection() ?>
