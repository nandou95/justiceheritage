<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_case_transfers')) ?></p>
        <h1><?= esc(lang('Backoffice.trf_process_title')) ?></h1>
        <p><?= esc(lang('Backoffice.trf_process_lead')) ?></p>
    </div>
    <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/transfers/' . $record['id']) ?>">
        <i class="bi bi-arrow-left"></i> <?= esc(lang('Backoffice.trf_back_details')) ?>
    </a>
</section>

<section class="bo-panel bo-crud-panel">
    <?php if ($record['already_received']): ?>
        <div class="alert alert-warning bo-alert" role="alert">
            <?= esc(lang('Backoffice.trf_already_received_msg')) ?>
        </div>
    <?php endif; ?>

    <div class="bo-detail-grid">
        <article>
            <h2><?= esc(lang('Backoffice.trf_section_complaint_info')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.trf_col_case')) ?></dt><dd><?= esc($record['case_number']) ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.trf_col_subject')) ?></dt><dd><?= esc($record['subject']) ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.trf_field_complaint_status')) ?></dt><dd><?= esc($record['complaint_status']) ?></dd></div>
            </dl>
        </article>
        <article>
            <h2><?= esc(lang('Backoffice.trf_section_source')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.filter_jurisdiction_level')) ?></dt><dd><?= esc($record['source_level']) ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.filter_court_jurisdiction')) ?></dt><dd><?= esc($record['source_court']) ?></dd></div>
            </dl>
        </article>
        <article>
            <h2><?= esc(lang('Backoffice.trf_section_dest')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.filter_jurisdiction_level')) ?></dt><dd><?= esc($record['dest_level']) ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.filter_court_jurisdiction')) ?></dt><dd><?= esc($record['dest_court']) ?></dd></div>
            </dl>
        </article>
        <article>
            <h2><?= esc(lang('Backoffice.trf_section_transfer_info')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.trf_field_transfer_number')) ?></dt><dd><?= esc($record['transfer_number']) ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.trf_col_transfer_date')) ?></dt><dd><?= esc($record['date_transfert']) ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.trf_col_reception_date')) ?></dt><dd><?= esc($record['date_reception']) ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.trf_col_status')) ?></dt><dd><?= esc($record['status_label']) ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.trf_field_transferred_by')) ?></dt><dd><?= esc($record['transferred_by']) ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.trf_field_received_by')) ?></dt><dd><?= esc($record['received_by']) ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.trf_field_observations')) ?></dt><dd><?= nl2br(esc($record['observations'] !== '' ? $record['observations'] : '—')) ?></dd></div>
            </dl>
        </article>
    </div>

    <div class="bo-form-actions mt-4">
        <a class="btn btn-outline-secondary" href="<?= site_url('backoffice/transfers/' . $record['id']) ?>"><?= esc(lang('Backoffice.btn_cancel')) ?></a>
        <?php if ($record['can_process']): ?>
            <form method="post" action="<?= site_url('backoffice/transfers/' . $record['id'] . '/receive') ?>" class="d-inline" onsubmit="return confirm('<?= esc(lang('Backoffice.trf_receive_confirm'), 'js') ?>');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-bo-primary">
                    <i class="bi bi-box-arrow-in-down"></i>
                    <?= esc(lang('Backoffice.trf_receive_btn')) ?>
                </button>
            </form>
        <?php else: ?>
            <button type="button" class="btn btn-bo-primary" disabled>
                <i class="bi bi-check2-circle"></i>
                <?= esc(lang('Backoffice.trf_receive_btn')) ?>
            </button>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>
