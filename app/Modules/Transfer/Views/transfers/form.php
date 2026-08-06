<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_case_transfers')) ?></p>
        <h1><?= esc(lang('Backoffice.trf_create_title')) ?></h1>
        <p><?= esc(lang('Backoffice.trf_form_lead')) ?></p>
    </div>
    <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/transfers') ?>">
        <i class="bi bi-arrow-left"></i> <?= esc(lang('Backoffice.trf_back_list')) ?>
    </a>
</section>

<section class="bo-panel bo-crud-panel">
    <form method="post" action="<?= site_url('backoffice/transfers') ?>" class="needs-validation" novalidate data-bo-trf-create
          data-api-communes="<?= esc(site_url('api/communes'), 'attr') ?>"
          data-api-jurisdictions="<?= esc(site_url('backoffice/api/court-jurisdictions'), 'attr') ?>"
          data-api-complaints="<?= esc(site_url('backoffice/transfers/api/eligible-complaints'), 'attr') ?>"
          data-api-destinations="<?= esc(site_url('backoffice/transfers/api/destination-courts'), 'attr') ?>">
        <?= csrf_field() ?>

        <h2 class="bo-form-section-title"><?= esc(lang('Backoffice.trf_section_source')) ?></h2>
        <div class="row g-3">
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label" for="niveau_juridiction_source_id"><?= esc(lang('Backoffice.trf_field_src_level')) ?> *</label>
                <select class="form-select" id="niveau_juridiction_source_id" name="niveau_juridiction_source_id" data-filter="src-niveau" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($levels as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($record['niveau_juridiction_source_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label" for="province_source_id"><?= esc(lang('Backoffice.trf_field_src_province')) ?></label>
                <select class="form-select" id="province_source_id" name="province_source_id" data-filter="src-province">
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($provinces as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($record['province_source_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label" for="commune_source_id"><?= esc(lang('Backoffice.trf_field_src_commune')) ?></label>
                <select class="form-select" id="commune_source_id" name="commune_source_id" data-filter="src-commune">
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($sourceCommunes as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($record['commune_source_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label" for="juridiction_source_id"><?= esc(lang('Backoffice.trf_field_src_court')) ?> *</label>
                <select class="form-select" id="juridiction_source_id" name="juridiction_source_id" data-filter="src-juridiction" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($sourceCourts as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($record['juridiction_source_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
        </div>

        <h2 class="bo-form-section-title mt-4"><?= esc(lang('Backoffice.trf_section_complaint')) ?></h2>
        <div class="row g-3">
            <div class="col-12 col-lg-8">
                <label class="form-label" for="plainte_id"><?= esc(lang('Backoffice.trf_field_complaint')) ?> *</label>
                <select class="form-select" id="plainte_id" name="plainte_id" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($complaints as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($record['plainte_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text"><?= esc(lang('Backoffice.trf_hint_complaint')) ?></div>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
        </div>

        <h2 class="bo-form-section-title mt-4"><?= esc(lang('Backoffice.trf_section_dest')) ?></h2>
        <div class="row g-3">
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label" for="niveau_juridiction_dest_id"><?= esc(lang('Backoffice.trf_field_dst_level')) ?> *</label>
                <select class="form-select" id="niveau_juridiction_dest_id" name="niveau_juridiction_dest_id" data-filter="dst-niveau" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($levels as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($record['niveau_juridiction_dest_id'] ?? $nextNiveauId ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text"><?= esc(lang('Backoffice.trf_hint_dest_level')) ?></div>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label" for="province_dest_id"><?= esc(lang('Backoffice.trf_field_dst_province')) ?></label>
                <select class="form-select" id="province_dest_id" name="province_dest_id" data-filter="dst-province">
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($provinces as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($record['province_dest_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label" for="commune_dest_id"><?= esc(lang('Backoffice.trf_field_dst_commune')) ?></label>
                <select class="form-select" id="commune_dest_id" name="commune_dest_id" data-filter="dst-commune">
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($destCommunes as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($record['commune_dest_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label" for="juridiction_dest_id"><?= esc(lang('Backoffice.trf_field_dst_court')) ?> *</label>
                <select class="form-select" id="juridiction_dest_id" name="juridiction_dest_id" data-filter="dst-juridiction" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($destCourts as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($record['juridiction_dest_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
        </div>

        <h2 class="bo-form-section-title mt-4"><?= esc(lang('Backoffice.trf_section_extra')) ?></h2>
        <div class="mb-3">
            <label class="form-label" for="observations"><?= esc(lang('Backoffice.trf_field_observations')) ?></label>
            <textarea class="form-control" id="observations" name="observations" rows="4"><?= esc((string) ($record['observations'] ?? '')) ?></textarea>
        </div>

        <div class="bo-form-actions">
            <a class="btn btn-outline-secondary" href="<?= site_url('backoffice/transfers') ?>"><?= esc(lang('Backoffice.btn_cancel')) ?></a>
            <button class="btn btn-bo-primary" type="submit"><?= esc(lang('Backoffice.trf_create')) ?></button>
        </div>
    </form>
</section>
<?= $this->endSection() ?>
