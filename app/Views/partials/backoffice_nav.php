<?php
/**
 * Back Office collapsible sidebar navigation.
 *
 * Expects:
 * - $active (string): current menu/submenu key
 * - $icon (callable): icon renderer
 */
$active = $active ?? 'dashboard';

$menu = [
    [
        'key'   => 'dashboard',
        'label' => lang('Backoffice.nav_dashboard'),
        'icon'  => 'grid',
        'url'   => site_url('backoffice'),
    ],
    [
        'key'      => 'administration',
        'label'    => lang('Backoffice.nav_administration'),
        'icon'     => 'shield',
        'children' => [
            ['key' => 'users', 'label' => lang('Backoffice.nav_users'), 'icon' => 'users', 'url' => site_url('backoffice/module/users')],
            ['key' => 'roles', 'label' => lang('Backoffice.nav_roles'), 'icon' => 'badge', 'url' => site_url('backoffice/module/roles')],
            ['key' => 'permissions', 'label' => lang('Backoffice.nav_permissions'), 'icon' => 'lock', 'url' => site_url('backoffice/module/permissions')],
        ],
    ],
    [
        'key'      => 'court-jurisdiction',
        'label'    => lang('Backoffice.nav_court_jurisdiction'),
        'icon'     => 'map',
        'children' => [
            ['key' => 'court-jurisdictions', 'label' => lang('Backoffice.nav_court_jurisdictions'), 'icon' => 'building', 'url' => site_url('backoffice/module/court-jurisdictions')],
            ['key' => 'court-jurisdiction-config', 'label' => lang('Backoffice.nav_court_jurisdiction_config'), 'icon' => 'sliders', 'url' => site_url('backoffice/module/court-jurisdiction-config')],
            ['key' => 'jurisdiction-levels', 'label' => lang('Backoffice.nav_jurisdiction_levels'), 'icon' => 'layers', 'url' => site_url('backoffice/module/jurisdiction-levels')],
            ['key' => 'jurisdiction-level-config', 'label' => lang('Backoffice.nav_jurisdiction_level_config'), 'icon' => 'sliders', 'url' => site_url('backoffice/module/jurisdiction-level-config')],
        ],
    ],
    [
        'key'   => 'people',
        'label' => lang('Backoffice.nav_people'),
        'icon'  => 'people',
        'url'   => site_url('backoffice/module/people'),
    ],
    [
        'key'      => 'complaints',
        'label'    => lang('Backoffice.nav_complaints'),
        'icon'     => 'inbox',
        'children' => [
            ['key' => 'complaint-stages', 'label' => lang('Backoffice.nav_complaint_stages'), 'icon' => 'steps', 'url' => site_url('backoffice/module/complaint-stages')],
            ['key' => 'complaint-stage-config', 'label' => lang('Backoffice.nav_complaint_stage_config'), 'icon' => 'sliders', 'url' => site_url('backoffice/module/complaint-stage-config')],
            ['key' => 'complaint-statuses', 'label' => lang('Backoffice.nav_complaint_statuses'), 'icon' => 'status', 'url' => site_url('backoffice/module/complaint-statuses')],
            ['key' => 'document-types', 'label' => lang('Backoffice.nav_document_types'), 'icon' => 'file', 'url' => site_url('backoffice/module/document-types')],
            ['key' => 'complaints-list', 'label' => lang('Backoffice.nav_complaints_list'), 'icon' => 'folder', 'url' => site_url('backoffice/module/complaints-list')],
        ],
    ],
    [
        'key'      => 'appeals',
        'label'    => lang('Backoffice.nav_appeals'),
        'icon'     => 'appeal',
        'children' => [
            ['key' => 'provincial-appeals', 'label' => lang('Backoffice.nav_provincial_appeals'), 'icon' => 'appeal', 'url' => site_url('backoffice/module/provincial-appeals')],
            ['key' => 'regional-appeals', 'label' => lang('Backoffice.nav_regional_appeals'), 'icon' => 'appeal', 'url' => site_url('backoffice/module/regional-appeals')],
            ['key' => 'ministry-appeals', 'label' => lang('Backoffice.nav_ministry_appeals'), 'icon' => 'building', 'url' => site_url('backoffice/module/ministry-appeals')],
        ],
    ],
    [
        'key'      => 'summons',
        'label'    => lang('Backoffice.nav_summons'),
        'icon'     => 'mail',
        'children' => [
            ['key' => 'summons-list', 'label' => lang('Backoffice.nav_summons_list'), 'icon' => 'mail', 'url' => site_url('backoffice/module/summons-list')],
            ['key' => 'summons-statuses', 'label' => lang('Backoffice.nav_summons_statuses'), 'icon' => 'status', 'url' => site_url('backoffice/module/summons-statuses')],
            ['key' => 'summons-communal', 'label' => lang('Backoffice.nav_summons_communal'), 'icon' => 'building', 'url' => site_url('backoffice/module/summons-communal')],
            ['key' => 'summons-provincial', 'label' => lang('Backoffice.nav_summons_provincial'), 'icon' => 'building', 'url' => site_url('backoffice/module/summons-provincial')],
            ['key' => 'summons-ministry', 'label' => lang('Backoffice.nav_summons_ministry'), 'icon' => 'building', 'url' => site_url('backoffice/module/summons-ministry')],
        ],
    ],
    [
        'key'      => 'hearings',
        'label'    => lang('Backoffice.nav_hearings'),
        'icon'     => 'calendar',
        'children' => [
            ['key' => 'hearings-list', 'label' => lang('Backoffice.nav_hearings_list'), 'icon' => 'calendar', 'url' => site_url('backoffice/module/hearings-list')],
            ['key' => 'hearing-statuses', 'label' => lang('Backoffice.nav_hearing_statuses'), 'icon' => 'status', 'url' => site_url('backoffice/module/hearing-statuses')],
            ['key' => 'hearings-communal', 'label' => lang('Backoffice.nav_hearings_communal'), 'icon' => 'building', 'url' => site_url('backoffice/module/hearings-communal')],
            ['key' => 'hearings-provincial', 'label' => lang('Backoffice.nav_hearings_provincial'), 'icon' => 'building', 'url' => site_url('backoffice/module/hearings-provincial')],
            ['key' => 'hearings-ministry', 'label' => lang('Backoffice.nav_hearings_ministry'), 'icon' => 'building', 'url' => site_url('backoffice/module/hearings-ministry')],
        ],
    ],
    [
        'key'      => 'verdicts',
        'label'    => lang('Backoffice.nav_verdicts'),
        'icon'     => 'scale',
        'children' => [
            ['key' => 'verdict-types', 'label' => lang('Backoffice.nav_verdict_types'), 'icon' => 'list', 'url' => site_url('backoffice/module/verdict-types')],
            ['key' => 'verdicts-list', 'label' => lang('Backoffice.nav_verdicts_list'), 'icon' => 'scale', 'url' => site_url('backoffice/module/verdicts-list')],
            ['key' => 'verdicts-communal', 'label' => lang('Backoffice.nav_verdicts_communal'), 'icon' => 'building', 'url' => site_url('backoffice/module/verdicts-communal')],
            ['key' => 'verdicts-provincial', 'label' => lang('Backoffice.nav_verdicts_provincial'), 'icon' => 'building', 'url' => site_url('backoffice/module/verdicts-provincial')],
            ['key' => 'verdicts-ministry', 'label' => lang('Backoffice.nav_verdicts_ministry'), 'icon' => 'building', 'url' => site_url('backoffice/module/verdicts-ministry')],
        ],
    ],
    [
        'key'      => 'case-transfers',
        'label'    => lang('Backoffice.nav_case_transfers'),
        'icon'     => 'transfer',
        'children' => [
            ['key' => 'transfer-statuses', 'label' => lang('Backoffice.nav_transfer_statuses'), 'icon' => 'status', 'url' => site_url('backoffice/module/transfer-statuses')],
            ['key' => 'case-transfers-list', 'label' => lang('Backoffice.nav_case_transfers_list'), 'icon' => 'transfer', 'url' => site_url('backoffice/module/case-transfers-list')],
        ],
    ],
    [
        'key'   => 'notifications',
        'label' => lang('Backoffice.nav_notifications'),
        'icon'  => 'bell',
        'url'   => site_url('backoffice/module/notifications'),
    ],
    [
        'key'   => 'logs',
        'label' => lang('Backoffice.nav_logs'),
        'icon'  => 'list',
        'url'   => site_url('backoffice/module/logs'),
    ],
];

