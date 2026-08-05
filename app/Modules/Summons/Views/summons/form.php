<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<?php
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
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_summons')) ?></p>
        <h1><?= esc(lang('Backoffice.sum_create_title')) ?></h1>
        <p><?= esc(lang('Backoffice.sum_form_lead')) ?></p>
    </div>
    <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/summons/pending') ?>"><i class="bi bi-arrow-left"></i> <?= esc(lang('Backoffice.sum_back_pending')) ?></a>
</section>

<section class="bo-panel bo-crud-panel">
    <h2 class="h5"><?= esc(lang('Backoffice.sum_section_complaint')) ?></h2>
    <dl class="bo-detail-list mb-4">
        <div><dt><?= esc(lang('Backoffice.sum_col_case')) ?></dt><dd><code class="bo-route-code"><?= esc($complaint['numero_dossier'] ?? '—') ?></code></dd></div>
        <div><dt><?= esc(lang('Backoffice.sum_col_subject')) ?></dt><dd><?= esc($complaint['objet'] ?? '—') ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.sum_col_court')) ?></dt><dd><?= esc(trim(($complaint['desc_niveau_juridiction'] ?? '') . ' / ' . ($complaint['nom_juridiction'] ?? ''), ' /')) ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.sum_col_stage')) ?></dt><dd><?= esc($complaint['description_etape_plainte'] ?? '—') ?></dd></div>
        <div><dt><?= esc(lang('Backoffice.sum_col_complaint_status')) ?></dt><dd><?= esc($complaint['description_statut_plainte'] ?? '—') ?></dd></div>
    </dl>

    <form class="bo-form needs-validation" method="post" action="<?= site_url('backoffice/summons/create/' . (int) $complaint['plainte_id']) ?>" novalidate
          data-bo-sum-form
          data-api-communes="<?= esc(site_url('api/communes'), 'attr') ?>"
          data-api-zones="<?= esc(site_url('api/zones'), 'attr') ?>"
          data-api-collines="<?= esc(site_url('api/collines'), 'attr') ?>"
          data-api-jurisdictions="<?= esc(site_url('backoffice/api/court-jurisdictions'), 'attr') ?>">
        <?= csrf_field() ?>

        <h2 class="h5"><?= esc(lang('Backoffice.sum_section_hearing')) ?></h2>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label" for="date_audience"><?= esc(lang('Backoffice.sum_field_hearing_date')) ?> *</label>
                <input class="form-control" type="date" id="date_audience" name="date_audience" value="<?= esc($val($record, 'date_audience')) ?>" required>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="heure_audience"><?= esc(lang('Backoffice.sum_field_hearing_time')) ?> *</label>
                <input class="form-control" type="time" id="heure_audience" name="heure_audience" value="<?= esc($val($record, 'heure_audience')) ?>" required>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="juridiction_lieu_audience_id"><?= esc(lang('Backoffice.sum_field_court')) ?> *</label>
                <select class="form-select" id="juridiction_lieu_audience_id" name="juridiction_lieu_audience_id" data-sum="court" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($jurisdictions as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'juridiction_lieu_audience_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="province_lieu_audience_id"><?= esc(lang('Backoffice.sum_field_province')) ?> *</label>
                <select class="form-select" id="province_lieu_audience_id" name="province_lieu_audience_id" data-sum="province" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($provinces as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'province_lieu_audience_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="commune_lieu_audience_id"><?= esc(lang('Backoffice.sum_field_commune')) ?> *</label>
                <select class="form-select" id="commune_lieu_audience_id" name="commune_lieu_audience_id" data-sum="commune" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($communes as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'commune_lieu_audience_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="zone_lieu_audience_id"><?= esc(lang('Backoffice.sum_field_zone')) ?></label>
                <select class="form-select" id="zone_lieu_audience_id" name="zone_lieu_audience_id" data-sum="zone">
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($zones as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'zone_lieu_audience_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="colline_lieu_audience_id"><?= esc(lang('Backoffice.sum_field_colline')) ?></label>
                <select class="form-select" id="colline_lieu_audience_id" name="colline_lieu_audience_id" data-sum="colline">
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($collines as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'colline_lieu_audience_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label" for="lieu_audience"><?= esc(lang('Backoffice.sum_field_venue')) ?> *</label>
                <input class="form-control" id="lieu_audience" name="lieu_audience" value="<?= esc($val($record, 'lieu_audience')) ?>" required>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12">
                <label class="form-label" for="observations"><?= esc(lang('Backoffice.sum_field_observations')) ?></label>
                <textarea class="form-control" id="observations" name="observations" rows="3"><?= esc($val($record, 'observations')) ?></textarea>
            </div>
        </div>

        <div class="bo-form-actions">
            <button class="btn btn-bo-primary" type="submit"><i class="bi bi-check-lg"></i> <?= esc(lang('Backoffice.sum_create')) ?></button>
            <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/summons/pending') ?>"><?= esc(lang('Backoffice.btn_cancel')) ?></a>
        </div>
    </form>
</section>
<?= $this->endSection() ?>
