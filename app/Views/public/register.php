<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<?php
$genders   = $genders ?? [];
$provinces = $provinces ?? [];
$communes  = $communes ?? [];
$zones     = $zones ?? [];
$collines  = $collines ?? [];
$selectPh  = '—';
$hasProvince = (string) old('birth_province') !== '';
$hasCommune  = (string) old('birth_commune') !== '';
$hasZone     = (string) old('birth_zone') !== '';
$maxDob      = $maxDob ?? date('Y-m-d', strtotime('-16 years'));
$cniMaxMb    = $cniMaxMb ?? 2;
?>

<section class="jh-auth jh-auth--register" aria-label="<?= esc(lang('Site.register_h1')) ?>">
    <aside class="jh-auth-visual">
        <img
            src="<?= public_asset('assets/img/hero.jpg') ?>"
            alt=""
            width="1600"
            height="1200"
            aria-hidden="true"
        >
        <div class="jh-auth-visual-shade"></div>
        <div class="jh-auth-visual-copy">
            <p class="jh-auth-brand">JusticeHeritage</p>
            <h2><?= esc(lang('Site.register_visual_title')) ?></h2>
            <p><?= esc(lang('Site.register_visual_text')) ?></p>
            <ul class="jh-auth-trust">
                <li><?= esc(lang('Site.register_benefit_1')) ?></li>
                <li><?= esc(lang('Site.register_benefit_2')) ?></li>
                <li><?= esc(lang('Site.register_benefit_3')) ?></li>
            </ul>
        </div>
    </aside>

    <div class="jh-auth-panel">
        <div class="jh-auth-panel-inner jh-auth-panel-inner--wide">
            <p class="jh-auth-welcome"><?= esc(lang('Site.register_welcome')) ?></p>
            <h1><?= esc(lang('Site.register_panel_title')) ?></h1>
            <p class="jh-auth-lead"><?= esc(lang('Site.register_panel_lead')) ?></p>

            <ol class="jh-reg-progress" aria-label="<?= esc(lang('Site.register_progress_label')) ?>" data-reg-progress>
                <li class="is-active" data-reg-nav="1"><span>1</span><small><?= esc(lang('Site.register_step1_short')) ?></small></li>
                <li data-reg-nav="2"><span>2</span><small><?= esc(lang('Site.register_step2_short')) ?></small></li>
                <li data-reg-nav="3"><span>3</span><small><?= esc(lang('Site.register_step3_short')) ?></small></li>
                <li data-reg-nav="4"><span>4</span><small><?= esc(lang('Site.register_step4_short')) ?></small></li>
                <li data-reg-nav="5"><span>5</span><small><?= esc(lang('Site.register_step5_short')) ?></small></li>
            </ol>

            <div class="jh-auth-info" role="status"><?= esc(lang('Site.register_info')) ?></div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="jh-auth-alert" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>

            <?php
            $formErrors = [];
            if (isset($validation) && $validation->getErrors()) {
                $formErrors = $validation->getErrors();
            } elseif (session()->getFlashdata('errors')) {
                $formErrors = (array) session()->getFlashdata('errors');
            }
            ?>
            <?php if ($formErrors): ?>
                <div class="jh-auth-alert" role="alert">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($formErrors as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form
                class="jh-auth-form jh-reg-wizard"
                method="post"
                action="<?= site_url('register') ?>"
                enctype="multipart/form-data"
                novalidate
                data-register-wizard
                data-api-sexes="<?= esc(site_url('api/sexes'), 'attr') ?>"
                data-api-provinces="<?= esc(site_url('api/provinces'), 'attr') ?>"
                data-api-communes="<?= esc(site_url('api/communes'), 'attr') ?>"
                data-api-zones="<?= esc(site_url('api/zones'), 'attr') ?>"
                data-api-collines="<?= esc(site_url('api/collines'), 'attr') ?>"
                data-ph-commune="<?= esc(lang('Site.ph_select_province_first'), 'attr') ?>"
                data-ph-zone="<?= esc(lang('Site.ph_select_commune_first'), 'attr') ?>"
                data-ph-colline="<?= esc(lang('Site.ph_select_zone_first'), 'attr') ?>"
                data-msg-required="<?= esc(lang('Site.err_required'), 'attr') ?>"
                data-msg-email="<?= esc(lang('Site.err_email'), 'attr') ?>"
                data-msg-password="<?= esc(lang('Site.err_password_min'), 'attr') ?>"
                data-msg-password-match="<?= esc(lang('Site.err_password_match'), 'attr') ?>"
                data-msg-step="<?= esc(lang('Site.err_step_incomplete'), 'attr') ?>"
                data-msg-load="<?= esc(lang('Site.err_location_load'), 'attr') ?>"
                data-msg-min-age="<?= esc(lang('Site.err_min_age'), 'attr') ?>"
                data-msg-cni-type="<?= esc(lang('Site.err_cni_type'), 'attr') ?>"
                data-msg-cni-size="<?= esc(lang('Site.err_cni_size'), 'attr') ?>"
                data-min-age="16"
                data-cni-max-bytes="<?= esc((string) (($cniMaxMb ?? 2) * 1024 * 1024), 'attr') ?>"
            >
                <?= csrf_field() ?>

                <!-- Step 1 -->
                <fieldset class="jh-reg-step is-active" data-reg-step="1">
                    <legend><?= esc(lang('Site.register_step1_title')) ?></legend>
                    <p class="jh-reg-step-lead"><?= esc(lang('Site.register_step1_lead')) ?></p>
                    <div class="jh-auth-grid">
                        <div class="jh-field">
                            <label class="form-label" for="first_name">
                                <?= esc(lang('Site.label_first_name')) ?> <span class="jh-req" aria-hidden="true">*</span>
                            </label>
                            <input class="form-control" type="text" id="first_name" name="first_name"
                                   value="<?= esc(old('first_name')) ?>" required autocomplete="given-name"
                                   data-error-empty="<?= esc(lang('Site.err_first_name'), 'attr') ?>">
                            <div class="jh-field-error" data-field-error hidden></div>
                        </div>
                        <div class="jh-field">
                            <label class="form-label" for="last_name">
                                <?= esc(lang('Site.label_last_name')) ?> <span class="jh-req" aria-hidden="true">*</span>
                            </label>
                            <input class="form-control" type="text" id="last_name" name="last_name"
                                   value="<?= esc(old('last_name')) ?>" required autocomplete="family-name"
                                   data-error-empty="<?= esc(lang('Site.err_last_name'), 'attr') ?>">
                            <div class="jh-field-error" data-field-error hidden></div>
                        </div>
                        <div class="jh-field">
                            <label class="form-label" for="date_of_birth">
                                <?= esc(lang('Site.label_dob')) ?> <span class="jh-req" aria-hidden="true">*</span>
                            </label>
                            <input class="form-control" type="date" id="date_of_birth" name="date_of_birth"
                                   value="<?= esc(old('date_of_birth')) ?>" required
                                   max="<?= esc($maxDob, 'attr') ?>"
                                   data-error-empty="<?= esc(lang('Site.err_dob'), 'attr') ?>"
                                   data-error-min-age="<?= esc(lang('Site.err_min_age'), 'attr') ?>">
                            <div class="jh-field-error" data-field-error hidden></div>
                        </div>
                        <div class="jh-field">
                            <label class="form-label" for="gender">
                                <?= esc(lang('Site.label_gender')) ?> <span class="jh-req" aria-hidden="true">*</span>
                            </label>
                            <select class="form-select" id="gender" name="gender" required
                                    data-error-empty="<?= esc(lang('Site.err_gender'), 'attr') ?>">
                                <option value=""><?= esc($selectPh) ?></option>
                                <?php foreach ($genders as $gender): ?>
                                    <option value="<?= esc((string) $gender['id']) ?>" <?= (string) old('gender') === (string) $gender['id'] ? 'selected' : '' ?>>
                                        <?= esc($gender['label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="jh-field-error" data-field-error hidden></div>
                        </div>
                        <div class="jh-field">
                            <label class="form-label" for="national_id">
                                <?= esc(lang('Site.label_national_id')) ?> <span class="jh-req" aria-hidden="true">*</span>
                            </label>
                            <input class="form-control" type="text" id="national_id" name="national_id"
                                   value="<?= esc(old('national_id')) ?>" required autocomplete="off"
                                   data-error-empty="<?= esc(lang('Site.err_national_id'), 'attr') ?>">
                            <div class="jh-field-error" data-field-error hidden></div>
                        </div>
                        <div class="jh-field jh-field--full">
                            <label class="form-label" for="national_id_file">
                                <?= esc(lang('Site.label_national_id_file')) ?> <span class="jh-req" aria-hidden="true">*</span>
                            </label>
                            <input class="form-control" type="file" id="national_id_file" name="national_id_file" required
                                   accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                   data-error-empty="<?= esc(lang('Site.err_cni_required'), 'attr') ?>"
                                   data-error-type="<?= esc(lang('Site.err_cni_type'), 'attr') ?>"
                                   data-error-size="<?= esc(lang('Site.err_cni_size'), 'attr') ?>">
                            <small class="jh-field-hint"><?= esc(lang('Site.hint_national_id_file', [(string) $cniMaxMb])) ?></small>
                            <div class="jh-field-error" data-field-error hidden></div>
                        </div>
                    </div>
                </fieldset>

                <!-- Step 2 -->
                <fieldset class="jh-reg-step" data-reg-step="2" hidden>
                    <legend><?= esc(lang('Site.register_step2_title')) ?></legend>
                    <p class="jh-reg-step-lead"><?= esc(lang('Site.register_step2_lead')) ?></p>
                    <div class="jh-auth-grid">
                        <div class="jh-field">
                            <label class="form-label" for="phone">
                                <?= esc(lang('Site.label_phone')) ?> <span class="jh-req" aria-hidden="true">*</span>
                            </label>
                            <input class="form-control" type="tel" id="phone" name="phone"
                                   value="<?= esc(old('phone')) ?>" required autocomplete="tel"
                                   data-error-empty="<?= esc(lang('Site.err_phone'), 'attr') ?>">
                            <div class="jh-field-error" data-field-error hidden></div>
                        </div>
                        <div class="jh-field">
                            <label class="form-label" for="email">
                                <?= esc(lang('Site.label_email')) ?> <span class="jh-req" aria-hidden="true">*</span>
                            </label>
                            <input class="form-control" type="email" id="email" name="email"
                                   value="<?= esc(old('email')) ?>" required autocomplete="email"
                                   data-error-empty="<?= esc(lang('Site.err_email_empty'), 'attr') ?>"
                                   data-error-invalid="<?= esc(lang('Site.err_email'), 'attr') ?>">
                            <div class="jh-field-error" data-field-error hidden></div>
                        </div>
                        <div class="jh-field jh-field--full">
                            <label class="form-label" for="address">
                                <?= esc(lang('Site.label_address')) ?> <span class="jh-req" aria-hidden="true">*</span>
                            </label>
                            <textarea class="form-control" id="address" name="address" rows="3" required
                                      data-error-empty="<?= esc(lang('Site.err_address'), 'attr') ?>"><?= esc(old('address')) ?></textarea>
                            <div class="jh-field-error" data-field-error hidden></div>
                        </div>
                    </div>
                </fieldset>

                <!-- Step 3 -->
                <fieldset class="jh-reg-step" data-reg-step="3" hidden>
                    <legend><?= esc(lang('Site.register_step3_title')) ?></legend>
                    <p class="jh-reg-step-lead"><?= esc(lang('Site.register_step3_lead')) ?></p>
                    <div class="jh-auth-grid">
                        <div class="jh-field">
                            <label class="form-label" for="birth_province">
                                <?= esc(lang('Site.label_birth_province')) ?> <span class="jh-req" aria-hidden="true">*</span>
                            </label>
                            <select class="form-select" id="birth_province" name="birth_province" required
                                    data-loc="province"
                                    data-error-empty="<?= esc(lang('Site.err_birth_province'), 'attr') ?>">
                                <option value=""><?= esc($selectPh) ?></option>
                                <?php foreach ($provinces as $province): ?>
                                    <option value="<?= esc((string) $province['id']) ?>" <?= (string) old('birth_province') === (string) $province['id'] ? 'selected' : '' ?>>
                                        <?= esc($province['label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="jh-field-error" data-field-error hidden></div>
                        </div>
                        <div class="jh-field">
                            <label class="form-label" for="birth_commune">
                                <?= esc(lang('Site.label_birth_commune')) ?> <span class="jh-req" aria-hidden="true">*</span>
                            </label>
                            <select class="form-select" id="birth_commune" name="birth_commune" required
                                    <?= $hasProvince ? '' : 'disabled' ?>
                                    data-loc="commune"
                                    data-error-empty="<?= esc(lang('Site.err_birth_commune'), 'attr') ?>">
                                <option value=""><?= esc($hasProvince ? $selectPh : lang('Site.ph_select_province_first')) ?></option>
                                <?php foreach ($communes as $commune): ?>
                                    <option value="<?= esc((string) $commune['id']) ?>" <?= (string) old('birth_commune') === (string) $commune['id'] ? 'selected' : '' ?>>
                                        <?= esc($commune['label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="jh-field-error" data-field-error hidden></div>
                        </div>
                        <div class="jh-field">
                            <label class="form-label" for="birth_zone">
                                <?= esc(lang('Site.label_birth_zone')) ?> <span class="jh-req" aria-hidden="true">*</span>
                            </label>
                            <select class="form-select" id="birth_zone" name="birth_zone" required
                                    <?= $hasCommune ? '' : 'disabled' ?>
                                    data-loc="zone"
                                    data-error-empty="<?= esc(lang('Site.err_birth_zone'), 'attr') ?>">
                                <option value=""><?= esc($hasCommune ? $selectPh : lang('Site.ph_select_commune_first')) ?></option>
                                <?php foreach ($zones as $zone): ?>
                                    <option value="<?= esc((string) $zone['id']) ?>" <?= (string) old('birth_zone') === (string) $zone['id'] ? 'selected' : '' ?>>
                                        <?= esc($zone['label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="jh-field-error" data-field-error hidden></div>
                        </div>
                        <div class="jh-field">
                            <label class="form-label" for="birth_colline">
                                <?= esc(lang('Site.label_birth_colline')) ?> <span class="jh-req" aria-hidden="true">*</span>
                            </label>
                            <select class="form-select" id="birth_colline" name="birth_colline" required
                                    <?= $hasZone ? '' : 'disabled' ?>
                                    data-loc="colline"
                                    data-error-empty="<?= esc(lang('Site.err_birth_colline'), 'attr') ?>">
                                <option value=""><?= esc($hasZone ? $selectPh : lang('Site.ph_select_zone_first')) ?></option>
                                <?php foreach ($collines as $colline): ?>
                                    <option value="<?= esc((string) $colline['id']) ?>" <?= (string) old('birth_colline') === (string) $colline['id'] ? 'selected' : '' ?>>
                                        <?= esc($colline['label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="jh-field-error" data-field-error hidden></div>
                        </div>
                    </div>
                </fieldset>

                <!-- Step 4 -->
                <fieldset class="jh-reg-step" data-reg-step="4" hidden>
                    <legend><?= esc(lang('Site.register_step4_title')) ?></legend>
                    <p class="jh-reg-step-lead"><?= esc(lang('Site.register_step4_lead')) ?></p>
                    <div class="jh-auth-grid">
                        <div class="jh-field jh-field--full">
                            <label class="form-label" for="username">
                                <?= esc(lang('Site.label_username')) ?> <span class="jh-req" aria-hidden="true">*</span>
                            </label>
                            <input class="form-control" type="text" id="username" name="username"
                                   value="<?= esc(old('username')) ?>" required autocomplete="username"
                                   data-error-empty="<?= esc(lang('Site.err_username'), 'attr') ?>">
                            <div class="jh-field-error" data-field-error hidden></div>
                        </div>
                        <div class="jh-field">
                            <label class="form-label" for="password">
                                <?= esc(lang('Site.label_password')) ?> <span class="jh-req" aria-hidden="true">*</span>
                            </label>
                            <div class="jh-password">
                                <input class="form-control" type="password" id="password" name="password"
                                       required autocomplete="new-password" minlength="8"
                                       data-error-empty="<?= esc(lang('Site.err_password_empty'), 'attr') ?>"
                                       data-error-invalid="<?= esc(lang('Site.err_password_min'), 'attr') ?>">
                                <button class="jh-password-toggle" type="button" data-password-toggle data-target="password"
                                        data-show-label="<?= esc(lang('Site.login_show_password'), 'attr') ?>"
                                        data-hide-label="<?= esc(lang('Site.login_hide_password'), 'attr') ?>"
                                        aria-label="<?= esc(lang('Site.login_show_password')) ?>">
                                    <?= esc(lang('Site.login_show_password')) ?>
                                </button>
                            </div>
                            <div class="jh-field-error" data-field-error hidden></div>
                        </div>
                        <div class="jh-field">
                            <label class="form-label" for="password_confirm">
                                <?= esc(lang('Site.label_password2')) ?> <span class="jh-req" aria-hidden="true">*</span>
                            </label>
                            <div class="jh-password">
                                <input class="form-control" type="password" id="password_confirm" name="password_confirm"
                                       required autocomplete="new-password" minlength="8"
                                       data-error-empty="<?= esc(lang('Site.err_password2_empty'), 'attr') ?>"
                                       data-error-match="<?= esc(lang('Site.err_password_match'), 'attr') ?>">
                                <button class="jh-password-toggle" type="button" data-password-toggle data-target="password_confirm"
                                        data-show-label="<?= esc(lang('Site.login_show_password'), 'attr') ?>"
                                        data-hide-label="<?= esc(lang('Site.login_hide_password'), 'attr') ?>"
                                        aria-label="<?= esc(lang('Site.login_show_password')) ?>">
                                    <?= esc(lang('Site.login_show_password')) ?>
                                </button>
                            </div>
                            <div class="jh-field-error" data-field-error hidden></div>
                        </div>
                    </div>
                </fieldset>

                <!-- Step 5 -->
                <fieldset class="jh-reg-step" data-reg-step="5" hidden>
                    <legend><?= esc(lang('Site.register_step5_title')) ?></legend>
                    <p class="jh-reg-step-lead"><?= esc(lang('Site.register_step5_lead')) ?></p>

                    <div class="jh-reg-review" data-reg-review>
                        <article class="jh-reg-review-card">
                            <header>
                                <h3><?= esc(lang('Site.register_step1_title')) ?></h3>
                                <button type="button" class="btn btn-link btn-sm" data-reg-edit="1"><?= esc(lang('Site.register_edit')) ?></button>
                            </header>
                            <dl>
                                <div><dt><?= esc(lang('Site.label_first_name')) ?></dt><dd data-review="first_name">—</dd></div>
                                <div><dt><?= esc(lang('Site.label_last_name')) ?></dt><dd data-review="last_name">—</dd></div>
                                <div><dt><?= esc(lang('Site.label_dob')) ?></dt><dd data-review="date_of_birth">—</dd></div>
                                <div><dt><?= esc(lang('Site.label_gender')) ?></dt><dd data-review="gender_label">—</dd></div>
                                <div><dt><?= esc(lang('Site.label_national_id')) ?></dt><dd data-review="national_id">—</dd></div>
                                <div><dt><?= esc(lang('Site.label_national_id_file')) ?></dt><dd data-review="national_id_file">—</dd></div>
                            </dl>
                        </article>

                        <article class="jh-reg-review-card">
                            <header>
                                <h3><?= esc(lang('Site.register_step2_title')) ?></h3>
                                <button type="button" class="btn btn-link btn-sm" data-reg-edit="2"><?= esc(lang('Site.register_edit')) ?></button>
                            </header>
                            <dl>
                                <div><dt><?= esc(lang('Site.label_phone')) ?></dt><dd data-review="phone">—</dd></div>
                                <div><dt><?= esc(lang('Site.label_email')) ?></dt><dd data-review="email">—</dd></div>
                                <div class="jh-reg-review-full"><dt><?= esc(lang('Site.label_address')) ?></dt><dd data-review="address">—</dd></div>
                            </dl>
                        </article>

                        <article class="jh-reg-review-card">
                            <header>
                                <h3><?= esc(lang('Site.register_step3_title')) ?></h3>
                                <button type="button" class="btn btn-link btn-sm" data-reg-edit="3"><?= esc(lang('Site.register_edit')) ?></button>
                            </header>
                            <dl>
                                <div><dt><?= esc(lang('Site.label_birth_province')) ?></dt><dd data-review="birth_province">—</dd></div>
                                <div><dt><?= esc(lang('Site.label_birth_commune')) ?></dt><dd data-review="birth_commune">—</dd></div>
                                <div><dt><?= esc(lang('Site.label_birth_zone')) ?></dt><dd data-review="birth_zone">—</dd></div>
                                <div><dt><?= esc(lang('Site.label_birth_colline')) ?></dt><dd data-review="birth_colline">—</dd></div>
                            </dl>
                        </article>

                        <article class="jh-reg-review-card">
                            <header>
                                <h3><?= esc(lang('Site.register_step4_title')) ?></h3>
                                <button type="button" class="btn btn-link btn-sm" data-reg-edit="4"><?= esc(lang('Site.register_edit')) ?></button>
                            </header>
                            <dl>
                                <div><dt><?= esc(lang('Site.label_username')) ?></dt><dd data-review="username">—</dd></div>
                                <div><dt><?= esc(lang('Site.label_password')) ?></dt><dd>••••••••</dd></div>
                            </dl>
                        </article>
                    </div>

                    <div class="jh-auth-consent form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="consent" name="consent" required
                               <?= old('consent') ? 'checked' : '' ?>
                               data-error-empty="<?= esc(lang('Site.err_consent'), 'attr') ?>">
                        <label class="form-check-label" for="consent">
                            <?= esc(lang('Site.label_consent')) ?> <span class="jh-req" aria-hidden="true">*</span>
                        </label>
                        <div class="jh-field-error" data-field-error hidden></div>
                    </div>
                </fieldset>

                <p class="jh-wizard-error" data-reg-error hidden></p>

                <div class="jh-reg-actions">
                    <button class="btn btn-jh-secondary" type="button" data-reg-prev hidden>
                        <?= esc(lang('Site.register_prev')) ?>
                    </button>
                    <div class="jh-reg-actions-main">
                        <a class="btn btn-jh-secondary" href="<?= site_url('login') ?>" data-reg-signin>
                            <?= esc(lang('Site.register_have_acct')) ?>
                        </a>
                        <button class="btn btn-jh-primary" type="button" data-reg-next>
                            <?= esc(lang('Site.register_next')) ?>
                        </button>
                        <button class="btn btn-jh-primary" type="submit" data-reg-submit hidden disabled>
                            <?= esc(lang('Site.register_submit')) ?>
                        </button>
                    </div>
                </div>
            </form>

            <p class="jh-auth-secure"><?= esc(lang('Site.register_secure_badge')) ?></p>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
