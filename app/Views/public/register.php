<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

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

            <ol class="jh-auth-steps jh-auth-steps--three" aria-label="<?= esc(lang('Site.register_h1')) ?>">
                <li class="is-active"><?= esc(lang('Site.register_step_profile')) ?></li>
                <li><?= esc(lang('Site.register_step_secure')) ?></li>
                <li><?= esc(lang('Site.register_step_verify')) ?></li>
            </ol>

            <div class="jh-auth-info" role="status">
                <?= esc(lang('Site.register_info')) ?>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="jh-auth-success" role="status"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="jh-auth-alert" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>

            <?php if (isset($validation) && $validation->getErrors()): ?>
                <div class="jh-auth-alert" role="alert">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($validation->getErrors() as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="jh-auth-form" method="post" action="<?= site_url('register') ?>" novalidate>
                <?= csrf_field() ?>

                <fieldset class="jh-auth-fieldset">
                    <legend><?= esc(lang('Site.register_sec_identity')) ?></legend>
                    <div class="jh-auth-grid">
                        <div class="jh-field">
                            <label class="form-label" for="first_name"><?= esc(lang('Site.label_first_name')) ?></label>
                            <input class="form-control" type="text" id="first_name" name="first_name" value="<?= esc(old('first_name')) ?>" required autocomplete="given-name">
                        </div>
                        <div class="jh-field">
                            <label class="form-label" for="last_name"><?= esc(lang('Site.label_last_name')) ?></label>
                            <input class="form-control" type="text" id="last_name" name="last_name" value="<?= esc(old('last_name')) ?>" required autocomplete="family-name">
                        </div>
                        <div class="jh-field">
                            <label class="form-label" for="national_id"><?= esc(lang('Site.label_national_id')) ?></label>
                            <input class="form-control" type="text" id="national_id" name="national_id" value="<?= esc(old('national_id')) ?>" required autocomplete="off">
                        </div>
                        <div class="jh-field">
                            <label class="form-label" for="phone"><?= esc(lang('Site.label_phone')) ?></label>
                            <input class="form-control" type="tel" id="phone" name="phone" value="<?= esc(old('phone')) ?>" required autocomplete="tel">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="jh-auth-fieldset">
                    <legend><?= esc(lang('Site.register_sec_contact')) ?></legend>
                    <div class="jh-auth-grid">
                        <div class="jh-field jh-field--full">
                            <label class="form-label" for="email"><?= esc(lang('Site.label_email')) ?></label>
                            <input class="form-control" type="email" id="email" name="email" value="<?= esc(old('email')) ?>" required autocomplete="email">
                        </div>
                        <div class="jh-field">
                            <label class="form-label" for="province"><?= esc(lang('Site.label_province')) ?></label>
                            <select class="form-select" id="province" name="province" required>
                                <option value=""><?= esc(lang('Site.label_select_prov')) ?></option>
                                <?php
                                $provinces = [
                                    'Bubanza', 'Bujumbura Mairie', 'Bujumbura Rural', 'Bururi', 'Cankuzo',
                                    'Cibitoke', 'Gitega', 'Karuzi', 'Kayanza', 'Kirundo', 'Makamba',
                                    'Muramvya', 'Muyinga', 'Mwaro', 'Ngozi', 'Rumonge', 'Rutana', 'Ruyigi',
                                ];
                                $selectedProvince = old('province');
                                foreach ($provinces as $province):
                                ?>
                                    <option value="<?= esc($province) ?>" <?= $selectedProvince === $province ? 'selected' : '' ?>><?= esc($province) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="jh-field">
                            <label class="form-label" for="commune"><?= esc(lang('Site.label_commune')) ?></label>
                            <input class="form-control" type="text" id="commune" name="commune" value="<?= esc(old('commune')) ?>" required>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="jh-auth-fieldset">
                    <legend><?= esc(lang('Site.register_sec_security')) ?></legend>
                    <div class="jh-auth-grid">
                        <div class="jh-field">
                            <label class="form-label" for="password"><?= esc(lang('Site.label_password')) ?></label>
                            <div class="jh-password">
                                <input class="form-control" type="password" id="password" name="password" required autocomplete="new-password" minlength="8">
                                <button
                                    class="jh-password-toggle"
                                    type="button"
                                    data-password-toggle
                                    data-target="password"
                                    data-show-label="<?= esc(lang('Site.login_show_password'), 'attr') ?>"
                                    data-hide-label="<?= esc(lang('Site.login_hide_password'), 'attr') ?>"
                                    aria-label="<?= esc(lang('Site.login_show_password')) ?>"
                                >
                                    <?= esc(lang('Site.login_show_password')) ?>
                                </button>
                            </div>
                        </div>
                        <div class="jh-field">
                            <label class="form-label" for="password_confirm"><?= esc(lang('Site.label_password2')) ?></label>
                            <div class="jh-password">
                                <input class="form-control" type="password" id="password_confirm" name="password_confirm" required autocomplete="new-password" minlength="8">
                                <button
                                    class="jh-password-toggle"
                                    type="button"
                                    data-password-toggle
                                    data-target="password_confirm"
                                    data-show-label="<?= esc(lang('Site.login_show_password'), 'attr') ?>"
                                    data-hide-label="<?= esc(lang('Site.login_hide_password'), 'attr') ?>"
                                    aria-label="<?= esc(lang('Site.login_show_password')) ?>"
                                >
                                    <?= esc(lang('Site.login_show_password')) ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="jh-auth-consent form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="consent" name="consent" required <?= old('consent') ? 'checked' : '' ?>>
                    <label class="form-check-label" for="consent">
                        <?= esc(lang('Site.label_consent')) ?>
                    </label>
                </div>

                <div class="jh-auth-actions">
                    <button class="btn btn-jh-primary jh-auth-submit" type="submit"><?= esc(lang('Site.register_submit')) ?></button>
                    <a class="btn btn-jh-secondary" href="<?= site_url('login') ?>"><?= esc(lang('Site.register_have_acct')) ?></a>
                </div>
            </form>

            <p class="jh-auth-secure"><?= esc(lang('Site.register_secure_badge')) ?></p>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
