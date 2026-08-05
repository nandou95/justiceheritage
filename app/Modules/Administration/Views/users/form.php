<?= $this->extend('layouts/backoffice') ?>

<?= $this->section('content') ?>

<?= view('Modules\Administration\Views\partials\flash') ?>

<?php
$isEdit = ($mode ?? 'create') === 'edit';
$action = $isEdit
    ? site_url('backoffice/users/' . (int) ($record['utilisateur_id'] ?? 0))
    : site_url('backoffice/users');

$val = static function (array $record, string $key) {
    $old = old($key);
    if ($old !== null && $old !== '') {
        return $old;
    }

    return $record[$key] ?? '';
};
?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_users')) ?></p>
        <h1><?= esc($isEdit ? lang('Backoffice.users_edit_title') : lang('Backoffice.users_create_title')) ?></h1>
        <p><?= esc(lang('Backoffice.users_form_lead')) ?></p>
    </div>
    <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/users') ?>">
        <i class="bi bi-arrow-left" aria-hidden="true"></i>
        <?= esc(lang('Backoffice.users_back_list')) ?>
    </a>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-form needs-validation" method="post" action="<?= esc($action) ?>" novalidate
          data-bo-user-form
          data-api-communes="<?= esc(site_url('api/communes'), 'attr') ?>"
          data-api-zones="<?= esc(site_url('api/zones'), 'attr') ?>"
          data-api-collines="<?= esc(site_url('api/collines'), 'attr') ?>"
          data-msg-required="<?= esc(lang('Backoffice.validation_required'), 'attr') ?>">
        <?= csrf_field() ?>

        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label" for="prenom_utilisateur"><?= esc(lang('Backoffice.users_field_first_name')) ?> *</label>
                <input class="form-control" type="text" id="prenom_utilisateur" name="prenom_utilisateur" value="<?= esc($val($record, 'prenom_utilisateur')) ?>" required>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="nom_utilisateur"><?= esc(lang('Backoffice.users_field_last_name')) ?> *</label>
                <input class="form-control" type="text" id="nom_utilisateur" name="nom_utilisateur" value="<?= esc($val($record, 'nom_utilisateur')) ?>" required>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label" for="numero_cni"><?= esc(lang('Backoffice.users_field_cni')) ?> *</label>
                <input class="form-control" type="text" id="numero_cni" name="numero_cni" value="<?= esc($val($record, 'numero_cni')) ?>" required>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="numero_matricule"><?= esc(lang('Backoffice.users_field_matricule')) ?> *</label>
                <input class="form-control" type="text" id="numero_matricule" name="numero_matricule" value="<?= esc($val($record, 'numero_matricule')) ?>" required>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label" for="email"><?= esc(lang('Backoffice.users_field_email')) ?> *</label>
                <input class="form-control" type="email" id="email" name="email" value="<?= esc($val($record, 'email')) ?>" required>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="telephone"><?= esc(lang('Backoffice.users_field_phone')) ?> *</label>
                <input class="form-control" type="text" id="telephone" name="telephone" value="<?= esc($val($record, 'telephone')) ?>" required>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label" for="date_naissance"><?= esc(lang('Backoffice.users_field_birth_date')) ?> *</label>
                <input class="form-control" type="date" id="date_naissance" name="date_naissance" value="<?= esc($val($record, 'date_naissance')) ?>" required>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="sexe_id"><?= esc(lang('Backoffice.users_field_sex')) ?> *</label>
                <select class="form-select" id="sexe_id" name="sexe_id" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($sexes as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'sexe_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label" for="profil_id"><?= esc(lang('Backoffice.users_field_profile')) ?> *</label>
                <select class="form-select" id="profil_id" name="profil_id" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($profiles as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'profil_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="juridiction_id"><?= esc(lang('Backoffice.users_field_jurisdiction')) ?> *</label>
                <select class="form-select" id="juridiction_id" name="juridiction_id" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($jurisdictions as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'juridiction_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>

            <div class="col-12"><hr class="bo-form-divider"></div>
            <div class="col-12"><h2 class="bo-form-section-title"><?= esc(lang('Backoffice.users_section_birthplace')) ?></h2></div>

            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label" for="province_naissance_id"><?= esc(lang('Backoffice.users_field_province')) ?> *</label>
                <select class="form-select" id="province_naissance_id" name="province_naissance_id" data-loc="province" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($provinces as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'province_naissance_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label" for="commune_naissance_id"><?= esc(lang('Backoffice.users_field_commune')) ?> *</label>
                <select class="form-select" id="commune_naissance_id" name="commune_naissance_id" data-loc="commune" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($communes as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'commune_naissance_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label" for="zone_naissance_id"><?= esc(lang('Backoffice.users_field_zone')) ?> *</label>
                <select class="form-select" id="zone_naissance_id" name="zone_naissance_id" data-loc="zone" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($zones as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'zone_naissance_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label" for="colline_naissance_id"><?= esc(lang('Backoffice.users_field_colline')) ?> *</label>
                <select class="form-select" id="colline_naissance_id" name="colline_naissance_id" data-loc="colline" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($collines as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'colline_naissance_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
        </div>

        <div class="bo-form-actions">
            <a class="btn btn-outline-secondary" href="<?= site_url('backoffice/users') ?>"><?= esc(lang('Backoffice.btn_cancel')) ?></a>
            <button class="btn btn-bo-primary" type="submit">
                <?= esc($isEdit ? lang('Backoffice.users_save') : lang('Backoffice.users_create')) ?>
            </button>
        </div>
    </form>
</section>

<?= $this->endSection() ?>
