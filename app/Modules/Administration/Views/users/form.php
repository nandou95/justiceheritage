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

$maxDob = (new DateTimeImmutable('today'))->modify('-16 years')->format('Y-m-d');
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
    <form class="bo-form bo-form-user needs-validation" method="post" action="<?= esc($action) ?>" novalidate
          data-bo-user-form
          data-bo-user-wizard
          data-api-communes="<?= esc(site_url('api/communes'), 'attr') ?>"
          data-api-zones="<?= esc(site_url('api/zones'), 'attr') ?>"
          data-api-collines="<?= esc(site_url('api/collines'), 'attr') ?>"
          data-msg-required="<?= esc(lang('Backoffice.validation_required'), 'attr') ?>"
          data-msg-email="<?= esc(lang('Backoffice.users_err_email'), 'attr') ?>"
          data-msg-min-age="<?= esc(lang('Backoffice.users_err_min_age'), 'attr') ?>">
        <?= csrf_field() ?>

        <div class="bo-wizard" data-wizard>
            <div class="bo-wizard-progress" aria-live="polite">
                <div class="bo-wizard-steps" role="list">
                    <div class="bo-wizard-step is-active" data-wizard-indicator="1" role="listitem">
                        <span class="bo-wizard-step-index">1</span>
                        <span class="bo-wizard-step-label"><?= esc(lang('Backoffice.users_wizard_step1_short')) ?></span>
                    </div>
                    <div class="bo-wizard-connector" aria-hidden="true"></div>
                    <div class="bo-wizard-step" data-wizard-indicator="2" role="listitem">
                        <span class="bo-wizard-step-index">2</span>
                        <span class="bo-wizard-step-label"><?= esc(lang('Backoffice.users_wizard_step2_short')) ?></span>
                    </div>
                </div>
                <p class="bo-wizard-status" data-wizard-status>
                    <?= esc(lang('Backoffice.users_wizard_progress', [1, 2])) ?>
                </p>
            </div>

            <div class="bo-wizard-pane is-active" data-wizard-step="1">
                <fieldset class="bo-form-section">
                    <legend class="bo-form-section-title"><?= esc(lang('Backoffice.users_section_identity')) ?></legend>
                    <div class="row g-3">
                        <div class="col-12 col-md-6 col-xl-3">
                            <label class="form-label" for="prenom_utilisateur"><?= esc(lang('Backoffice.users_field_first_name')) ?> *</label>
                            <input class="form-control" type="text" id="prenom_utilisateur" name="prenom_utilisateur"
                                   value="<?= esc($val($record, 'prenom_utilisateur')) ?>"
                                   required maxlength="100" autocomplete="given-name"
                                   data-max-length="100"
                                   data-max-msg="<?= esc(lang('Backoffice.users_err_max_length', [lang('Backoffice.users_field_first_name'), 100]), 'attr') ?>">
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <label class="form-label" for="nom_utilisateur"><?= esc(lang('Backoffice.users_field_last_name')) ?> *</label>
                            <input class="form-control" type="text" id="nom_utilisateur" name="nom_utilisateur"
                                   value="<?= esc($val($record, 'nom_utilisateur')) ?>"
                                   required maxlength="100" autocomplete="family-name"
                                   data-max-length="100"
                                   data-max-msg="<?= esc(lang('Backoffice.users_err_max_length', [lang('Backoffice.users_field_last_name'), 100]), 'attr') ?>">
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <label class="form-label" for="sexe_id"><?= esc(lang('Backoffice.users_field_sex')) ?> *</label>
                            <select class="form-select" id="sexe_id" name="sexe_id" required>
                                <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                                <?php foreach ($sexes as $opt): ?>
                                    <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'sexe_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <label class="form-label" for="date_naissance"><?= esc(lang('Backoffice.users_field_birth_date')) ?> *</label>
                            <input class="form-control" type="date" id="date_naissance" name="date_naissance"
                                   value="<?= esc($val($record, 'date_naissance')) ?>"
                                   required max="<?= esc($maxDob, 'attr') ?>">
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <label class="form-label" for="numero_cni"><?= esc(lang('Backoffice.users_field_cni')) ?> *</label>
                            <input class="form-control" type="text" id="numero_cni" name="numero_cni"
                                   value="<?= esc($val($record, 'numero_cni')) ?>"
                                   required maxlength="50"
                                   data-max-length="50"
                                   data-max-msg="<?= esc(lang('Backoffice.users_err_max_length', [lang('Backoffice.users_field_cni'), 50]), 'attr') ?>">
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <label class="form-label" for="numero_matricule"><?= esc(lang('Backoffice.users_field_matricule')) ?> *</label>
                            <input class="form-control" type="text" id="numero_matricule" name="numero_matricule"
                                   value="<?= esc($val($record, 'numero_matricule')) ?>"
                                   required maxlength="50"
                                   data-max-length="50"
                                   data-max-msg="<?= esc(lang('Backoffice.users_err_max_length', [lang('Backoffice.users_field_matricule'), 50]), 'attr') ?>">
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="bo-form-section">
                    <legend class="bo-form-section-title"><?= esc(lang('Backoffice.users_section_contact')) ?></legend>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="email"><?= esc(lang('Backoffice.users_field_email')) ?> *</label>
                            <input class="form-control" type="email" id="email" name="email"
                                   value="<?= esc($val($record, 'email')) ?>"
                                   required maxlength="150" autocomplete="email"
                                   data-max-length="150"
                                   data-max-msg="<?= esc(lang('Backoffice.users_err_max_length', [lang('Backoffice.users_field_email'), 150]), 'attr') ?>">
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="telephone"><?= esc(lang('Backoffice.users_field_phone')) ?> *</label>
                            <input class="form-control" type="text" id="telephone" name="telephone"
                                   value="<?= esc($val($record, 'telephone')) ?>"
                                   required maxlength="20" autocomplete="tel"
                                   data-max-length="20"
                                   data-max-msg="<?= esc(lang('Backoffice.users_err_max_length', [lang('Backoffice.users_field_phone'), 20]), 'attr') ?>">
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="bo-wizard-pane" data-wizard-step="2" hidden>
                <fieldset class="bo-form-section">
                    <legend class="bo-form-section-title"><?= esc(lang('Backoffice.users_section_birthplace')) ?></legend>
                    <div class="row g-3">
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
                </fieldset>

                <fieldset class="bo-form-section">
                    <legend class="bo-form-section-title"><?= esc(lang('Backoffice.users_section_assignment')) ?></legend>
                    <div class="row g-3">
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
                    </div>
                </fieldset>
            </div>

            <div class="bo-form-actions bo-wizard-actions">
                <a class="btn btn-outline-secondary" href="<?= site_url('backoffice/users') ?>"><?= esc(lang('Backoffice.btn_cancel')) ?></a>
                <div class="bo-wizard-nav">
                    <button class="btn btn-bo-secondary" type="button" data-wizard-prev hidden>
                        <i class="bi bi-arrow-left" aria-hidden="true"></i>
                        <?= esc(lang('Backoffice.users_wizard_prev')) ?>
                    </button>
                    <button class="btn btn-bo-primary" type="button" data-wizard-next>
                        <?= esc(lang('Backoffice.users_wizard_next')) ?>
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </button>
                    <button class="btn btn-bo-primary" type="submit" data-wizard-submit hidden>
                        <?= esc($isEdit ? lang('Backoffice.users_update') : lang('Backoffice.users_save_user')) ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</section>

<script>
window.JH_USER_WIZARD_I18N = {
    progress: <?= json_encode(lang('Backoffice.users_wizard_progress')) ?>
};
</script>

<?= $this->endSection() ?>
