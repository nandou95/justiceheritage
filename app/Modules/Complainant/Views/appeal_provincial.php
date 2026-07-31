<?= $this->extend('layouts/portal') ?>

<?= $this->section('content') ?>

<?php
$casesJson = [];
foreach ($cases ?? [] as $case) {
    $casesJson[$case['id']] = [
        'id'           => $case['id'],
        'subject'      => $case['subject'],
        'court_label'  => $case['court_label'],
        'status_label' => $case['status_label'],
        'status'       => $case['status'],
        'location'     => $case['location'],
        'filed'        => $case['filed'],
        'appeal_days'  => $case['appeal_days'],
    ];
}
?>

<section class="jh-appeal-hero">
    <div class="jh-appeal-hero-copy">
        <p class="jh-appeal-kicker"><?= esc(lang('Portal.prov_kicker')) ?></p>
        <h1><?= esc(lang('Portal.prov_h1')) ?></h1>
        <p><?= esc(lang('Portal.prov_lead')) ?></p>
    </div>
    <ol class="jh-appeal-steps" aria-label="<?= esc(lang('Portal.prov_steps_label')) ?>">
        <li class="is-active"><span>1</span><?= esc(lang('Portal.prov_step_1')) ?></li>
        <li><span>2</span><?= esc(lang('Portal.prov_step_2')) ?></li>
        <li><span>3</span><?= esc(lang('Portal.prov_step_3')) ?></li>
    </ol>
</section>

<section class="jh-appeal-pathway" aria-label="<?= esc(lang('Portal.prov_pathway_label')) ?>">
    <article class="jh-appeal-pathway-item is-done">
        <span class="jh-appeal-pathway-ico" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M4 20V9l8-5 8 5v11H4Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 20v-6h6v6" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>
        </span>
        <div>
            <span class="jh-appeal-pathway-tag"><?= esc(lang('Portal.path_stage_done')) ?></span>
            <strong><?= esc(lang('Portal.prov_path_from')) ?></strong>
            <p><?= esc(lang('Portal.prov_path_from_text')) ?></p>
        </div>
    </article>
    <span class="jh-appeal-pathway-arrow" aria-hidden="true">
        <span class="jh-appeal-pathway-line"></span>
        <span class="jh-appeal-pathway-chevron">→</span>
    </span>
    <article class="jh-appeal-pathway-item is-accent is-current">
        <span class="jh-appeal-pathway-ico" aria-hidden="true">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M12 8v4l2.5 1.5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
        </span>
        <div>
            <span class="jh-appeal-pathway-tag"><?= esc(lang('Portal.path_stage_current')) ?></span>
            <strong><?= esc(lang('Portal.prov_path_deadline')) ?></strong>
            <p><?= esc(lang('Portal.prov_path_deadline_text')) ?></p>
        </div>
    </article>
    <span class="jh-appeal-pathway-arrow" aria-hidden="true">
        <span class="jh-appeal-pathway-line"></span>
        <span class="jh-appeal-pathway-chevron">→</span>
    </span>
    <article class="jh-appeal-pathway-item">
        <span class="jh-appeal-pathway-ico" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="m7 14 5-5 5 5M5 20h14" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span>
        <div>
            <span class="jh-appeal-pathway-tag"><?= esc(lang('Portal.path_stage_next')) ?></span>
            <strong><?= esc(lang('Portal.prov_path_to')) ?></strong>
            <p><?= esc(lang('Portal.prov_path_to_text')) ?></p>
        </div>
    </article>
</section>

<?= view('Modules\Complainant\Views\partials\list_stats', [
    'listStats' => $listStats ?? [],
]) ?>

<section class="jh-dash-panel jh-appeal-cases jh-appeal-cases--list">
    <div class="jh-dash-panel-head">
        <div>
            <h2><?= esc(lang('Portal.prov_cases_title')) ?></h2>
            <p><?= esc(lang('Portal.prov_cases_lead')) ?></p>
        </div>
    </div>

    <?= view('Modules\Complainant\Views\partials\provincial_complaint_list_table', [
        'complaints'   => $provincialComplaints ?? [],
        'emptyMessage' => lang('Portal.list_empty_message'),
        'pageLength'   => 10,
    ]) ?>
