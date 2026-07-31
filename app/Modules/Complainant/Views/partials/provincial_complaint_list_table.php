<?php
/**
 * Shared provincial complaint / appeal list table.
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
    <table class="table table-hover jh-table jh-datatable jh-complaint-list-table jh-complaint-list-table--provincial w-100"
           data-page-length="<?= (int) $pageLength ?>"
           data-order-col="8"
           data-order-dir="desc"
           data-length-change="<?= $pageLength <= 5 ? 'false' : 'true' ?>"
           data-empty-table="<?= esc($emptyMessage) ?>"
           data-zero-records="<?= esc($emptyMessage) ?>">
        <thead>
            <tr>
                <th><?= esc(lang('Portal.col_case_number')) ?></th>
                <th><?= esc(lang('Portal.col_subject')) ?></th>
                <th><?= esc(lang('Portal.col_description')) ?></th>
                <th><?= esc(lang('Portal.col_jurisdiction_name')) ?></th>
                <th><?= esc(lang('Portal.col_old_case_number')) ?></th>
                <th><?= esc(lang('Portal.col_old_description')) ?></th>
                <th><?= esc(lang('Portal.col_complaint_stage')) ?></th>
                <th><?= esc(lang('Portal.col_complaint_status')) ?></th>
                <th><?= esc(lang('Portal.col_filing_date')) ?></th>
                <th><?= esc(lang('Portal.col_insertion_date')) ?></th>
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
                    <td data-label="<?= esc(lang('Portal.col_jurisdiction_name')) ?>">
                        <?= esc($row['court_jurisdiction'] ?: '—') ?>
                    </td>
                    <td data-label="<?= esc(lang('Portal.col_old_case_number')) ?>">
                        <span class="jh-case-ref jh-case-ref--muted">
                            <?= esc(($row['old_case_number'] ?? '') !== '' ? $row['old_case_number'] : '—') ?>
                        </span>
                    </td>
                    <td data-label="<?= esc(lang('Portal.col_old_description')) ?>">
                        <span class="jh-cell-desc" title="<?= esc($row['old_description'] ?? '') ?>">
                            <?= esc(($row['old_description'] ?? '') !== '' ? $row['old_description'] : '—') ?>
                        </span>
                    </td>
                    <td data-label="<?= esc(lang('Portal.col_complaint_stage')) ?>">
                        <span class="jh-stage-badge"><?= esc($row['stage'] ?: '—') ?></span>
                    </td>
                    <td data-label="<?= esc(lang('Portal.col_complaint_status')) ?>">
                        <span class="jh-status is-pending"><?= esc($row['status'] ?: '—') ?></span>
                    </td>
                    <td data-label="<?= esc(lang('Portal.col_filing_date')) ?>"
                        data-order="<?= esc($row['submission_sort'] ?? '') ?>">
                        <?= esc($row['submission_date'] ?: '—') ?>
                    </td>
                    <td data-label="<?= esc(lang('Portal.col_insertion_date')) ?>"
                        data-order="<?= esc($row['created_sort'] ?? '') ?>">
                        <?= esc($row['created_at'] ?: '—') ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
