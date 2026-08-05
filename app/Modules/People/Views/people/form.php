<?= $this->extend('layouts/backoffice') ?>

<?= $this->section('content') ?>

<?= view('Modules\Administration\Views\partials\flash') ?>

<?php
$isEdit = ($mode ?? 'create') === 'edit';
$action = $isEdit
    ? site_url('backoffice/people/' . (int) ($record['personne_id'] ?? 0))
    : site_url('backoffice/people');

$val = static function (array $record, string $key) {
    $old = old($key);
    if ($old !== null && $old !== '') {
        return $old;
    }

    return $record[$key] ?? '';
};

$maxDob = (new DateTimeImmutable('today'))->modify('-16 years')->format('Y-m-d');
?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_people')) ?></p>
        <h1><?= esc($isEdit ? lang('Backoffice.people_edit_title') : lang('Backoffice.people_create_title')) ?></h1>
        <p><?= esc(lang('Backoffice.people_form_lead')) ?></p>
    </div>
    <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/people') ?>">
        <i class="bi bi-arrow-left" aria-hidden="true"></i>
        <?= esc(lang('Backoffice.people_back_list')) ?>
    </a>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-form needs-validation" method="post" action="<?= esc($action) ?>" enctype="multipart/form-data" novalidate
          data-bo-people-form
          data-api-communes="<?= esc(site_url('api/communes'), 'attr') ?>"
          data-api-zones="<?= esc(site_url('api/zones'), 'attr') ?>"
          data-api-collines="<?= esc(site_url('api/collines'), 'attr') ?>"
          data-msg-required="<?= esc(lang('Backoffice.validation_required'), 'attr') ?>">
        <?= csrf_field() ?>

        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label" for="prenom_personne"><?= esc(lang('Backoffice.people_field_first_name')) ?> *</label>
                <input class="form-control" type="text" id="prenom_personne" name="prenom_personne" value="<?= esc($val($record, 'prenom_personne')) ?>" required maxlength="100">
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="nom_personne"><?= esc(lang('Backoffice.people_field_last_name')) ?> *</label>
                <input class="form-control" type="text" id="nom_personne" name="nom_personne" value="<?= esc($val($record, 'nom_personne')) ?>" required maxlength="100">
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label" for="sexe_id"><?= esc(lang('Backoffice.people_field_gender')) ?> *</label>
                <select class="form-select" id="sexe_id" name="sexe_id" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($sexes as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'sexe_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="date_naissance"><?= esc(lang('Backoffice.people_field_birth_date')) ?> *</label>
                <input class="form-control" type="date" id="date_naissance" name="date_naissance" value="<?= esc($val($record, 'date_naissance')) ?>" max="<?= esc($maxDob) ?>" required>
                <div class="form-text"><?= esc(lang('Backoffice.people_hint_min_age')) ?></div>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label" for="email"><?= esc(lang('Backoffice.people_field_email')) ?> *</label>
                <input class="form-control" type="email" id="email" name="email" value="<?= esc($val($record, 'email')) ?>" required maxlength="150">
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="telephone"><?= esc(lang('Backoffice.people_field_phone')) ?> *</label>
                <input class="form-control" type="text" id="telephone" name="telephone" value="<?= esc($val($record, 'telephone')) ?>" required maxlength="20">
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label" for="numero_cni"><?= esc(lang('Backoffice.people_field_cni')) ?> *</label>
                <input class="form-control" type="text" id="numero_cni" name="numero_cni" value="<?= esc($val($record, 'numero_cni')) ?>" required maxlength="50">
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="upload_cni"><?= esc(lang('Backoffice.people_field_cni_file')) ?> <?= $isEdit && ! empty($record['upload_cni']) ? '' : '*' ?></label>
                <input class="form-control" type="file" id="upload_cni" name="upload_cni" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" <?= $isEdit && ! empty($record['upload_cni']) ? '' : 'required' ?>>
                <div class="form-text"><?= esc(lang('Backoffice.people_hint_cni_file')) ?></div>
                <?php if ($isEdit && ! empty($record['upload_cni'])): ?>
                    <div class="mt-2">
                        <a href="<?= site_url('backoffice/people/' . (int) $record['personne_id'] . '/cni/view') ?>" target="_blank" rel="noopener">
                            <i class="bi bi-eye" aria-hidden="true"></i> <?= esc(lang('Backoffice.people_action_view_cni')) ?>
                        </a>
                    </div>
                <?php endif; ?>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>

            <div class="col-12">
                <label class="form-label" for="adresse_residence"><?= esc(lang('Backoffice.people_field_address')) ?> *</label>
                <textarea class="form-control" id="adresse_residence" name="adresse_residence" rows="3" required maxlength="1000"><?= esc($val($record, 'adresse_residence')) ?></textarea>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>

            <div class="col-12"><hr class="bo-form-divider"><h2 class="h6 mb-0"><?= esc(lang('Backoffice.people_section_birthplace')) ?></h2></div>

            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label" for="province_naissance_id"><?= esc(lang('Backoffice.people_field_province')) ?> *</label>
                <select class="form-select" id="province_naissance_id" name="province_naissance_id" data-loc="province" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($provinces as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'province_naissance_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label" for="commune_naissance_id"><?= esc(lang('Backoffice.people_field_commune')) ?> *</label>
                <select class="form-select" id="commune_naissance_id" name="commune_naissance_id" data-loc="commune" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($communes as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'commune_naissance_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label" for="zone_naissance_id"><?= esc(lang('Backoffice.people_field_zone')) ?> *</label>
                <select class="form-select" id="zone_naissance_id" name="zone_naissance_id" data-loc="zone" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($zones as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'zone_naissance_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label" for="colline_naissance_id"><?= esc(lang('Backoffice.people_field_colline')) ?> *</label>
                <select class="form-select" id="colline_naissance_id" name="colline_naissance_id" data-loc="colline" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($collines as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'colline_naissance_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
        </div>

        <div class="bo-form-actions mt-4">
            <button class="btn btn-bo-primary" type="submit">
                <i class="bi bi-check-lg" aria-hidden="true"></i>
                <?= esc($isEdit ? lang('Backoffice.people_save') : lang('Backoffice.people_create')) ?>
            </button>
            <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/people') ?>"><?= esc(lang('Backoffice.btn_cancel')) ?></a>
        </div>
    </form>
</section>

<?= $this->endSection() ?>
