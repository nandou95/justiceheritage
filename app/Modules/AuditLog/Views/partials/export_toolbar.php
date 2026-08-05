<?php
/**
 * Export / print toolbar for list tables.
 *
 * Expects:
 * - $tableId (string)
 * - $filename (string) optional export basename
 */
$tableId  = $tableId ?? '';
$filename = $filename ?? 'export';
?>
<div class="bo-export-actions" data-bo-export data-table="<?= esc($tableId, 'attr') ?>" data-filename="<?= esc($filename, 'attr') ?>">
    <button type="button" class="btn btn-bo-secondary btn-sm" data-export="excel" title="<?= esc(lang('Backoffice.export_excel'), 'attr') ?>">
        <i class="bi bi-file-earmark-excel" aria-hidden="true"></i>
        <span><?= esc(lang('Backoffice.export_excel')) ?></span>
    </button>
    <button type="button" class="btn btn-bo-secondary btn-sm" data-export="pdf" title="<?= esc(lang('Backoffice.export_pdf'), 'attr') ?>">
        <i class="bi bi-file-earmark-pdf" aria-hidden="true"></i>
        <span><?= esc(lang('Backoffice.export_pdf')) ?></span>
    </button>
    <button type="button" class="btn btn-bo-secondary btn-sm" data-export="print" title="<?= esc(lang('Backoffice.export_print'), 'attr') ?>">
        <i class="bi bi-printer" aria-hidden="true"></i>
        <span><?= esc(lang('Backoffice.export_print')) ?></span>
    </button>
</div>
