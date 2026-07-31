<?= $this->extend('layouts/portal') ?>

<?= $this->section('content') ?>

<section class="jh-appeal-hero">
    <div class="jh-appeal-hero-copy">
        <p class="jh-appeal-kicker"><?= esc(lang('Portal.level_regional')) ?></p>
        <h1><?= esc(lang('Portal.reg_h1')) ?></h1>
        <p><?= esc(lang('Portal.reg_lead')) ?></p>
    </div>
</section>

<section class="jh-appeal-pathway" aria-label="<?= esc(lang('Portal.reg_pathway_label')) ?>">
    <article class="jh-appeal-pathway-item is-done">
        <span class="jh-appeal-pathway-ico" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="m7 14 5-5 5 5M5 20h14" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span>
        <div>
            <span class="jh-appeal-pathway-tag"><?= esc(lang('Portal.path_stage_done')) ?></span>
            <strong><?= esc(lang('Portal.reg_path_from')) ?></strong>
            <p><?= esc(lang('Portal.reg_path_from_text')) ?></p>
        </div>
    </article>
    <span class="jh-appeal-pathway-arrow" aria-hidden="true">
        <span class="jh-appeal-pathway-line"></span>
        <span class="jh-appeal-pathway-chevron">→</span>
    </span>
    <article class="jh-appeal-pathway-item">
        <span class="jh-appeal-pathway-ico" aria-hidden="true">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M12 8v4l2.5 1.5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
        </span>
        <div>
            <span class="jh-appeal-pathway-tag"><?= esc(lang('Portal.path_stage_window')) ?></span>
            <strong><?= esc(lang('Portal.reg_path_deadline')) ?></strong>
            <p><?= esc(lang('Portal.reg_path_deadline_text')) ?></p>
        </div>
    </article>
    <span class="jh-appeal-pathway-arrow" aria-hidden="true">
        <span class="jh-appeal-pathway-line"></span>
        <span class="jh-appeal-pathway-chevron">→</span>
    </span>
    <article class="jh-appeal-pathway-item is-accent is-current">
        <span class="jh-appeal-pathway-ico" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M12 3 4 9v11h6v-6h4v6h6V9l-8-6Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
        </span>
        <div>
            <span class="jh-appeal-pathway-tag"><?= esc(lang('Portal.path_stage_current')) ?></span>
            <strong><?= esc(lang('Portal.reg_path_to')) ?></strong>
            <p><?= esc(lang('Portal.reg_path_to_text')) ?></p>
        </div>
    </article>
</section>

<?= view('Modules\Complainant\Views\partials\list_stats', [
    'listStats' => $listStats ?? [],
]) ?>

<section class="jh-dash-panel jh-appeal-cases jh-appeal-cases--list">
    <div class="jh-dash-panel-head">
        <div>
            <h2><?= esc(lang('Portal.reg_cases_title')) ?></h2>
            <p><?= esc(lang('Portal.reg_cases_lead')) ?></p>
        </div>
    </div>

    <?= view('Modules\Complainant\Views\partials\provincial_complaint_list_table', [
        'complaints'   => $regionalComplaints ?? [],
        'emptyMessage' => lang('Portal.list_empty_message'),
        'pageLength'   => 10,
    ]) ?>
</section>

<section class="jh-appeal-form-panel jh-appeal-form-panel--standalone">
    <div class="jh-appeal-form-card">
        <header class="jh-appeal-form-head">
            <h2><?= esc(lang('Portal.reg_submit')) ?></h2>
            <p><?= esc(lang('Portal.new_demo')) ?></p>
        </header>

        <form class="jh-portal-form" method="post" action="<?= site_url('portal/appeals/regional') ?>">
            <?= csrf_field() ?>
            <div class="jh-field mb-3">
                <label class="form-label" for="case_id"><?= esc(lang('Portal.reg_case')) ?></label>
                <select class="form-select" id="case_id" name="case_id" required>
                    <option value="">—</option>
                    <?php foreach ($cases ?? [] as $case): ?>
                        <option value="<?= esc($case['id']) ?>"><?= esc($case['id'] . ' · ' . $case['court_label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="jh-field mb-3">
                <label class="form-label" for="grounds"><?= esc(lang('Portal.reg_grounds')) ?></label>
                <textarea class="form-control" id="grounds" name="grounds" rows="6" required></textarea>
            </div>
            <button class="btn btn-jh-primary w-100" type="submit" <?= empty($cases) ? 'disabled' : '' ?>>
                <?= esc(lang('Portal.reg_submit')) ?>
            </button>
        </form>
    </div>
</section>

<?= $this->endSection() ?>
