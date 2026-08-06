<?php
/**
 * Details section card with icon and two-column field grid.
 *
 * @var string $title
 * @var string $icon  Bootstrap Icons class without "bi " prefix, e.g. "person"
 * @var list<array{label:string,value:string,html?:bool,full?:bool}> $fields
 * @var string|null $body  Optional custom HTML below fields
 * @var string|null $class Extra CSS classes on the card
 */
$title = $title ?? '';
$icon  = $icon ?? 'info-circle';
$fields = $fields ?? [];
$body  = $body ?? null;
$class = trim('card bo-detail-card ' . ($class ?? ''));
?>

<div class="<?= esc($class, 'attr') ?>">
    <div class="card-header bo-detail-card-header">
        <span class="bo-detail-card-icon" aria-hidden="true">
            <i class="bi bi-<?= esc($icon, 'attr') ?>"></i>
        </span>
        <h2 class="bo-detail-card-title"><?= esc($title) ?></h2>
    </div>
    <div class="card-body">
        <?php if ($fields): ?>
            <dl class="bo-detail-fields">
                <?php foreach ($fields as $field): ?>
                    <?php
                    $full  = ! empty($field['full']);
                    $isHtml = ! empty($field['html']);
                    $value = $field['value'] ?? '—';
                    ?>
                    <div class="bo-detail-field<?= $full ? ' is-full' : '' ?>">
                        <dt><?= esc($field['label'] ?? '') ?></dt>
                        <dd><?= $isHtml ? $value : esc((string) $value) ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        <?php endif; ?>
        <?php if ($body !== null && $body !== ''): ?>
            <div class="bo-detail-card-body<?= $fields ? ' mt-3' : '' ?>"><?= $body ?></div>
        <?php endif; ?>
    </div>
</div>
