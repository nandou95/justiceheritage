<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_case_transfers')) ?></p>
        <h1><?= esc(lang('Backoffice.trf_details_title')) ?></h1>
        <p><?= esc($record['transfer_number']) ?> — <?= esc($record['case_number']) ?></p>
    </div>
    <div class="bo-crud-head-actions">
        <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/transfers') ?>">
            <i class="bi bi-arrow-left"></i> <?= esc(lang('Backoffice.trf_back_list')) ?>
        </a>
        <?php if ($record['can_process'] && can_access('backoffice/transfers/process')): ?>
            <a class="btn btn-bo-primary" href="<?= site_url('backoffice/transfers/' . $record['id'] . '/process') ?>">
                <i class="bi bi-box-arrow-in-down"></i> <?= esc(lang('Backoffice.trf_action_process')) ?>
            </a>
        <?php endif; ?>
    </div>
</section>

<section class="bo-panel bo-crud-panel">
    <div class="bo-detail-grid">
        <article>
            <h2><?= esc(lang('Backoffice.trf_section_complaint_info')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.trf_col_case')) ?></dt><dd><?= esc($record['case_number']) ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.trf_col_subject')) ?></dt><dd><?= esc($record['subject']) ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.trf_field_description')) ?></dt><dd><?= nl2br(esc($record['description'])) ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.trf_field_filing_date')) ?></dt><dd><?= esc($record['filing_date']) ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.trf_field_stage')) ?></dt><dd><?= esc($record['stage_label']) ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.trf_field_complaint_status')) ?></dt><dd><?= esc($record['complaint_status']) ?></dd></div>
            </dl>
        </article>
        <article>
            <h2><?= esc(lang('Backoffice.trf_section_source')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.filter_jurisdiction_level')) ?></dt><dd><?= esc($record['source_level']) ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.filter_court_jurisdiction')) ?></dt><dd><?= esc($record['source_court']) ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.filter_province')) ?></dt><dd><?= esc($record['source_province']) ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.filter_commune')) ?></dt><dd><?= esc($record['source_commune']) ?></dd></div>
            </dl>
        </article>
        <article>
            <h2><?= esc(lang('Backoffice.trf_section_dest')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.filter_jurisdiction_level')) ?></dt><dd><?= esc($record['dest_level']) ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.filter_court_jurisdiction')) ?></dt><dd><?= esc($record['dest_court']) ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.filter_province')) ?></dt><dd><?= esc($record['dest_province']) ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.filter_commune')) ?></dt><dd><?= esc($record['dest_commune']) ?></dd></div>
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
                <div><dt><?= esc(lang('Backoffice.trf_field_dest_case')) ?></dt><dd><?= esc($record['numero_dossier_dest']) ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.trf_field_observations')) ?></dt><dd><?= nl2br(esc($record['observations'] !== '' ? $record['observations'] : '—')) ?></dd></div>
            </dl>
        </article>
    </div>

    <h2 class="bo-form-section-title mt-4"><?= esc(lang('Backoffice.trf_section_documents')) ?></h2>
    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table w-100">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.trf_doc_type')) ?></th>
                    <th><?= esc(lang('Backoffice.trf_doc_name')) ?></th>
                    <th><?= esc(lang('Backoffice.trf_doc_date')) ?></th>
                    <th><?= esc(lang('Backoffice.trf_doc_uploader')) ?></th>
                    <th><?= esc(lang('Backoffice.col_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($record['documents'] as $doc): ?>
                <tr>
                    <td><?= esc($doc['type']) ?></td>
                    <td><?= esc($doc['name']) ?></td>
                    <td><?= esc($doc['date']) ?></td>
                    <td><?= esc($doc['uploader']) ?></td>
                    <td>
                        <div class="bo-row-actions">
                            <?php if (can_access('backoffice/transfers/show')): ?>
                            <a class="btn btn-bo-icon" href="<?= site_url('backoffice/transfers/documents/' . $doc['id'] . '/view') ?>" target="_blank" rel="noopener" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.trf_doc_view'), 'attr') ?>">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a class="btn btn-bo-icon" href="<?= site_url('backoffice/transfers/documents/' . $doc['id'] . '/download') ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.trf_doc_download'), 'attr') ?>">
                                <i class="bi bi-download"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (! $record['documents']): ?>
            <p class="bo-empty-hint"><?= esc(lang('Backoffice.trf_docs_empty')) ?></p>
        <?php endif; ?>
    </div>

    <h2 class="bo-form-section-title mt-4"><?= esc(lang('Backoffice.trf_section_audit')) ?></h2>
    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table w-100">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.logs_col_datetime')) ?></th>
                    <th><?= esc(lang('Backoffice.logs_col_action')) ?></th>
                    <th><?= esc(lang('Backoffice.logs_col_user')) ?></th>
                    <th><?= esc(lang('Backoffice.logs_col_ip')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($record['audit_history'] as $evt): ?>
                <tr>
                    <td><?= esc($evt['created_at']) ?></td>
                    <td><span class="bo-status-pill"><?= esc($evt['action']) ?></span></td>
                    <td><?= esc($evt['user_name']) ?></td>
                    <td><?= esc($evt['ip'] ?: '—') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (! $record['audit_history']): ?>
            <p class="bo-empty-hint"><?= esc(lang('Backoffice.trf_audit_empty')) ?></p>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>
