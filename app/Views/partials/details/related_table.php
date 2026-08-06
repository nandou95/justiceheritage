<?php
/**
 * Related records table inside a Details card.
 *
 * @var string $title
 * @var string $icon
 * @var string $tableId
 * @var list<string> $headers
 * @var list<list<string>> $rows  Cell HTML already escaped by caller when needed; plain text is escaped
 * @var list<list<array{html?:bool,value:string}>>|null $richRows  Optional rich cells
 * @var bool $searchable
 * @var int $pageLength
 */
$title      = $title ?? '';
$icon       = $icon ?? 'table';
$tableId    = $tableId ?? ('bo-rel-' . bin2hex(random_bytes(3)));
$headers    = $headers ?? [];
$rows       = $rows ?? [];
$richRows   = $richRows ?? null;
$searchable = $searchable ?? true;
$pageLength = $pageLength ?? 5;
?>

<div class="card bo-detail-card bo-detail-related">
    <div class="card-header bo-detail-card-header">
        <span class="bo-detail-card-icon" aria-hidden="true">
            <i class="bi bi-<?= esc($icon, 'attr') ?>"></i>
        </span>
        <h2 class="bo-detail-card-title"><?= esc($title) ?></h2>
        <?php if ($searchable): ?>
            <label class="bo-table-search ms-auto">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" class="form-control form-control-sm" id="<?= esc($tableId, 'attr') ?>-search"
                       placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>"
                       aria-label="<?= esc(lang('Backoffice.search_placeholder')) ?>">
            </label>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive bo-table-wrap mb-0">
            <table class="table table-hover bo-table jh-datatable w-100 mb-0" id="<?= esc($tableId, 'attr') ?>"
                   data-page-length="<?= (int) $pageLength ?>" data-dom="lrtip">
                <thead>
                    <tr>
                        <?php foreach ($headers as $h): ?>
                            <th><?= esc($h) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($richRows !== null): ?>
                        <?php foreach ($richRows as $row): ?>
                            <tr>
                                <?php foreach ($row as $cell): ?>
                                    <td><?= ! empty($cell['html']) ? ($cell['value'] ?? '') : esc((string) ($cell['value'] ?? '')) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <?php foreach ($row as $cell): ?>
                                    <td><?= esc((string) $cell) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
