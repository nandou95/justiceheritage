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

$maxDob     = (new DateTimeImmutable('today'))->modify('-16 years')->format('Y-m-d');
$totalSteps = $isEdit ? 4 : 5;
$cniRequired = ! ($isEdit && ! empty($record['upload_cni']));
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
          data-bo-people-wizard
          data-api-communes="<?= esc(site_url('api/communes'), 'attr') ?>"
          data-api-zones="<?= esc(site_url('api/zones'), 'attr') ?>"
          data-api-collines="<?= esc(site_url('api/collines'), 'attr') ?>"
          data-msg-required="<?= esc(lang('Backoffice.validation_required'), 'attr') ?>"
          data-msg-email="<?= esc(lang('Backoffice.people_err_email'), 'attr') ?>"
          data-msg-min-age="<?= esc(lang('Backoffice.people_err_min_age'), 'attr') ?>"
          data-msg-password="<?= esc(lang('Backoffice.people_err_password_min'), 'attr') ?>"
          data-msg-password-match="<?= esc(lang('Backoffice.people_err_password_match'), 'attr') ?>"
          data-msg-account-partial="<?= esc(lang('Backoffice.people_err_account_partial'), 'attr') ?>"
          data-msg-cni-type="<?= esc(lang('Backoffice.people_err_cni_type'), 'attr') ?>"
          data-msg-cni-size="<?= esc(lang('Backoffice.people_err_cni_size'), 'attr') ?>"
          data-msg-no-account="<?= esc(lang('Backoffice.people_review_no_account'), 'attr') ?>"
          data-msg-show-password="<?= esc(lang('Backoffice.people_show_password'), 'attr') ?>"
          data-msg-hide-password="<?= esc(lang('Backoffice.people_hide_password'), 'attr') ?>"
          data-cni-max-bytes="<?= esc((string) (2048 * 1024), 'attr') ?>"
          data-min-age="16">
        <?= csrf_field() ?>

        <div class="bo-wizard" data-wizard>
            <div class="bo-wizard-progress" aria-live="polite">
                <div class="bo-wizard-steps" role="list">
                    <button type="button" class="bo-wizard-step is-active" data-wizard-indicator="1" role="listitem">
                        <span class="bo-wizard-step-index">1</span>
                        <span class="bo-wizard-step-label"><?= esc(lang('Backoffice.people_wizard_step1_short')) ?></span>
                    </button>
                    <div class="bo-wizard-connector" aria-hidden="true"></div>
                    <button type="button" class="bo-wizard-step" data-wizard-indicator="2" role="listitem">
                        <span class="bo-wizard-step-index">2</span>
                        <span class="bo-wizard-step-label"><?= esc(lang('Backoffice.people_wizard_step2_short')) ?></span>
                    </button>
                    <div class="bo-wizard-connector" aria-hidden="true"></div>
                    <button type="button" class="bo-wizard-step" data-wizard-indicator="3" role="listitem">
                        <span class="bo-wizard-step-index">3</span>
                        <span class="bo-wizard-step-label"><?= esc(lang('Backoffice.people_wizard_step3_short')) ?></span>
                    </button>
                    <?php if (! $isEdit): ?>
                        <div class="bo-wizard-connector" aria-hidden="true"></div>
                        <button type="button" class="bo-wizard-step" data-wizard-indicator="4" role="listitem">
                            <span class="bo-wizard-step-index">4</span>
                            <span class="bo-wizard-step-label"><?= esc(lang('Backoffice.people_wizard_step4_short')) ?></span>
                        </button>
                        <div class="bo-wizard-connector" aria-hidden="true"></div>
                        <button type="button" class="bo-wizard-step" data-wizard-indicator="5" role="listitem">
                            <span class="bo-wizard-step-index">5</span>
                            <span class="bo-wizard-step-label"><?= esc(lang('Backoffice.people_wizard_step5_short')) ?></span>
                        </button>
                    <?php else: ?>
                        <div class="bo-wizard-connector" aria-hidden="true"></div>
                        <button type="button" class="bo-wizard-step" data-wizard-indicator="4" role="listitem">
                            <span class="bo-wizard-step-index">4</span>
                            <span class="bo-wizard-step-label"><?= esc(lang('Backoffice.people_wizard_step5_short')) ?></span>
                        </button>
                    <?php endif; ?>
                </div>
                <p class="bo-wizard-status" data-wizard-status>
                    <?= esc(lang('Backoffice.people_wizard_progress', [1, $totalSteps])) ?>
                </p>
            </div>

            <div class="bo-wizard-pane is-active" data-wizard-step="1">
                <fieldset class="bo-form-section">
                    <legend class="bo-form-section-title"><?= esc(lang('Backoffice.people_wizard_step1_title')) ?></legend>
                    <p class="bo-wizard-step-lead"><?= esc(lang('Backoffice.people_wizard_step1_lead')) ?></p>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="prenom_personne"><?= esc(lang('Backoffice.people_field_first_name')) ?> *</label>
                            <input class="form-control" type="text" id="prenom_personne" name="prenom_personne"
                                   value="<?= esc($val($record, 'prenom_personne')) ?>" required maxlength="100" autocomplete="given-name">
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="nom_personne"><?= esc(lang('Backoffice.people_field_last_name')) ?> *</label>
                            <input class="form-control" type="text" id="nom_personne" name="nom_personne"
                                   value="<?= esc($val($record, 'nom_personne')) ?>" required maxlength="100" autocomplete="family-name">
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="sexe_id"><?= esc(lang('Backoffice.people_field_gender')) ?> *</label>
                            <select class="form-select" id="sexe_id" name="sexe_id" required data-review-label>
                                <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                                <?php foreach ($sexes as $opt): ?>
                                    <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'sexe_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="date_naissance"><?= esc(lang('Backoffice.people_field_birth_date')) ?> *</label>
                            <input class="form-control" type="date" id="date_naissance" name="date_naissance"
                                   value="<?= esc($val($record, 'date_naissance')) ?>" max="<?= esc($maxDob) ?>" required>
                            <div class="form-text"><?= esc(lang('Backoffice.people_hint_min_age')) ?></div>
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="numero_cni"><?= esc(lang('Backoffice.people_field_cni')) ?> *</label>
                            <input class="form-control" type="text" id="numero_cni" name="numero_cni"
                                   value="<?= esc($val($record, 'numero_cni')) ?>" required maxlength="50">
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="upload_cni"><?= esc(lang('Backoffice.people_field_cni_file')) ?> <?= $cniRequired ? '*' : '' ?></label>
                            <input class="form-control" type="file" id="upload_cni" name="upload_cni"
                                   accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                   <?= $cniRequired ? 'required' : '' ?>>
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
                    </div>
                </fieldset>
            </div>

            <div class="bo-wizard-pane" data-wizard-step="2" hidden>
                <fieldset class="bo-form-section">
                    <legend class="bo-form-section-title"><?= esc(lang('Backoffice.people_wizard_step2_title')) ?></legend>
                    <p class="bo-wizard-step-lead"><?= esc(lang('Backoffice.people_wizard_step2_lead')) ?></p>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="telephone"><?= esc(lang('Backoffice.people_field_phone')) ?> *</label>
                            <input class="form-control" type="text" id="telephone" name="telephone"
                                   value="<?= esc($val($record, 'telephone')) ?>" required maxlength="20" autocomplete="tel">
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="email"><?= esc(lang('Backoffice.people_field_email')) ?> *</label>
                            <input class="form-control" type="email" id="email" name="email"
                                   value="<?= esc($val($record, 'email')) ?>" required maxlength="150" autocomplete="email">
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="adresse_residence"><?= esc(lang('Backoffice.people_field_address')) ?> *</label>
                            <textarea class="form-control" id="adresse_residence" name="adresse_residence" rows="3" required maxlength="1000"><?= esc($val($record, 'adresse_residence')) ?></textarea>
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="bo-wizard-pane" data-wizard-step="3" hidden>
                <fieldset class="bo-form-section">
                    <legend class="bo-form-section-title"><?= esc(lang('Backoffice.people_wizard_step3_title')) ?></legend>
                    <p class="bo-wizard-step-lead"><?= esc(lang('Backoffice.people_wizard_step3_lead')) ?></p>
                    <div class="row g-3">
                        <div class="col-12 col-md-6 col-xl-3">
                            <label class="form-label" for="province_naissance_id"><?= esc(lang('Backoffice.people_field_province')) ?> *</label>
                            <select class="form-select" id="province_naissance_id" name="province_naissance_id" data-loc="province" required data-review-label>
                                <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                                <?php foreach ($provinces as $opt): ?>
                                    <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'province_naissance_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <label class="form-label" for="commune_naissance_id"><?= esc(lang('Backoffice.people_field_commune')) ?> *</label>
                            <select class="form-select" id="commune_naissance_id" name="commune_naissance_id" data-loc="commune" required data-review-label>
                                <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                                <?php foreach ($communes as $opt): ?>
                                    <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'commune_naissance_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <label class="form-label" for="zone_naissance_id"><?= esc(lang('Backoffice.people_field_zone')) ?> *</label>
                            <select class="form-select" id="zone_naissance_id" name="zone_naissance_id" data-loc="zone" required data-review-label>
                                <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                                <?php foreach ($zones as $opt): ?>
                                    <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'zone_naissance_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <label class="form-label" for="colline_naissance_id"><?= esc(lang('Backoffice.people_field_colline')) ?> *</label>
                            <select class="form-select" id="colline_naissance_id" name="colline_naissance_id" data-loc="colline" required data-review-label>
                                <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                                <?php foreach ($collines as $opt): ?>
                                    <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'colline_naissance_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                    </div>
                </fieldset>
            </div>

            <?php if (! $isEdit): ?>
                <div class="bo-wizard-pane" data-wizard-step="4" data-wizard-optional="1" hidden>
                    <fieldset class="bo-form-section">
                        <legend class="bo-form-section-title"><?= esc(lang('Backoffice.people_wizard_step4_title')) ?></legend>
                        <p class="bo-wizard-step-lead"><?= esc(lang('Backoffice.people_wizard_step4_lead')) ?></p>
                        <div class="bo-wizard-optional-note" role="note"><?= esc(lang('Backoffice.people_wizard_step4_optional')) ?></div>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="username"><?= esc(lang('Backoffice.people_field_username')) ?></label>
                                <input class="form-control" type="text" id="username" name="username"
                                       value="<?= esc(old('username')) ?>" maxlength="100" autocomplete="username" minlength="3">
                                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="password"><?= esc(lang('Backoffice.people_field_password')) ?></label>
                                <div class="input-group">
                                    <input class="form-control" type="password" id="password" name="password"
                                           autocomplete="new-password" minlength="8">
                                    <button class="btn btn-outline-secondary" type="button" data-password-toggle data-target="password"
                                            aria-label="<?= esc(lang('Backoffice.people_show_password'), 'attr') ?>">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <div class="form-text"><?= esc(lang('Backoffice.people_hint_password')) ?></div>
                                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="password_confirm"><?= esc(lang('Backoffice.people_field_password_confirm')) ?></label>
                                <div class="input-group">
                                    <input class="form-control" type="password" id="password_confirm" name="password_confirm"
                                           autocomplete="new-password" minlength="8">
                                    <button class="btn btn-outline-secondary" type="button" data-password-toggle data-target="password_confirm"
                                            aria-label="<?= esc(lang('Backoffice.people_show_password'), 'attr') ?>">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            <?php endif; ?>

            <div class="bo-wizard-pane" data-wizard-step="<?= $isEdit ? '4' : '5' ?>" data-wizard-review hidden>
                <fieldset class="bo-form-section">
                    <legend class="bo-form-section-title"><?= esc(lang('Backoffice.people_wizard_step5_title')) ?></legend>
                    <p class="bo-wizard-step-lead"><?= esc(lang('Backoffice.people_wizard_step5_lead')) ?></p>

                    <div class="bo-wizard-review" data-people-review>
                        <article class="bo-wizard-review-card">
                            <header>
                                <h3><?= esc(lang('Backoffice.people_wizard_step1_title')) ?></h3>
                                <button type="button" class="btn btn-link btn-sm" data-wizard-edit="1"><?= esc(lang('Backoffice.people_wizard_edit')) ?></button>
                            </header>
                            <dl>
                                <div><dt><?= esc(lang('Backoffice.people_field_first_name')) ?></dt><dd data-review="prenom_personne">—</dd></div>
                                <div><dt><?= esc(lang('Backoffice.people_field_last_name')) ?></dt><dd data-review="nom_personne">—</dd></div>
                                <div><dt><?= esc(lang('Backoffice.people_field_gender')) ?></dt><dd data-review="sexe_id">—</dd></div>
                                <div><dt><?= esc(lang('Backoffice.people_field_birth_date')) ?></dt><dd data-review="date_naissance">—</dd></div>
                                <div><dt><?= esc(lang('Backoffice.people_field_cni')) ?></dt><dd data-review="numero_cni">—</dd></div>
                                <div><dt><?= esc(lang('Backoffice.people_field_cni_file')) ?></dt><dd data-review="upload_cni">—</dd></div>
                            </dl>
                        </article>

                        <article class="bo-wizard-review-card">
                            <header>
                                <h3><?= esc(lang('Backoffice.people_wizard_step2_title')) ?></h3>
                                <button type="button" class="btn btn-link btn-sm" data-wizard-edit="2"><?= esc(lang('Backoffice.people_wizard_edit')) ?></button>
                            </header>
                            <dl>
                                <div><dt><?= esc(lang('Backoffice.people_field_phone')) ?></dt><dd data-review="telephone">—</dd></div>
                                <div><dt><?= esc(lang('Backoffice.people_field_email')) ?></dt><dd data-review="email">—</dd></div>
                                <div class="bo-wizard-review-full"><dt><?= esc(lang('Backoffice.people_field_address')) ?></dt><dd data-review="adresse_residence">—</dd></div>
                            </dl>
                        </article>

                        <article class="bo-wizard-review-card">
                            <header>
                                <h3><?= esc(lang('Backoffice.people_wizard_step3_title')) ?></h3>
                                <button type="button" class="btn btn-link btn-sm" data-wizard-edit="3"><?= esc(lang('Backoffice.people_wizard_edit')) ?></button>
                            </header>
                            <dl>
                                <div><dt><?= esc(lang('Backoffice.people_field_province')) ?></dt><dd data-review="province_naissance_id">—</dd></div>
                                <div><dt><?= esc(lang('Backoffice.people_field_commune')) ?></dt><dd data-review="commune_naissance_id">—</dd></div>
                                <div><dt><?= esc(lang('Backoffice.people_field_zone')) ?></dt><dd data-review="zone_naissance_id">—</dd></div>
                                <div><dt><?= esc(lang('Backoffice.people_field_colline')) ?></dt><dd data-review="colline_naissance_id">—</dd></div>
                            </dl>
                        </article>

                        <?php if (! $isEdit): ?>
                            <article class="bo-wizard-review-card">
                                <header>
                                    <h3><?= esc(lang('Backoffice.people_wizard_step4_title')) ?></h3>
                                    <button type="button" class="btn btn-link btn-sm" data-wizard-edit="4"><?= esc(lang('Backoffice.people_wizard_edit')) ?></button>
                                </header>
                                <dl>
                                    <div><dt><?= esc(lang('Backoffice.people_field_username')) ?></dt><dd data-review="username">—</dd></div>
                                    <div><dt><?= esc(lang('Backoffice.people_field_password')) ?></dt><dd data-review="password_mask">—</dd></div>
                                </dl>
                            </article>
                        <?php endif; ?>
                    </div>
                </fieldset>
            </div>

            <div class="bo-form-actions bo-wizard-actions">
                <a class="btn btn-outline-secondary" href="<?= site_url('backoffice/people') ?>"><?= esc(lang('Backoffice.btn_cancel')) ?></a>
                <div class="bo-wizard-nav">
                    <button class="btn btn-bo-secondary" type="button" data-wizard-prev hidden>
                        <i class="bi bi-arrow-left" aria-hidden="true"></i>
                        <?= esc(lang('Backoffice.people_wizard_prev')) ?>
                    </button>
                    <button class="btn btn-bo-primary" type="button" data-wizard-next>
                        <?= esc(lang('Backoffice.people_wizard_next')) ?>
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </button>
                    <button class="btn btn-bo-primary" type="submit" data-wizard-submit hidden>
                        <i class="bi bi-check-lg" aria-hidden="true"></i>
                        <?= esc($isEdit ? lang('Backoffice.people_save') : lang('Backoffice.people_create')) ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</section>

<script>
window.JH_PEOPLE_WIZARD_I18N = {
    progress: <?= json_encode(lang('Backoffice.people_wizard_progress')) ?>
};
</script>

<?= $this->endSection() ?>