$childActive = static function (array $children) use ($active): bool {
    foreach ($children as $child) {
        if (($child['key'] ?? '') === $active) {
            return true;
        }
    }

    return false;
};
?>

<nav class="bo-nav" aria-label="<?= esc(lang('Backoffice.app_name')) ?>">
    <?php foreach ($menu as $item): ?>
        <?php if (! empty($item['children'])): ?>
            <?php
            $isGroupActive = $childActive($item['children']);
            $isOpen = $isGroupActive;
            $panelId = 'bo-nav-panel-' . $item['key'];
            ?>
            <div class="bo-nav-item<?= $isOpen ? ' is-open' : '' ?><?= $isGroupActive ? ' has-active' : '' ?>" data-bo-nav-item>
                <button
                    class="bo-nav-toggle<?= $isGroupActive ? ' is-active' : '' ?>"
                    type="button"
                    data-bo-nav-toggle
                    aria-expanded="<?= $isOpen ? 'true' : 'false' ?>"
                    aria-controls="<?= esc($panelId) ?>"
                >
                    <span class="bo-ico" aria-hidden="true"><?= $icon($item['icon']) ?></span>
                    <span class="bo-nav-label"><?= esc($item['label']) ?></span>
                    <span class="bo-chev" aria-hidden="true"><?= $icon('chevron') ?></span>
                </button>
                <div class="bo-nav-sub" id="<?= esc($panelId) ?>"<?= $isOpen ? '' : ' hidden' ?>>
                    <?php foreach ($item['children'] as $child): ?>
                        <a
                            class="<?= $active === $child['key'] ? 'is-active' : '' ?>"
                            href="<?= esc($child['url']) ?>"
                            <?= $active === $child['key'] ? 'aria-current="page"' : '' ?>
                        >
                            <span class="bo-ico" aria-hidden="true"><?= $icon($child['icon']) ?></span>
                            <span class="bo-nav-label"><?= esc($child['label']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <a
                class="bo-nav-link<?= $active === $item['key'] ? ' is-active' : '' ?>"
                href="<?= esc($item['url']) ?>"
                <?= $active === $item['key'] ? 'aria-current="page"' : '' ?>
            >
                <span class="bo-ico" aria-hidden="true"><?= $icon($item['icon']) ?></span>
                <span class="bo-nav-label"><?= esc($item['label']) ?></span>
            </a>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>
