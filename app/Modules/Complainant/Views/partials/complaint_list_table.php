<?php
/**
 * Shared communal complaint list table.
 *
 * Always renders the table (headers + DataTables controls), even with zero rows.
 *
 * @var list<array<string, mixed>> $complaints
 * @var string                     $emptyMessage
 * @var int                        $pageLength
 */
$complaints   = $complaints ?? [];
$emptyMessage = $emptyMessage ?? lang('Portal.list_empty_message');
$pageLength   = $pageLength ?? 10;
?>

<div class="jh-table-wrap jh-table-wrap--complaints">
    <table class="table table-hover jh-table jh-datatable jh-complaint-list-table w-100"
           data-page-length="<?= (int) $pageLength ?>"
           data-order-col="6"
           data-order-dir="desc"
           data-length-change="<?= $pageLength <= 5 ? 'false' : 'true' ?>"
           data-empty-table="<?= esc($emptyMessage) ?>"
           data-zero-records="<?= esc($emptyMessage) ?>">
        <thead>
            <tr>
                <th><?= esc(lang('Portal.col_case_number')) ?></th>
                <th><?= esc(lang('Portal.col_subject')) ?></th>
                <th><?= esc(lang('Portal.col_description')) ?></th>
                <th><?= esc(lang('Portal.col_court_jurisdiction')) ?></th>
                <th><?= esc(lang('Portal.col_complaint_stage')) ?></th>
                <th><?= esc(lang('Portal.col_complaint_status')) ?></th>
                <th><?= esc(lang('Portal.col_submission_date')) ?></th>
                <th><?= esc(lang('Portal.col_created_date')) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($complaints as $row): ?>
                <tr>
                    <td data-label="<?= esc(lang('Portal.col_case_number')) ?>">
                        <a class="jh-case-ref"
                           href="<?= site_url('portal/complaints/' . rawurlencode((string) $row['id'])) ?>">
                            <?= esc($row['case_number'] !== '' ? $row['case_number'] : ('#' . $row['id'])) ?>
                        </a>
                    </td>
                    <td data-label="<?= esc(lang('Portal.col_subject')) ?>">
                        <span class="jh-cell-text"><?= esc($row['subject'] ?: '—') ?></span>
                    </td>
                    <td data-label="<?= esc(lang('Portal.col_description')) ?>">
                        <span class="jh-cell-desc" title="<?= esc($row['description']) ?>">
                            <?= esc($row['description'] ?: '—') ?>
                        </span>
                    </td>
                    <td data-label="<?= esc(lang('Portal.col_court_jurisdiction')) ?>">
                        <?= esc($row['court_jurisdiction'] ?: '—') ?>
                    </td>
                    <td data-label="<?= esc(lang('Portal.col_complaint_stage')) ?>">
                        <span class="jh-stage-badge"><?= esc($row['stage'] ?: '—') ?></span>
                    </td>
                    <td data-label="<?= esc(lang('Portal.col_complaint_status')) ?>">
                        <span class="jh-status is-pending"><?= esc($row['status'] ?: '—') ?></span>
                    </td>
                    <td data-label="<?= esc(lang('Portal.col_submission_date')) ?>"
                        data-order="<?= esc($row['submission_sort'] ?? '') ?>">
                        <?= esc($row['submission_date'] ?: '—') ?>
                    </td>
                    <td data-label="<?= esc(lang('Portal.col_created_date')) ?>"
                        data-order="<?= esc($row['created_sort'] ?? '') ?>">
                        <?= esc($row['created_at'] ?: '—') ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
