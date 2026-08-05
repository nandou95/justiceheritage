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
$judgeIds = array_map('strval', (array) (old('judge_ids') ?: ($record['judge_ids'] ?? [])));
?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_verdicts')) ?></p>
        <h1><?= esc(lang('Backoffice.vrd_create_title')) ?></h1>
        <p><?= esc(lang('Backoffice.vrd_form_lead')) ?></p>
    </div>
    <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/verdicts') ?>"><i class="bi bi-arrow-left"></i> <?= esc(lang('Backoffice.vrd_back_list')) ?></a>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-form needs-validation" method="post" action="<?= site_url('backoffice/verdicts') ?>" enctype="multipart/form-data" novalidate
          data-bo-vrd-form
          data-api-communes="<?= esc(site_url('api/communes'), 'attr') ?>"
          data-api-jurisdictions="<?= esc(site_url('backoffice/api/court-jurisdictions'), 'attr') ?>"
          data-api-hearings="<?= esc(site_url('backoffice/api/verdict-eligible-hearings'), 'attr') ?>"
          data-api-judges="<?= esc(site_url('backoffice/api/verdict-hearing-judges'), 'attr') ?>"
          data-api-deadline="<?= esc(site_url('backoffice/api/verdict-default-deadline'), 'attr') ?>"
          data-deadline-days="<?= esc((string) $deadlineDays, 'attr') ?>">
        <?= csrf_field() ?>

        <h2 class="h5"><?= esc(lang('Backoffice.vrd_section_general')) ?></h2>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label" for="niveau_juridiction_id"><?= esc(lang('Backoffice.vrd_field_level')) ?> *</label>
                <select class="form-select" id="niveau_juridiction_id" name="niveau_juridiction_id" data-vrd="niveau" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($levels as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'niveau_juridiction_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="province_id"><?= esc(lang('Backoffice.vrd_field_province')) ?> *</label>
                <select class="form-select" id="province_id" name="province_id" data-vrd="province" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($provinces as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'province_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="commune_id"><?= esc(lang('Backoffice.vrd_field_commune')) ?> *</label>
                <select class="form-select" id="commune_id" name="commune_id" data-vrd="commune" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($communes as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'commune_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="juridiction_id"><?= esc(lang('Backoffice.vrd_field_court')) ?> *</label>
                <select class="form-select" id="juridiction_id" name="juridiction_id" data-vrd="court" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($jurisdictions as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'juridiction_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="audience_plainte_id"><?= esc(lang('Backoffice.vrd_field_hearing_complaint')) ?> *</label>
                <select class="form-select" id="audience_plainte_id" name="audience_plainte_id" data-vrd="hearing" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($hearings as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>"
                                data-hearing-date="<?= esc($opt['hearing_date'] ?? '', 'attr') ?>"
                                <?= (string) $val($record, 'audience_plainte_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text"><?= esc(lang('Backoffice.vrd_hearing_hint')) ?></div>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="type_verdict_id"><?= esc(lang('Backoffice.vrd_field_type')) ?> *</label>
                <select class="form-select" id="type_verdict_id" name="type_verdict_id" required>
                    <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                    <?php foreach ($types as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) $val($record, 'type_verdict_id') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="date_verdict"><?= esc(lang('Backoffice.vrd_field_date')) ?> *</label>
                <input class="form-control" type="date" id="date_verdict" name="date_verdict" data-vrd="verdict-date" value="<?= esc($val($record, 'date_verdict')) ?>" required
                       <?= $hearingDate ? 'min="' . esc($hearingDate, 'attr') . '"' : '' ?>>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="date_limite_recours"><?= esc(lang('Backoffice.vrd_field_deadline')) ?></label>
                <input class="form-control" type="date" id="date_limite_recours" name="date_limite_recours" data-vrd="deadline" value="<?= esc($val($record, 'date_limite_recours')) ?>">
                <div class="form-text"><?= esc(lang('Backoffice.vrd_deadline_hint', [$deadlineDays])) ?></div>
            </div>
            <div class="col-md-9">
                <label class="form-label" for="upload_rapport_verdict"><?= esc(lang('Backoffice.vrd_field_report')) ?></label>
                <input class="form-control" type="file" id="upload_rapport_verdict" name="upload_rapport_verdict" accept=".pdf,.jpg,.jpeg,.png">
                <div class="form-text"><?= esc(lang('Backoffice.vrd_report_hint')) ?></div>
            </div>
            <div class="col-12">
                <label class="form-label" for="resume"><?= esc(lang('Backoffice.vrd_field_resume')) ?> *</label>
                <textarea class="form-control" id="resume" name="resume" rows="3" required><?= esc($val($record, 'resume')) ?></textarea>
            </div>
            <div class="col-12">
                <label class="form-label" for="dispositif"><?= esc(lang('Backoffice.vrd_field_dispositif')) ?> *</label>
                <textarea class="form-control" id="dispositif" name="dispositif" rows="4" required><?= esc($val($record, 'dispositif')) ?></textarea>
            </div>
        </div>

        <h2 class="h5"><?= esc(lang('Backoffice.vrd_section_judges')) ?></h2>
        <div class="mb-4">
            <label class="form-label" for="judge_ids"><?= esc(lang('Backoffice.vrd_field_judges')) ?> *</label>
            <select class="form-select" id="judge_ids" name="judge_ids[]" multiple size="6" data-vrd="judges" required>
                <?php foreach ($judges as $opt): ?>
                    <option value="<?= esc($opt['id']) ?>" <?= in_array((string) $opt['id'], $judgeIds, true) ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="form-text"><?= esc(lang('Backoffice.vrd_judges_hint')) ?></div>
        </div>

        <div class="bo-form-actions">
            <button class="btn btn-bo-primary" type="submit"><i class="bi bi-check-lg"></i> <?= esc(lang('Backoffice.vrd_create')) ?></button>
            <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/verdicts') ?>"><?= esc(lang('Backoffice.btn_cancel')) ?></a>
        </div>
    </form>
</section>
<?= $this->endSection() ?>
