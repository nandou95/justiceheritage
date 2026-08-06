<?php
/**
 * Back Office collapsible sidebar navigation.
 *
 * Expects:
 * - $active (string): current menu/submenu key
 * - $icon (callable): icon renderer
 *
 * Items without can_access() permission are omitted entirely.
 */
$active = $active ?? 'dashboard';

$menu = [
    [
        'key'      => 'dashboard',
        'label'    => lang('Backoffice.nav_dashboard'),
        'icon'     => 'grid',
        'children' => [
            ['key' => 'dash-executive', 'label' => lang('Backoffice.nav_dash_executive'), 'icon' => 'star', 'url' => site_url('backoffice/dashboards/executive'), 'permission' => 'backoffice/dashboards/executive'],
            ['key' => 'dash-complaints', 'label' => lang('Backoffice.nav_dash_complaints'), 'icon' => 'inbox', 'url' => site_url('backoffice/dashboards/complaints'), 'permission' => 'backoffice/dashboards/complaints'],
            ['key' => 'dash-complainants', 'label' => lang('Backoffice.nav_dash_complainants'), 'icon' => 'people', 'url' => site_url('backoffice/dashboards/complainants'), 'permission' => 'backoffice/dashboards/complainants'],
            ['key' => 'dash-appeals', 'label' => lang('Backoffice.nav_dash_appeals'), 'icon' => 'appeal', 'url' => site_url('backoffice/dashboards/appeals'), 'permission' => 'backoffice/dashboards/appeals'],
            ['key' => 'dash-summons', 'label' => lang('Backoffice.nav_dash_summons'), 'icon' => 'mail', 'url' => site_url('backoffice/dashboards/summons'), 'permission' => 'backoffice/dashboards/summons'],
            ['key' => 'dash-hearings', 'label' => lang('Backoffice.nav_dash_hearings'), 'icon' => 'calendar', 'url' => site_url('backoffice/dashboards/hearings'), 'permission' => 'backoffice/dashboards/hearings'],
            ['key' => 'dash-notifications', 'label' => lang('Backoffice.nav_dash_notifications'), 'icon' => 'bell', 'url' => site_url('backoffice/dashboards/notifications'), 'permission' => 'backoffice/dashboards/notifications'],
            ['key' => 'dash-court-jurisdictions', 'label' => lang('Backoffice.nav_dash_court_jurisdictions'), 'icon' => 'map', 'url' => site_url('backoffice/dashboards/courts'), 'permission' => 'backoffice/dashboards/courts'],
        ],
    ],
    [
        'key'      => 'administration',
        'label'    => lang('Backoffice.nav_administration'),
        'icon'     => 'shield',
        'children' => [
            ['key' => 'users', 'label' => lang('Backoffice.nav_users'), 'icon' => 'users', 'url' => site_url('backoffice/users'), 'permission' => 'backoffice/users'],
            ['key' => 'profiles', 'label' => lang('Backoffice.nav_profiles'), 'icon' => 'badge', 'url' => site_url('backoffice/profiles'), 'permission' => 'backoffice/profiles'],
            ['key' => 'permissions', 'label' => lang('Backoffice.nav_permissions'), 'icon' => 'lock', 'url' => site_url('backoffice/permissions'), 'permission' => 'backoffice/permissions'],
        ],
    ],
    [
        'key'      => 'court-jurisdiction',
        'label'    => lang('Backoffice.nav_court_jurisdiction'),
        'icon'     => 'map',
        'children' => [
            ['key' => 'court-jurisdictions', 'label' => lang('Backoffice.nav_court_jurisdictions'), 'icon' => 'building', 'url' => site_url('backoffice/court-jurisdictions'), 'permission' => 'backoffice/court-jurisdictions'],
            ['key' => 'court-jurisdiction-config', 'label' => lang('Backoffice.nav_court_jurisdiction_config'), 'icon' => 'sliders', 'url' => site_url('backoffice/court-jurisdiction-configs'), 'permission' => 'backoffice/court-jurisdiction-configs'],
            ['key' => 'jurisdiction-levels', 'label' => lang('Backoffice.nav_jurisdiction_levels'), 'icon' => 'layers', 'url' => site_url('backoffice/jurisdiction-levels'), 'permission' => 'backoffice/jurisdiction-levels'],
            ['key' => 'jurisdiction-level-config', 'label' => lang('Backoffice.nav_jurisdiction_level_config'), 'icon' => 'sliders', 'url' => site_url('backoffice/jurisdiction-level-configs'), 'permission' => 'backoffice/jurisdiction-level-configs'],
        ],
    ],
    [
        'key'        => 'people',
        'label'      => lang('Backoffice.nav_people'),
        'icon'       => 'people',
        'url'        => site_url('backoffice/people'),
        'permission' => 'backoffice/people',
    ],
    [
        'key'      => 'complaints',
        'label'    => lang('Backoffice.nav_complaints'),
        'icon'     => 'inbox',
        'children' => [
            ['key' => 'complaint-stages', 'label' => lang('Backoffice.nav_complaint_stages'), 'icon' => 'steps', 'url' => site_url('backoffice/complaint-stages'), 'permission' => 'backoffice/complaint-stages'],
            ['key' => 'complaint-stage-config', 'label' => lang('Backoffice.nav_complaint_stage_config'), 'icon' => 'sliders', 'url' => site_url('backoffice/complaint-stage-configs'), 'permission' => 'backoffice/complaint-stage-configs'],
            ['key' => 'complaint-statuses', 'label' => lang('Backoffice.nav_complaint_statuses'), 'icon' => 'status', 'url' => site_url('backoffice/complaint-statuses'), 'permission' => 'backoffice/complaint-statuses'],
            ['key' => 'document-types', 'label' => lang('Backoffice.nav_document_types'), 'icon' => 'file', 'url' => site_url('backoffice/document-types'), 'permission' => 'backoffice/document-types'],
            ['key' => 'complaints-list', 'label' => lang('Backoffice.nav_complaints_list'), 'icon' => 'folder', 'url' => site_url('backoffice/complaints'), 'permission' => 'backoffice/complaints'],
        ],
    ],
    [
        'key'        => 'appeals',
        'label'      => lang('Backoffice.nav_appeals'),
        'icon'       => 'appeal',
        'url'        => site_url('backoffice/appeals'),
        'permission' => 'backoffice/appeals',
    ],
    [
        'key'      => 'summons',
        'label'    => lang('Backoffice.nav_summons'),
        'icon'     => 'mail',
        'children' => [
            ['key' => 'summons-list', 'label' => lang('Backoffice.nav_summons_list'), 'icon' => 'mail', 'url' => site_url('backoffice/summons'), 'permission' => 'backoffice/summons'],
            ['key' => 'summons-statuses', 'label' => lang('Backoffice.nav_summons_statuses'), 'icon' => 'status', 'url' => site_url('backoffice/summons-statuses'), 'permission' => 'backoffice/summons-statuses'],
        ],
    ],
    [
        'key'      => 'hearings',
        'label'    => lang('Backoffice.nav_hearings'),
        'icon'     => 'calendar',
        'children' => [
            ['key' => 'hearings-list', 'label' => lang('Backoffice.nav_hearings_list'), 'icon' => 'calendar', 'url' => site_url('backoffice/hearings'), 'permission' => 'backoffice/hearings'],
            ['key' => 'hearing-statuses', 'label' => lang('Backoffice.nav_hearing_statuses'), 'icon' => 'status', 'url' => site_url('backoffice/hearing-statuses'), 'permission' => 'backoffice/hearing-statuses'],
        ],
    ],
    [
        'key'      => 'verdicts',
        'label'    => lang('Backoffice.nav_verdicts'),
        'icon'     => 'scale',
        'children' => [
            ['key' => 'verdict-types', 'label' => lang('Backoffice.nav_verdict_types'), 'icon' => 'list', 'url' => site_url('backoffice/verdict-types'), 'permission' => 'backoffice/verdict-types'],
            ['key' => 'verdicts-list', 'label' => lang('Backoffice.nav_verdicts_list'), 'icon' => 'scale', 'url' => site_url('backoffice/verdicts'), 'permission' => 'backoffice/verdicts'],
        ],
    ],
    [
        'key'      => 'case-transfers',
        'label'    => lang('Backoffice.nav_case_transfers'),
        'icon'     => 'transfer',
        'children' => [
            ['key' => 'transfer-statuses', 'label' => lang('Backoffice.nav_transfer_statuses'), 'icon' => 'status', 'url' => site_url('backoffice/transfer-statuses'), 'permission' => 'backoffice/transfer-statuses'],
            ['key' => 'case-transfers-list', 'label' => lang('Backoffice.nav_case_transfers_list'), 'icon' => 'transfer', 'url' => site_url('backoffice/transfers'), 'permission' => 'backoffice/transfers'],
        ],
    ],
    [
        'key'      => 'notifications',
        'label'    => lang('Backoffice.nav_notifications'),
        'icon'     => 'bell',
        'children' => [
            ['key' => 'ntf-complainants', 'label' => lang('Backoffice.nav_ntf_complainants'), 'icon' => 'people', 'url' => site_url('backoffice/notifications/complainants'), 'permission' => 'backoffice/notifications/complainants'],
            ['key' => 'ntf-users', 'label' => lang('Backoffice.nav_ntf_users'), 'icon' => 'users', 'url' => site_url('backoffice/notifications/users'), 'permission' => 'backoffice/notifications/users'],
        ],
    ],
    [
        'key'      => 'logs',
        'label'    => lang('Backoffice.nav_logs'),
        'icon'     => 'list',
        'children' => [
            ['key' => 'logs-complainants', 'label' => lang('Backoffice.nav_logs_complainants'), 'icon' => 'people', 'url' => site_url('backoffice/system-logs/complainants'), 'permission' => 'backoffice/system-logs/complainants'],
            ['key' => 'logs-users', 'label' => lang('Backoffice.nav_logs_users'), 'icon' => 'users', 'url' => site_url('backoffice/system-logs/users'), 'permission' => 'backoffice/system-logs/users'],
        ],
    ],
];

// Filter children / leaf items by permission.
$filtered = [];
foreach ($menu as $item) {
    if (! empty($item['children'])) {
        $children = [];
        foreach ($item['children'] as $child) {
            $perm = (string) ($child['permission'] ?? '');
            if ($perm === '' || can_access($perm)) {
                $children[] = $child;
            }
        }
        if ($children === []) {
            continue;
        }
        $item['children'] = $children;
        $filtered[]       = $item;
        continue;
    }

    $perm = (string) ($item['permission'] ?? '');
    if ($perm === '' || can_access($perm)) {
        $filtered[] = $item;
    }
}
$menu = $filtered;

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
            $isGroupActive = $childActive($item['children'])
                || ($item['key'] === 'dashboard' && $active === 'dashboard');
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
