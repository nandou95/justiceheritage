<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>
<?php
$isEdit = ($mode ?? 'create') === 'edit';
$action = $isEdit ? site_url('backoffice/court-jurisdictions/' . (int) ($record['juridiction_id'] ?? 0)) : site_url('backoffice/court-jurisdictions');
$val = static function (array $record, string $key) {
    $old = old($key);
    return ($old !== null && $old !== '') ? $old : ($record[$key] ?? '');
};
?>
<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_court_jurisdictions')) ?></p>
        <h1><?= esc($isEdit ? lang('Backoffice.cj_edit_title') : lang('Backoffice.cj_create_title')) ?></h1>
        <p><?= esc(lang('Backoffice.cj_form_lead')) ?></p>
    </div>
    <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/court-jurisdictions') ?>"><i class="bi bi-arrow-left"></i> <?= esc(lang('Backoffice.cj_back_list')) ?></a>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-form needs-validation" method="post" action="<?= esc($action) ?>" novalidate data-bo-cj-form
          data-api-communes="<?= esc(site_url('api/communes'), 'attr') ?>"
          data-api-zones="<?= esc(site_url('api/zones'), 'attr') ?>"
          data-api-collines="<?= esc(site_url('api/collines'), 'attr') ?>">
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label" for="code_juridiction"><?= esc(lang('Backoffice.cj_field_code')) ?> *</label>
                <input class="form-control" id="code_juridiction" name="code_juridiction" value="<?= esc($val($record, 'code_juridiction')) ?>" required><div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div></div>
            <div class="col-md-8"><label class="form-label" for="nom_juridiction"><?= esc(lang('Backoffice.cj_field_name')) ?> *</label>
                <input class="form-control" id="nom_juridiction" name="nom_juridiction" value="<?= esc($val($record, 'nom_juridiction')) ?>" required><div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div></div>
            <div class="col-md-6"><label class="form-label" for="niveau_juridiction_id"><?= esc(lang('Backoffice.cj_field_level')) ?> *</label>
                <select class="form-select" id="niveau_juridiction_id" name="niveau_juridiction_id" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($niveaux as $opt): ?><option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'niveau_juridiction_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option><?php endforeach; ?>
                </select><div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div></div>
            <div class="col-md-6"><label class="form-label" for="adresse"><?= esc(lang('Backoffice.cj_field_address')) ?> *</label>
                <input class="form-control" id="adresse" name="adresse" value="<?= esc($val($record, 'adresse')) ?>" required><div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div></div>
            <div class="col-md-6"><label class="form-label" for="telephone"><?= esc(lang('Backoffice.cj_field_phone')) ?> *</label>
                <input class="form-control" id="telephone" name="telephone" value="<?= esc($val($record, 'telephone')) ?>" required><div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div></div>
            <div class="col-md-6"><label class="form-label" for="email"><?= esc(lang('Backoffice.cj_field_email')) ?> *</label>
                <input class="form-control" type="email" id="email" name="email" value="<?= esc($val($record, 'email')) ?>" required><div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div></div>
            <div class="col-12"><hr class="bo-form-divider"><h2 class="bo-form-section-title"><?= esc(lang('Backoffice.cj_section_location')) ?></h2></div>
            <div class="col-md-3"><label class="form-label" for="province_id"><?= esc(lang('Backoffice.cj_field_province')) ?> *</label>
                <select class="form-select" id="province_id" name="province_id" data-loc="province" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($provinces as $opt): ?><option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'province_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option><?php endforeach; ?>
                </select><div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div></div>
            <div class="col-md-3"><label class="form-label" for="commune_id"><?= esc(lang('Backoffice.cj_field_commune')) ?> *</label>
                <select class="form-select" id="commune_id" name="commune_id" data-loc="commune" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($communes as $opt): ?><option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'commune_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option><?php endforeach; ?>
                </select><div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div></div>
            <div class="col-md-3"><label class="form-label" for="zone_id"><?= esc(lang('Backoffice.cj_field_zone')) ?> *</label>
                <select class="form-select" id="zone_id" name="zone_id" data-loc="zone" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($zones as $opt): ?><option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'zone_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option><?php endforeach; ?>
                </select><div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div></div>
            <div class="col-md-3"><label class="form-label" for="colline_id"><?= esc(lang('Backoffice.cj_field_colline')) ?> *</label>
                <select class="form-select" id="colline_id" name="colline_id" data-loc="colline" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($collines as $opt): ?><option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'colline_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option><?php endforeach; ?>
                </select><div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div></div>
        </div>
        <div class="bo-form-actions">
            <a class="btn btn-outline-secondary" href="<?= site_url('backoffice/court-jurisdictions') ?>"><?= esc(lang('Backoffice.btn_cancel')) ?></a>
            <button class="btn btn-bo-primary" type="submit"><?= esc($isEdit ? lang('Backoffice.cj_save') : lang('Backoffice.cj_create')) ?></button>
        </div>
    </form>
</section>
<?= $this->endSection() ?>
