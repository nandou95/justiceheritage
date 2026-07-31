<?= $this->extend('layouts/portal') ?>

<?= $this->section('content') ?>

<?php
$locations = $locations ?? [];
$courts = $courts ?? [];
$docTypes = $docTypes ?? [];
$selectPlaceholder = '—';
?>

<section class="jh-new-hero">
    <div class="jh-new-hero-main">
        <p class="jh-new-kicker"><?= esc(lang('Portal.new_kicker')) ?></p>
        <h1><?= esc(lang('Portal.new_h1')) ?></h1>
        <p><?= esc(lang('Portal.new_lead')) ?></p>
    </div>
    <ol class="jh-new-steps" aria-label="<?= esc(lang('Portal.new_steps_label')) ?>" data-wizard-steps>
        <li class="is-active" data-step-nav="1"><span>1</span><?= esc(lang('Portal.new_sec_parties')) ?></li>
        <li data-step-nav="2"><span>2</span><?= esc(lang('Portal.new_sec_details')) ?></li>
        <li data-step-nav="3"><span>3</span><?= esc(lang('Portal.new_sec_evidence')) ?></li>
    </ol>
</section>

<form class="jh-new-layout jh-portal-form"
      method="post"
      action="<?= site_url('portal/complaints/new') ?>"
      enctype="multipart/form-data"
      data-new-complaint
      data-locations="<?= esc(json_encode($locations, JSON_UNESCAPED_UNICODE), 'attr') ?>"
      data-step-error="<?= esc(lang('Portal.new_step_error'), 'attr') ?>"
      data-doc-label="<?= esc(lang('Portal.new_doc_entry'), 'attr') ?>"
      novalidate>
    <?= csrf_field() ?>

    <div class="jh-new-main">
        <article class="jh-new-card is-active" data-step-panel="1">
            <header class="jh-new-card-head">
                <span class="jh-new-step-badge" aria-hidden="true">1</span>
                <div>
                    <h2><?= esc(lang('Portal.new_sec_parties')) ?></h2>
                    <p><?= esc(lang('Portal.new_sec_parties_lead')) ?></p>
                </div>
            </header>

            <div class="jh-new-grid">
                <div class="jh-field jh-field--full">
                    <label class="form-label" for="parcel"><?= esc(lang('Portal.new_parcel')) ?></label>
                    <textarea class="form-control" id="parcel" name="parcel" rows="3" required
                              placeholder="<?= esc(lang('Portal.new_parcel_ph'), 'attr') ?>"></textarea>
                    <small class="jh-field-hint"><?= esc(lang('Portal.new_parcel_hint')) ?></small>
                </div>

                <div class="jh-field">
                    <label class="form-label" for="province"><?= esc(lang('Portal.new_province')) ?></label>
                    <select class="form-select" id="province" name="province" required data-loc="province">
                        <option value=""><?= esc($selectPlaceholder) ?></option>
                        <?php foreach (array_keys($locations) as $province): ?>
                            <option value="<?= esc($province) ?>"><?= esc($province) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="jh-field">
                    <label class="form-label" for="commune"><?= esc(lang('Portal.new_commune')) ?></label>
                    <select class="form-select" id="commune" name="commune" required data-loc="commune" disabled>
                        <option value=""><?= esc($selectPlaceholder) ?></option>
                    </select>
                </div>

                <div class="jh-field">
                    <label class="form-label" for="zone"><?= esc(lang('Portal.new_zone')) ?></label>
                    <select class="form-select" id="zone" name="zone" required data-loc="zone" disabled>
                        <option value=""><?= esc($selectPlaceholder) ?></option>
                    </select>
                </div>

                <div class="jh-field">
                    <label class="form-label" for="colline"><?= esc(lang('Portal.new_colline')) ?></label>
                    <select class="form-select" id="colline" name="colline" required data-loc="colline" disabled>
                        <option value=""><?= esc($selectPlaceholder) ?></option>
                    </select>
                </div>

                <div class="jh-field jh-field--full">
                    <label class="form-label" for="court"><?= esc(lang('Portal.new_court')) ?></label>
                    <select class="form-select" id="court" name="court" required>
                        <option value=""><?= esc($selectPlaceholder) ?></option>
                        <?php foreach ($courts as $value => $label): ?>
                            <option value="<?= esc($value) ?>"><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="jh-field-hint"><?= esc(lang('Portal.new_court_hint')) ?></small>
                </div>
            </div>
        </article>

        <article class="jh-new-card" data-step-panel="2" hidden>
            <header class="jh-new-card-head">
                <span class="jh-new-step-badge" aria-hidden="true">2</span>
                <div>
                    <h2><?= esc(lang('Portal.new_sec_details')) ?></h2>
                    <p><?= esc(lang('Portal.new_sec_details_lead')) ?></p>
                </div>
            </header>

            <div class="jh-new-grid">
                <div class="jh-field jh-field--full">
                    <label class="form-label" for="summary"><?= esc(lang('Portal.new_summary')) ?></label>
                    <textarea class="form-control" id="summary" name="summary" rows="5" required
                              placeholder="<?= esc(lang('Portal.new_summary_ph'), 'attr') ?>"></textarea>
                </div>
                <div class="jh-field jh-field--full">
                    <label class="form-label" for="relief"><?= esc(lang('Portal.new_relief')) ?></label>
                    <textarea class="form-control" id="relief" name="relief" rows="3" required
                              placeholder="<?= esc(lang('Portal.new_relief_ph'), 'attr') ?>"></textarea>
                </div>
            </div>
        </article>

        <article class="jh-new-card" data-step-panel="3" hidden>
            <header class="jh-new-card-head">
                <span class="jh-new-step-badge" aria-hidden="true">3</span>
                <div>
                    <h2><?= esc(lang('Portal.new_sec_evidence')) ?></h2>
                    <p><?= esc(lang('Portal.new_sec_evidence_lead')) ?></p>
                </div>
            </header>

            <div class="jh-doc-rows" data-doc-rows></div>

            <button class="btn btn-jh-secondary btn-sm jh-doc-add" type="button" data-doc-add>
                + <?= esc(lang('Portal.new_doc_add')) ?>
            </button>
            <small class="jh-field-hint d-block mt-2"><?= esc(lang('Portal.new_upload_hint')) ?></small>

            <template data-doc-row-template>
                <div class="jh-doc-row" data-doc-row>
                    <div class="jh-doc-row-head">
                        <strong data-doc-label><?= esc(lang('Portal.new_doc_entry')) ?></strong>
                        <button class="jh-doc-remove" type="button" data-doc-remove aria-label="<?= esc(lang('Portal.new_doc_remove'), 'attr') ?>">
                            <?= esc(lang('Portal.new_doc_remove')) ?>
                        </button>
                    </div>
                    <div class="jh-new-grid">
                        <div class="jh-field">
                            <label class="form-label"><?= esc(lang('Portal.new_doc_type')) ?></label>
                            <select class="form-select" name="doc_type[]" required data-doc-type>
                                <option value=""><?= esc($selectPlaceholder) ?></option>
                                <?php foreach ($docTypes as $value => $label): ?>
                                    <option value="<?= esc($value) ?>"><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="jh-field">
                            <label class="form-label"><?= esc(lang('Portal.new_doc_file')) ?></label>
                            <input class="form-control" type="file" name="doc_file[]" accept=".pdf,image/*" required data-doc-file>
                        </div>
                    </div>
                </div>
            </template>
        </article>

        <div class="jh-new-actions">
            <button class="btn btn-jh-secondary" type="button" data-wizard-prev hidden>
                <?= esc(lang('Portal.new_prev')) ?>
            </button>
            <a class="btn btn-jh-secondary" href="<?= site_url('portal/complaints') ?>" data-wizard-cancel>
                <?= esc(lang('Portal.new_cancel')) ?>
            </a>
            <div class="jh-new-actions-main">
                <button class="btn btn-jh-secondary" type="button"><?= esc(lang('Portal.new_save')) ?></button>
                <button class="btn btn-jh-primary" type="button" data-wizard-next>
                    <?= esc(lang('Portal.new_next')) ?>
                </button>
                <button class="btn btn-jh-primary" type="submit" data-wizard-submit hidden>
                    <?= esc(lang('Portal.new_submit')) ?>
                </button>
            </div>
        </div>
        <p class="jh-wizard-error" data-wizard-error hidden></p>
    </div>

    <aside class="jh-new-aside">
        <article class="jh-new-side-card">
            <h2><?= esc(lang('Portal.new_need_title')) ?></h2>
            <ul class="jh-new-checklist">
                <li><?= esc(lang('Portal.new_need_1')) ?></li>
                <li><?= esc(lang('Portal.new_need_2')) ?></li>
                <li><?= esc(lang('Portal.new_need_3')) ?></li>
                <li><?= esc(lang('Portal.new_need_4')) ?></li>
            </ul>
        </article>

        <article class="jh-new-side-card jh-new-side-card--soft">
            <h2><?= esc(lang('Portal.new_process_title')) ?></h2>
            <p><?= esc(lang('Portal.new_process_body')) ?></p>
        </article>

        <p class="jh-new-demo" role="status"><?= esc(lang('Portal.new_demo')) ?></p>
    </aside>
</form>

<?= $this->endSection() ?>
