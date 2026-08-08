<?= $this->extend('layouts/portal') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<?php
$record      = is_array($record ?? null) ? $record : [];
$parcels     = is_array($parcels ?? null) && $parcels !== [] ? $parcels : [[
    'localisation_parcelle'     => '',
    'superficie_maitre_carreau' => '',
    'province_parcelle_id'      => '',
    'commune_parcelle_id'       => '',
    'zone_parcelle_id'          => '',
    'colline_parcelle_id'       => '',
]];
$levels        = $levels ?? [];
$provinces     = $provinces ?? [];
$communes      = $communes ?? [];
$jurisdictions = $jurisdictions ?? [];
$people        = $people ?? [];
$docTypes      = $docTypes ?? [];
$hasWitness    = ! empty($hasWitness);
$isEdit        = false;
$totalSteps    = 5;

$val = static function (array $record, string $key) {
    $old = old($key);
    if ($old !== null && $old !== '') {
        return $old;
    }

    return $record[$key] ?? '';
};
$selected = static function (array $record, string $key): array {
    $old = old($key);
    if (is_array($old)) {
        return array_map('strval', $old);
    }

    return array_map('strval', (array) ($record[$key] ?? []));
};
$complainants = $selected($record, 'complainant_ids');
$defendants   = $selected($record, 'defendant_ids');

$partySelect = static function (
    string $id,
    string $name,
    string $label,
    array $people,
    array $selectedIds,
    bool $required
): void {
    ?>
    <div class="bo-multi-select" data-bo-multi-select>
        <label class="form-label" for="<?= esc($id, 'attr') ?>"><?= esc($label) ?><?= $required ? ' *' : '' ?></label>
        <div class="bo-multi-select-box">
            <div class="bo-multi-select-chips" data-multi-chips></div>
            <input type="search" class="bo-multi-select-search" data-multi-search
                   placeholder="<?= esc(lang('Backoffice.cmp_party_search'), 'attr') ?>"
                   autocomplete="off" aria-controls="<?= esc($id, 'attr') ?>-list">
            <div class="bo-multi-select-dropdown" data-multi-dropdown id="<?= esc($id, 'attr') ?>-list" hidden role="listbox"></div>
        </div>
        <select class="bo-multi-select-native" id="<?= esc($id, 'attr') ?>" name="<?= esc($name, 'attr') ?>"
                multiple <?= $required ? 'required' : '' ?> data-multi-source
                data-msg-required="<?= esc(lang('Backoffice.validation_required'), 'attr') ?>">
            <?php foreach ($people as $opt): ?>
                <option value="<?= esc($opt['id']) ?>" <?= in_array((string) $opt['id'], $selectedIds, true) ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
            <?php endforeach; ?>
        </select>
        <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
        <p class="form-text mb-0"><?= esc(lang('Backoffice.cmp_party_hint')) ?></p>
    </div>
    <?php
};
?>

<section class="jh-new-hero">
    <div class="jh-new-hero-main">
        <p class="jh-new-kicker"><?= esc(lang('Portal.new_kicker')) ?></p>
        <h1><?= esc(lang('Portal.new_h1')) ?></h1>
        <p><?= esc(lang('Portal.new_lead')) ?></p>
    </div>
</section>

