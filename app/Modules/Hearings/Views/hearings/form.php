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
$selectedIds = array_map('strval', (array) (old('plainte_ids') ?: ($record['plainte_ids'] ?? [])));
?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_hearings')) ?></p>
        <h1><?= esc(lang('Backoffice.hrg_create_title')) ?></h1>
        <p><?= esc(lang('Backoffice.hrg_form_lead')) ?></p>
    </div>
    <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/hearings') ?>"><i class="bi bi-arrow-left"></i> <?= esc(lang('Backoffice.hrg_back_list')) ?></a>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-form needs-validation" method="post" action="<?= site_url('backoffice/hearings') ?>" novalidate
          data-bo-hrg-form
          data-api-communes="<?= esc(site_url('api/communes'), 'attr') ?>"
          data-api-zones="<?= esc(site_url('api/zones'), 'attr') ?>"
          data-api-collines="<?= esc(site_url('api/collines'), 'attr') ?>"
          data-api-jurisdictions="<?= esc(site_url('backoffice/api/court-jurisdictions'), 'attr') ?>"
          data-api-complaints="<?= esc(site_url('backoffice/api/hearing-eligible-complaints'), 'attr') ?>">
        <?= csrf_field() ?>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label" for="niveau_juridiction_id"><?= esc(lang('Backoffice.hrg_field_level')) ?> *</label>
                <select class="form-select" id="niveau_juridiction_id" name="niveau_juridiction_id" data-hrg="niveau" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($levels as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'niveau_juridiction_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="province_audience_id"><?= esc(lang('Backoffice.hrg_field_province')) ?> *</label>
                <select class="form-select" id="province_audience_id" name="province_audience_id" data-hrg="province" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($provinces as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'province_audience_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="commune_audience_id"><?= esc(lang('Backoffice.hrg_field_commune')) ?> *</label>
                <select class="form-select" id="commune_audience_id" name="commune_audience_id" data-hrg="commune" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($communes as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'commune_audience_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="zone_audience_id"><?= esc(lang('Backoffice.hrg_field_zone')) ?></label>
                <select class="form-select" id="zone_audience_id" name="zone_audience_id" data-hrg="zone">
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($zones as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'zone_audience_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="colline_audience_id"><?= esc(lang('Backoffice.hrg_field_colline')) ?></label>
                <select class="form-select" id="colline_audience_id" name="colline_audience_id" data-hrg="colline">
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($collines as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'colline_audience_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="juridiction_audience_id"><?= esc(lang('Backoffice.hrg_field_court')) ?> *</label>
                <select class="form-select" id="juridiction_audience_id" name="juridiction_audience_id" data-hrg="court" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($jurisdictions as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'juridiction_audience_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="date_audience"><?= esc(lang('Backoffice.hrg_field_date')) ?> *</label>
                <input class="form-control" type="date" id="date_audience" name="date_audience" value="<?= esc($val($record, 'date_audience')) ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="heure_audience"><?= esc(lang('Backoffice.hrg_field_time')) ?> *</label>
                <input class="form-control" type="time" id="heure_audience" name="heure_audience" value="<?= esc($val($record, 'heure_audience')) ?>" required>
            </div>
            <div class="col-12">
                <label class="form-label" for="lieu_audience"><?= esc(lang('Backoffice.hrg_field_venue')) ?> *</label>
                <input class="form-control" id="lieu_audience" name="lieu_audience" value="<?= esc($val($record, 'lieu_audience')) ?>" required>
            </div>
            <div class="col-12">
                <label class="form-label" for="plainte_ids"><?= esc(lang('Backoffice.hrg_field_complaints')) ?> *</label>
                <select class="form-select" id="plainte_ids" name="plainte_ids[]" multiple size="8" data-hrg="complaints" required>
                    <?php foreach ($complaints as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= in_array((string) $opt['id'], $selectedIds, true) ? 'selected' : '' ?>><?= esc($opt['label'] . ' (' . $opt['court'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text"><?= esc(lang('Backoffice.hrg_complaints_hint')) ?></div>
            </div>
        </div>

        <div class="bo-form-actions">
            <button class="btn btn-bo-primary" type="submit"><i class="bi bi-check-lg"></i> <?= esc(lang('Backoffice.hrg_create')) ?></button>
            <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/hearings') ?>"><?= esc(lang('Backoffice.btn_cancel')) ?></a>
        </div>
    </form>
</section>
<?= $this->endSection() ?>