</section>

<section class="jh-appeal-form-panel jh-appeal-form-panel--standalone"
         data-appeal-page
         data-cases="<?= esc(json_encode($casesJson, JSON_UNESCAPED_UNICODE), 'attr') ?>"
         data-days-template="<?= esc(lang('Portal.prov_days_left'), 'attr') ?>"
         data-no-deadline="<?= esc(lang('Portal.prov_no_deadline'), 'attr') ?>">
    <div class="jh-appeal-form-card">
        <header class="jh-appeal-form-head">
            <h2><?= esc(lang('Portal.prov_form_title')) ?></h2>
            <p><?= esc(lang('Portal.prov_form_lead')) ?></p>
        </header>

        <div class="jh-appeal-preview" data-appeal-preview>
            <div class="jh-appeal-preview-empty" data-appeal-empty>
                <span class="jh-appeal-preview-ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M8 7h11M8 12h11M8 17h7M5 7h.01M5 12h.01M5 17h.01" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                </span>
                <p><?= esc(lang('Portal.prov_preview_empty')) ?></p>
            </div>
            <div class="jh-appeal-preview-body" data-appeal-selected hidden>
                <span class="jh-case-ref" data-preview-id></span>
                <h3 data-preview-subject></h3>
                <dl class="jh-appeal-preview-meta">
                    <div>
                        <dt><?= esc(lang('Portal.list_court')) ?></dt>
                        <dd data-preview-court></dd>
                    </div>
                    <div>
                        <dt><?= esc(lang('Portal.list_jurisdiction')) ?></dt>
                        <dd data-preview-location></dd>
                    </div>
                    <div>
                        <dt><?= esc(lang('Portal.list_status')) ?></dt>
                        <dd data-preview-status></dd>
                    </div>
                    <div>
                        <dt><?= esc(lang('Portal.prov_deadline_col')) ?></dt>
                        <dd data-preview-deadline></dd>
                    </div>
                </dl>
            </div>
        </div>

        <form class="jh-portal-form jh-appeal-form" method="post" action="<?= site_url('portal/appeals/provincial') ?>" data-appeal-form>
            <?= csrf_field() ?>

            <div class="jh-field mb-3">
                <label class="form-label" for="case_id"><?= esc(lang('Portal.prov_case')) ?> <span class="jh-req">*</span></label>
                <select class="form-select" id="case_id" name="case_id" required data-appeal-select>
                    <option value=""><?= esc(lang('Portal.prov_case_placeholder')) ?></option>
                    <?php foreach ($cases ?? [] as $case): ?>
                        <option value="<?= esc($case['id']) ?>">
                            <?= esc($case['id'] . ' · ' . $case['subject']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="jh-field-hint"><?= esc(lang('Portal.prov_case_hint')) ?></small>
            </div>

            <div class="jh-field mb-3">
                <label class="form-label" for="grounds"><?= esc(lang('Portal.prov_grounds')) ?> <span class="jh-req">*</span></label>
                <textarea class="form-control" id="grounds" name="grounds" rows="7" required
                          placeholder="<?= esc(lang('Portal.prov_grounds_ph'), 'attr') ?>"
                          data-appeal-grounds></textarea>
                <div class="jh-appeal-grounds-meta">
                    <small class="jh-field-hint"><?= esc(lang('Portal.prov_grounds_hint')) ?></small>
                    <small class="jh-appeal-charcount" data-appeal-count>0</small>
                </div>
            </div>

            <button class="btn btn-jh-primary w-100" type="submit" <?= empty($cases) ? 'disabled' : '' ?>>
                <?= esc(lang('Portal.prov_submit')) ?>
            </button>
        </form>
    </div>
</section>

<?= $this->endSection() ?>