<section class="jh-portal-panel bo-crud-panel bo-panel">
    <form class="bo-form needs-validation" method="post" action="<?= esc(site_url('portal/complaints/new')) ?>" enctype="multipart/form-data" novalidate
          data-bo-cmp-form
          data-bo-cmp-wizard
          data-api-communes="<?= esc(site_url('api/communes'), 'attr') ?>"
          data-api-zones="<?= esc(site_url('api/zones'), 'attr') ?>"
          data-api-collines="<?= esc(site_url('api/collines'), 'attr') ?>"
          data-api-jurisdictions="<?= esc(site_url('portal/api/court-jurisdictions'), 'attr') ?>"
          data-api-doc-types="<?= esc(site_url('portal/api/complaint-document-types'), 'attr') ?>"
          data-msg-required="<?= esc(lang('Backoffice.validation_required'), 'attr') ?>"
          data-msg-parties="<?= esc(lang('Backoffice.cmp_err_complainants'), 'attr') ?>"
          data-msg-defendants="<?= esc(lang('Backoffice.cmp_err_defendants'), 'attr') ?>"
          data-msg-parcels="<?= esc(lang('Backoffice.cmp_err_parcels'), 'attr') ?>"
          data-doc-required-label="<?= esc(lang('Backoffice.cmp_doc_required_badge'), 'attr') ?>"
          data-doc-optional-label="<?= esc(lang('Backoffice.cmp_doc_optional_badge'), 'attr') ?>"
          data-doc-accept-hint="<?= esc(lang('Backoffice.cmp_doc_accept_hint'), 'attr') ?>"
          data-is-edit="0">
        <?= csrf_field() ?>

        <div class="bo-wizard" data-wizard>
            <div class="bo-wizard-progress" aria-live="polite">
                <div class="bo-wizard-steps" role="list">
                    <button type="button" class="bo-wizard-step is-active" data-wizard-indicator="1" role="listitem">
                        <span class="bo-wizard-step-index">1</span>
                        <span class="bo-wizard-step-label"><?= esc(lang('Portal.new_wizard_step1_short')) ?></span>
                    </button>
                    <div class="bo-wizard-connector" aria-hidden="true"></div>
                    <button type="button" class="bo-wizard-step" data-wizard-indicator="2" role="listitem">
                        <span class="bo-wizard-step-index">2</span>
                        <span class="bo-wizard-step-label"><?= esc(lang('Portal.new_wizard_step2_short')) ?></span>
                    </button>
                    <div class="bo-wizard-connector" aria-hidden="true"></div>
                    <button type="button" class="bo-wizard-step" data-wizard-indicator="3" role="listitem">
                        <span class="bo-wizard-step-index">3</span>
                        <span class="bo-wizard-step-label"><?= esc(lang('Portal.new_wizard_step3_short')) ?></span>
                    </button>
                    <div class="bo-wizard-connector" aria-hidden="true"></div>
                    <button type="button" class="bo-wizard-step" data-wizard-indicator="4" role="listitem">
                        <span class="bo-wizard-step-index">4</span>
                        <span class="bo-wizard-step-label"><?= esc(lang('Portal.new_wizard_step4_short')) ?></span>
                    </button>
                    <div class="bo-wizard-connector" aria-hidden="true"></div>
                    <button type="button" class="bo-wizard-step" data-wizard-indicator="5" role="listitem">
                        <span class="bo-wizard-step-index">5</span>
                        <span class="bo-wizard-step-label"><?= esc(lang('Portal.new_wizard_step5_short')) ?></span>
                    </button>
                </div>
                <p class="bo-wizard-status" data-wizard-status>
                    <?= esc(lang('Portal.new_wizard_progress', [1, $totalSteps])) ?>
                </p>
            </div>

            <div class="bo-wizard-pane is-active" data-wizard-step="1">
                <fieldset class="bo-form-section">
                    <legend class="bo-form-section-title"><?= esc(lang('Portal.new_wizard_step1_title')) ?></legend>
                    <p class="bo-wizard-step-lead"><?= esc(lang('Portal.new_wizard_step1_lead')) ?></p>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="objet"><?= esc(lang('Backoffice.cmp_field_objet')) ?> *</label>
                            <input class="form-control" id="objet" name="objet" value="<?= esc($val($record, 'objet')) ?>" required maxlength="255" data-review-label>
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="description"><?= esc(lang('Backoffice.cmp_field_description')) ?> *</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required data-review-label><?= esc($val($record, 'description')) ?></textarea>
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <label class="form-label" for="niveau_juridiction_id"><?= esc(lang('Backoffice.cmp_field_level')) ?> *</label>
                            <select class="form-select" id="niveau_juridiction_id" name="niveau_juridiction_id" data-cmp="niveau" required data-review-label>
                                <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                                <?php foreach ($levels as $opt): ?>
                                    <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'niveau_juridiction_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text"><?= esc(lang('Backoffice.cmp_hint_level_filing')) ?></div>
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <label class="form-label" for="province_id"><?= esc(lang('Backoffice.cmp_field_province')) ?> *</label>
                            <select class="form-select" id="province_id" name="province_id" data-cmp="province" required data-review-label>
                                <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                                <?php foreach ($provinces as $opt): ?>
                                    <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'province_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <label class="form-label" for="commune_id"><?= esc(lang('Backoffice.cmp_field_commune')) ?> *</label>
                            <select class="form-select" id="commune_id" name="commune_id" data-cmp="commune" required data-review-label>
                                <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                                <?php foreach ($communes as $opt): ?>
                                    <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'commune_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <label class="form-label" for="juridiction_id"><?= esc(lang('Backoffice.cmp_field_court')) ?> *</label>
                            <select class="form-select" id="juridiction_id" name="juridiction_id" data-cmp="juridiction" required data-review-label>
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

            <div class="bo-wizard-pane" data-wizard-step="2" hidden>
                <fieldset class="bo-form-section">
                    <legend class="bo-form-section-title"><?= esc(lang('Portal.new_wizard_step2_title')) ?></legend>
                    <p class="bo-wizard-step-lead"><?= esc(lang('Portal.new_wizard_step2_lead')) ?></p>
                    <div id="cmp-parcels" data-parcel-rows>
                        <?php foreach ($parcels as $i => $parcel): ?>
                            <div class="bo-parcel-card" data-parcel-row>
                                <div class="bo-parcel-card-head">
                                    <strong><?= esc(lang('Backoffice.cmp_parcel_label')) ?> #<span data-parcel-index><?= $i + 1 ?></span></strong>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-parcel-remove><?= esc(lang('Backoffice.cmp_parcel_remove')) ?></button>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label"><?= esc(lang('Backoffice.cmp_field_localisation')) ?> *</label>
                                        <textarea class="form-control" name="parcels[<?= $i ?>][localisation_parcelle]" rows="2" required><?= esc($parcel['localisation_parcelle'] ?? '') ?></textarea>
                                        <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><?= esc(lang('Backoffice.cmp_field_superficie')) ?></label>
                                        <input class="form-control" type="number" step="0.01" min="0" name="parcels[<?= $i ?>][superficie_maitre_carreau]" value="<?= esc($parcel['superficie_maitre_carreau'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label"><?= esc(lang('Backoffice.cmp_field_province')) ?> *</label>
                                        <select class="form-select" name="parcels[<?= $i ?>][province_parcelle_id]" data-parcel-province required>
                                            <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                                            <?php foreach ($provinces as $opt): ?>
                                                <option value="<?= esc($opt['id']) ?>" <?= (string) ($parcel['province_parcelle_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label"><?= esc(lang('Backoffice.cmp_field_commune')) ?> *</label>
                                        <select class="form-select" name="parcels[<?= $i ?>][commune_parcelle_id]" data-parcel-commune required>
                                            <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                                            <?php if (! empty($parcel['commune_parcelle_id'])): ?>
                                                <option value="<?= esc($parcel['commune_parcelle_id']) ?>" selected><?= esc($parcel['commune_name'] ?? $parcel['commune_parcelle_id']) ?></option>
                                            <?php endif; ?>
                                        </select>
                                        <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label"><?= esc(lang('Backoffice.people_field_zone')) ?></label>
                                        <select class="form-select" name="parcels[<?= $i ?>][zone_parcelle_id]" data-parcel-zone>
                                            <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                                            <?php if (! empty($parcel['zone_parcelle_id'])): ?>
                                                <option value="<?= esc($parcel['zone_parcelle_id']) ?>" selected><?= esc($parcel['zone_name'] ?? $parcel['zone_parcelle_id']) ?></option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label"><?= esc(lang('Backoffice.people_field_colline')) ?></label>
                                        <select class="form-select" name="parcels[<?= $i ?>][colline_parcelle_id]" data-parcel-colline>
                                            <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                                            <?php if (! empty($parcel['colline_parcelle_id'])): ?>
                                                <option value="<?= esc($parcel['colline_parcelle_id']) ?>" selected><?= esc($parcel['colline_name'] ?? $parcel['colline_parcelle_id']) ?></option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-bo-secondary btn-sm" data-parcel-add>
                        <i class="bi bi-plus" aria-hidden="true"></i> <?= esc(lang('Backoffice.cmp_parcel_add')) ?>
                    </button>
                </fieldset>
            </div>

            <div class="bo-wizard-pane" data-wizard-step="3" hidden>
                <fieldset class="bo-form-section">
                    <legend class="bo-form-section-title"><?= esc(lang('Portal.new_wizard_step3_title')) ?></legend>
                    <p class="bo-wizard-step-lead"><?= esc(lang('Portal.new_wizard_step3_lead')) ?></p>
                    <div class="bo-wizard-optional-note" role="note">
                        <p class="mb-1"><?= esc(lang('Portal.new_auto_plaintiff')) ?></p>
                        <p class="mb-0"><?= esc(lang('Portal.new_parties_optional')) ?></p>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-lg-6">
                            <?php $partySelect('complainant_ids', 'complainant_ids[]', lang('Backoffice.cmp_field_complainants'), $people, $complainants, false); ?>
                        </div>
                        <div class="col-12 col-lg-6">
                            <?php $partySelect('defendant_ids', 'defendant_ids[]', lang('Backoffice.cmp_field_defendants'), $people, $defendants, false); ?>
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="bo-wizard-pane" data-wizard-step="4" hidden>
                <fieldset class="bo-form-section">
                    <legend class="bo-form-section-title"><?= esc(lang('Portal.new_wizard_step4_title')) ?></legend>
                    <p class="bo-wizard-step-lead"><?= esc(lang('Portal.new_wizard_step4_lead')) ?></p>
                    <div id="cmp-documents" class="bo-doc-upload-grid" data-doc-types data-empty="<?= esc(lang('Backoffice.cmp_docs_hint'), 'attr') ?>">
                        <?php foreach ($docTypes as $type): ?>
                            <?php
                            $required  = db_bool($type['is_obligatoire'] ?? false) && ! $isEdit;
                            $typeId    = (int) ($type['type_document_id'] ?? 0);
                            $typeLabel = $type['libelle_type_document'] ?? $type['code_type_document'];
                            ?>
                            <div class="bo-doc-upload-card">
                                <div class="bo-doc-upload-head">
                                    <strong><?= esc($typeLabel) ?><?= $required ? ' *' : '' ?></strong>
                                    <span class="bo-doc-upload-badge <?= $required ? 'is-required' : 'is-optional' ?>">
                                        <?= esc($required ? lang('Backoffice.cmp_doc_required_badge') : lang('Backoffice.cmp_doc_optional_badge')) ?>
                                    </span>
                                </div>
                                <input class="form-control" type="file" name="documents[<?= $typeId ?>][]"
                                       accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                       multiple <?= $required ? 'required' : '' ?>>
                                <p class="form-text mb-0"><?= esc(lang('Backoffice.cmp_doc_accept_hint')) ?></p>
                                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                            </div>
                        <?php endforeach; ?>
                        <?php if ($docTypes === []): ?>
                            <p class="text-muted mb-0"><?= esc(lang('Backoffice.cmp_docs_hint')) ?></p>
                        <?php endif; ?>
                    </div>
                </fieldset>
            </div>

            <div class="bo-wizard-pane" data-wizard-step="5" data-wizard-review hidden>
                <fieldset class="bo-form-section">
                    <legend class="bo-form-section-title"><?= esc(lang('Portal.new_wizard_step5_title')) ?></legend>
                    <p class="bo-wizard-step-lead"><?= esc(lang('Portal.new_wizard_step5_lead')) ?></p>
                    <div class="bo-wizard-review" data-cmp-review>
                        <article class="bo-wizard-review-card">
                            <header>
                                <h3><?= esc(lang('Portal.new_wizard_step1_title')) ?></h3>
                                <button type="button" class="btn btn-link btn-sm" data-wizard-edit="1"><?= esc(lang('Portal.new_wizard_edit')) ?></button>
                            </header>
                            <dl>
                                <div class="bo-wizard-review-full"><dt><?= esc(lang('Backoffice.cmp_field_objet')) ?></dt><dd data-review="objet">—</dd></div>
                                <div class="bo-wizard-review-full"><dt><?= esc(lang('Backoffice.cmp_field_description')) ?></dt><dd data-review="description">—</dd></div>
                                <div><dt><?= esc(lang('Backoffice.cmp_field_level')) ?></dt><dd data-review="niveau_juridiction_id">—</dd></div>
                                <div><dt><?= esc(lang('Backoffice.cmp_field_province')) ?></dt><dd data-review="province_id">—</dd></div>
                                <div><dt><?= esc(lang('Backoffice.cmp_field_commune')) ?></dt><dd data-review="commune_id">—</dd></div>
                                <div><dt><?= esc(lang('Backoffice.cmp_field_court')) ?></dt><dd data-review="juridiction_id">—</dd></div>
                            </dl>
                        </article>
                        <article class="bo-wizard-review-card">
                            <header>
                                <h3><?= esc(lang('Portal.new_wizard_step2_title')) ?></h3>
                                <button type="button" class="btn btn-link btn-sm" data-wizard-edit="2"><?= esc(lang('Portal.new_wizard_edit')) ?></button>
                            </header>
                            <dl>
                                <div class="bo-wizard-review-full"><dt><?= esc(lang('Backoffice.cmp_section_parcels')) ?></dt><dd data-review="parcels_summary">—</dd></div>
                            </dl>
                        </article>
                        <article class="bo-wizard-review-card">
                            <header>
                                <h3><?= esc(lang('Portal.new_wizard_step3_title')) ?></h3>
                                <button type="button" class="btn btn-link btn-sm" data-wizard-edit="3"><?= esc(lang('Portal.new_wizard_edit')) ?></button>
                            </header>
                            <dl>
                                <div class="bo-wizard-review-full"><dt><?= esc(lang('Backoffice.cmp_field_complainants')) ?></dt><dd data-review="complainant_ids">—</dd></div>
                                <div class="bo-wizard-review-full"><dt><?= esc(lang('Backoffice.cmp_field_defendants')) ?></dt><dd data-review="defendant_ids">—</dd></div>
                            </dl>
                        </article>
                        <article class="bo-wizard-review-card">
                            <header>
                                <h3><?= esc(lang('Portal.new_wizard_step4_title')) ?></h3>
                                <button type="button" class="btn btn-link btn-sm" data-wizard-edit="4"><?= esc(lang('Portal.new_wizard_edit')) ?></button>
                            </header>
                            <dl>
                                <div class="bo-wizard-review-full"><dt><?= esc(lang('Backoffice.cmp_section_documents')) ?></dt><dd data-review="documents_summary">—</dd></div>
                            </dl>
                        </article>
                    </div>
                </fieldset>
            </div>

            <div class="bo-form-actions bo-wizard-actions">
                <a class="btn btn-outline-secondary" href="<?= site_url('portal/complaints') ?>"><?= esc(lang('Portal.new_cancel')) ?></a>
                <div class="bo-wizard-nav">
                    <button class="btn btn-bo-secondary" type="button" data-wizard-prev hidden>
                        <i class="bi bi-arrow-left" aria-hidden="true"></i>
                        <?= esc(lang('Portal.new_wizard_prev')) ?>
                    </button>
                    <button class="btn btn-bo-primary" type="button" data-wizard-next>
                        <?= esc(lang('Portal.new_wizard_next')) ?>
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </button>
                    <button class="btn btn-bo-primary" type="submit" data-wizard-submit hidden>
                        <i class="bi bi-check-lg" aria-hidden="true"></i>
                        <?= esc(lang('Portal.new_submit')) ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</section>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= public_asset('assets/css/backoffice.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
window.JH_CMP_WIZARD_I18N = {
    progress: <?= json_encode(lang('Portal.new_wizard_progress')) ?>,
    parcelCount: <?= json_encode(lang('Backoffice.cmp_review_parcel_count')) ?>,
    docsNone: <?= json_encode(lang('Backoffice.cmp_review_docs_none')) ?>,
    docsCount: <?= json_encode(lang('Backoffice.cmp_review_docs_count')) ?>,
    noneSelected: <?= json_encode(lang('Backoffice.cmp_review_none_selected')) ?>
};
</script>
<script src="<?= public_asset('assets/js/portal-complaint.js') ?>"></script>
<?= $this->endSection() ?>
