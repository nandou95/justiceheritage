<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<?php
$isEdit = ($mode ?? 'create') === 'edit';
$action = $isEdit
    ? site_url('backoffice/complaints/' . (int) ($record['plainte_id'] ?? 0))
    : site_url('backoffice/complaints');
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
$witnesses    = $selected($record, 'witness_ids');
?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_complaints_list')) ?></p>
        <h1><?= esc($isEdit ? lang('Backoffice.cmp_edit_title') : lang('Backoffice.cmp_create_title')) ?></h1>
        <p><?= esc(lang('Backoffice.cmp_form_lead')) ?></p>
    </div>
    <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/complaints') ?>"><i class="bi bi-arrow-left"></i> <?= esc(lang('Backoffice.cmp_back_list')) ?></a>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-form needs-validation" method="post" action="<?= esc($action) ?>" enctype="multipart/form-data" novalidate
          data-bo-cmp-form
          data-api-communes="<?= esc(site_url('api/communes'), 'attr') ?>"
          data-api-zones="<?= esc(site_url('api/zones'), 'attr') ?>"
          data-api-collines="<?= esc(site_url('api/collines'), 'attr') ?>"
          data-api-jurisdictions="<?= esc(site_url('backoffice/api/court-jurisdictions'), 'attr') ?>"
          data-api-doc-types="<?= esc(site_url('backoffice/api/complaint-document-types'), 'attr') ?>">
        <?= csrf_field() ?>

        <h2 class="h5"><?= esc(lang('Backoffice.cmp_section_general')) ?></h2>
        <div class="row g-3 mb-4">
            <div class="col-12">
                <label class="form-label" for="objet"><?= esc(lang('Backoffice.cmp_field_objet')) ?> *</label>
                <input class="form-control" id="objet" name="objet" value="<?= esc($val($record, 'objet')) ?>" required>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12">
                <label class="form-label" for="description"><?= esc(lang('Backoffice.cmp_field_description')) ?> *</label>
                <textarea class="form-control" id="description" name="description" rows="4" required><?= esc($val($record, 'description')) ?></textarea>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="niveau_juridiction_id"><?= esc(lang('Backoffice.cmp_field_level')) ?> *</label>
                <select class="form-select" id="niveau_juridiction_id" name="niveau_juridiction_id" data-cmp="niveau" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($levels as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'niveau_juridiction_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="province_id"><?= esc(lang('Backoffice.cmp_field_province')) ?> *</label>
                <select class="form-select" id="province_id" name="province_id" data-cmp="province" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($provinces as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'province_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="commune_id"><?= esc(lang('Backoffice.cmp_field_commune')) ?> *</label>
                <select class="form-select" id="commune_id" name="commune_id" data-cmp="commune" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($communes as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'commune_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="juridiction_id"><?= esc(lang('Backoffice.cmp_field_court')) ?> *</label>
                <select class="form-select" id="juridiction_id" name="juridiction_id" data-cmp="juridiction" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($jurisdictions as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'juridiction_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <h2 class="h5"><?= esc(lang('Backoffice.cmp_section_parcels')) ?></h2>
        <div id="cmp-parcels" data-parcel-rows>
            <?php foreach ($parcels as $i => $parcel): ?>
                <div class="border rounded p-3 mb-3" data-parcel-row>
                    <div class="d-flex justify-content-between mb-2">
                        <strong><?= esc(lang('Backoffice.cmp_parcel_label')) ?> #<?= $i + 1 ?></strong>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-parcel-remove><?= esc(lang('Backoffice.cmp_parcel_remove')) ?></button>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label"><?= esc(lang('Backoffice.cmp_field_localisation')) ?> *</label>
                            <textarea class="form-control" name="parcels[<?= $i ?>][localisation_parcelle]" rows="2" required><?= esc($parcel['localisation_parcelle'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?= esc(lang('Backoffice.cmp_field_superficie')) ?></label>
                            <input class="form-control" type="number" step="0.01" name="parcels[<?= $i ?>][superficie_maitre_carreau]" value="<?= esc($parcel['superficie_maitre_carreau'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><?= esc(lang('Backoffice.cmp_field_province')) ?> *</label>
                            <select class="form-select" name="parcels[<?= $i ?>][province_parcelle_id]" data-parcel-province required>
                                <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                                <?php foreach ($provinces as $opt): ?>
                                    <option value="<?= esc($opt['id']) ?>" <?= (string) ($parcel['province_parcelle_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><?= esc(lang('Backoffice.cmp_field_commune')) ?> *</label>
                            <select class="form-select" name="parcels[<?= $i ?>][commune_parcelle_id]" data-parcel-commune required>
                                <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                                <?php if (! empty($parcel['commune_parcelle_id'])): ?>
                                    <option value="<?= esc($parcel['commune_parcelle_id']) ?>" selected><?= esc($parcel['commune_name'] ?? $parcel['commune_parcelle_id']) ?></option>
                                <?php endif; ?>
                            </select>
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
        <button type="button" class="btn btn-bo-secondary btn-sm mb-4" data-parcel-add><i class="bi bi-plus"></i> <?= esc(lang('Backoffice.cmp_parcel_add')) ?></button>

        <h2 class="h5"><?= esc(lang('Backoffice.cmp_section_parties')) ?></h2>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label" for="complainant_ids"><?= esc(lang('Backoffice.cmp_field_complainants')) ?> *</label>
                <select class="form-select" id="complainant_ids" name="complainant_ids[]" multiple size="6" required>
                    <?php foreach ($people as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= in_array((string) $opt['id'], $complainants, true) ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="defendant_ids"><?= esc(lang('Backoffice.cmp_field_defendants')) ?> *</label>
                <select class="form-select" id="defendant_ids" name="defendant_ids[]" multiple size="6" required>
                    <?php foreach ($people as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= in_array((string) $opt['id'], $defendants, true) ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($hasWitness): ?>
            <div class="col-md-4">
                <label class="form-label" for="witness_ids"><?= esc(lang('Backoffice.cmp_field_witnesses')) ?></label>
                <select class="form-select" id="witness_ids" name="witness_ids[]" multiple size="6">
                    <?php foreach ($people as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= in_array((string) $opt['id'], $witnesses, true) ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
        </div>

        <h2 class="h5"><?= esc(lang('Backoffice.cmp_section_documents')) ?></h2>
        <div id="cmp-documents" class="mb-3" data-doc-types>
            <?php foreach ($docTypes as $type): ?>
                <div class="mb-3">
                    <label class="form-label">
                        <?= esc($type['libelle_type_document'] ?? $type['code_type_document']) ?>
                        <?= filter_var($type['is_obligatoire'] ?? false, FILTER_VALIDATE_BOOLEAN) ? '*' : '' ?>
                    </label>
                    <input class="form-control" type="file" name="documents[<?= (int) $type['type_document_id'] ?>][]" accept=".pdf,.jpg,.jpeg,.png" <?= filter_var($type['is_obligatoire'] ?? false, FILTER_VALIDATE_BOOLEAN) && ! $isEdit ? 'required' : '' ?>>
                </div>
            <?php endforeach; ?>
            <?php if ($docTypes === []): ?>
                <p class="text-muted"><?= esc(lang('Backoffice.cmp_docs_hint')) ?></p>
            <?php endif; ?>
        </div>

        <div class="bo-form-actions">
            <button class="btn btn-bo-primary" type="submit"><i class="bi bi-check-lg"></i> <?= esc($isEdit ? lang('Backoffice.cmp_save') : lang('Backoffice.cmp_create')) ?></button>
            <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/complaints') ?>"><?= esc(lang('Backoffice.btn_cancel')) ?></a>
        </div>
    </form>
</section>
<?= $this->endSection() ?>
