<?= $this->extend('layouts/backoffice') ?>

<?= $this->section('content') ?>

<?php
$icon = static function (string $name): string {
    $map = [
        'grid' => '<svg viewBox="0 0 24 24"><path d="M4 4h7v7H4V4Zm9 0h7v7h-7V4ZM4 13h7v7H4v-7Zm9 0h7v7h-7v-7Z" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>',
        'people' => '<svg viewBox="0 0 24 24"><path d="M8.5 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm9 1a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5ZM3.5 19a5 5 0 0 1 10 0m2-1a4 4 0 0 1 5 0" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
        'building2' => '<svg viewBox="0 0 24 24"><path d="M4 20h16M7 20V5h10v15M10 9h.01M14 9h.01M10 13h.01M14 13h.01" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
    ];

    return $map[$name] ?? $map['grid'];
};

$trafficLabel = static function (string $level): string {
    return match ($level) {
        'high'   => lang('Backoffice.high_traffic'),
        'medium' => lang('Backoffice.medium_traffic'),
        default  => lang('Backoffice.low_traffic'),
    };
};
?>

<p class="bo-demo"><?= esc(lang('Backoffice.demo_notice')) ?></p>

<section class="bo-banner">
    <div class="bo-banner-ico" aria-hidden="true"><?= $icon('grid') ?></div>
    <div>
        <h1><?= esc(lang('Backoffice.portal_title')) ?></h1>
        <p><?= esc(lang('Backoffice.portal_lead')) ?></p>
    </div>
</section>

<section class="bo-lifecycle" aria-label="<?= esc(lang('Backoffice.lifecycle_title')) ?>">
    <h2><?= esc(lang('Backoffice.lifecycle_title')) ?></h2>
    <div class="bo-lifecycle-track">
        <span>1. <?= esc(lang('Backoffice.svc_reception_title')) ?></span>
        <span>2. <?= esc(lang('Backoffice.svc_assign_title')) ?></span>
        <span>3. <?= esc(lang('Backoffice.svc_hearing_title')) ?></span>
        <span>4. <?= esc(lang('Backoffice.svc_judgment_title')) ?></span>
        <span>5. <?= esc(lang('Backoffice.svc_appeal_title')) ?></span>
    </div>
</section>

<div class="bo-tabs" role="tablist">
    <a class="bo-tab <?= ($tab ?? 'court') === 'court' ? 'is-active' : '' ?>" href="<?= site_url('backoffice?tab=court') ?>">
        <span class="bo-ico"><?= $icon('people') ?></span>
        <?= esc(lang('Backoffice.tab_court')) ?>
    </a>
    <a class="bo-tab <?= ($tab ?? '') === 'admin' ? 'is-active' : '' ?>" href="<?= site_url('backoffice?tab=admin') ?>">
        <span class="bo-ico"><?= $icon('building2') ?></span>
        <?= esc(lang('Backoffice.tab_admin')) ?>
    </a>
</div>

<article class="bo-panel">
    <div class="bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" data-order-col="0" data-order-dir="asc">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.col_service')) ?></th>
                    <th><?= esc(lang('Backoffice.col_description')) ?></th>
                    <th><?= esc(lang('Backoffice.col_pending')) ?></th>
                    <th><?= esc(lang('Backoffice.col_users')) ?></th>
                    <th><?= esc(lang('Backoffice.col_activity')) ?></th>
                    <th><?= esc(lang('Backoffice.col_rating')) ?></th>
                    <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td><strong><?= esc($service['title']) ?></strong></td>
                        <td><?= esc($service['desc']) ?></td>
                        <td data-order="<?= esc((string) $service['pending']) ?>"><?= esc((string) $service['pending']) ?></td>
                        <td data-order="<?= esc($service['users']) ?>"><?= esc($service['users']) ?></td>
                        <td>
                            <span class="bo-traffic is-<?= esc($service['traffic']) ?>"><?= esc($trafficLabel($service['traffic'])) ?></span>
                        </td>
                        <td data-order="<?= esc($service['rating']) ?>"><?= esc($service['rating']) ?></td>
                        <td>
                            <a class="bo-access" href="<?= site_url('backoffice/module/' . $service['key']) ?>">
                                <?= esc(lang('Backoffice.access')) ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</article>

<?= $this->endSection() ?>
