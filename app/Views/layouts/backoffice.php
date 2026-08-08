<!DOCTYPE html>
<html lang="<?= esc(service('request')->getLocale()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? lang('Backoffice.app_name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= public_asset('assets/vendor/bootstrap/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= public_asset('assets/vendor/datatables/dataTables.bootstrap5.min.css') ?>">
    <link rel="stylesheet" href="<?= public_asset('assets/css/backoffice.css') ?>">
    <?= $this->renderSection('styles') ?>
</head>
<body class="bo-body">
    <?php
    $locale = service('request')->getLocale();
    $currentUserId = (int) (session('backoffice_user_id') ?? 0);
    $topbarProfile = null;
    $unreadCount = 0;
    $unreadNotifications = [];

    if ($currentUserId > 0) {
        try {
            $topbarProfile = (new \Modules\Administration\Models\UtilisateurModel())->findWithRelations($currentUserId);
        } catch (Throwable $e) {
            $topbarProfile = null;
        }
        try {
            $inbox = new \Modules\Notification\Services\InboxNotificationService();
            $unreadCount = $inbox->unreadCount($currentUserId);
            $unreadNotifications = $inbox->unreadForUser($currentUserId, 8);
        } catch (Throwable $e) {
            $unreadCount = 0;
            $unreadNotifications = [];
        }
    }

    $sessionUser = session('backoffice_user');
    $displayName = '';
    if (is_array($topbarProfile)) {
        $displayName = trim(($topbarProfile['prenom_utilisateur'] ?? '') . ' ' . ($topbarProfile['nom_utilisateur'] ?? ''));
    }
    if ($displayName === '' && is_array($sessionUser)) {
        $displayName = (string) ($sessionUser['name'] ?? '');
    }
    if ($displayName === '') {
        $displayName = (string) (($user['name'] ?? '') ?: lang('Backoffice.user_sample'));
    }
    $displayRole = is_array($topbarProfile)
        ? (string) ($topbarProfile['libelle_profil'] ?? '')
        : (string) (($sessionUser['email'] ?? '') ?: ($user['role'] ?? ''));
    $statusLabel = (string) ($topbarProfile['desc_statut_compte'] ?? '—');
    $statusActive = stripos($statusLabel, 'actif') !== false || stripos($statusLabel, 'active') !== false;
    $fmtTopbarDate = static function ($value, string $format = 'd/m/Y H:i'): string {
        $value = trim((string) $value);
        if ($value === '') {
            return '—';
        }
        $ts = strtotime($value);

        return $ts ? date($format, $ts) : $value;
    };
    $topbarInitials = static function (string $name): string {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        $letters = '';
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            $letters .= mb_strtoupper(mb_substr($part, 0, 1));
            if (mb_strlen($letters) >= 2) {
                break;
            }
        }

        return $letters !== '' ? $letters : 'U';
    };
    $initial = $topbarInitials($displayName);
    $active = $active ?? 'dashboard';
    $icon = static function (string $name): string {
        $map = [
            'grid' => '<svg viewBox="0 0 24 24"><path d="M4 4h7v7H4V4Zm9 0h7v7h-7V4ZM4 13h7v7H4v-7Zm9 0h7v7h-7v-7Z" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>',
            'inbox' => '<svg viewBox="0 0 24 24"><path d="M4 13h5l1.5 2h3L15 13h5v5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-5Zm0 0 2.5-7h11L20 13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>',
            'mail' => '<svg viewBox="0 0 24 24"><path d="M4 7.5 12 13l8-5.5M5 19h14a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1Z" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>',
            'calendar' => '<svg viewBox="0 0 24 24"><path d="M7 4v3M17 4v3M4 9h16M6 6h12a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>',
            'users' => '<svg viewBox="0 0 24 24"><path d="M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1M9.5 10a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm7.5 1a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Zm1.5 8v-1a3.5 3.5 0 0 0-2.2-3.2" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
            'folder' => '<svg viewBox="0 0 24 24"><path d="M3 8.5V18a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V10a2 2 0 0 0-2-2h-7l-2-2H5a2 2 0 0 0-2 2.5Z" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>',
            'scale' => '<svg viewBox="0 0 24 24"><path d="M12 3v18M8 21h8M6 8h12M7.5 8 5 14h5L7.5 8Zm9 0L14 14h5l-2.5-6Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>',
            'appeal' => '<svg viewBox="0 0 24 24"><path d="m8 14 4-8 4 8M6 18h12" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
            'building' => '<svg viewBox="0 0 24 24"><path d="M4 20h16M6 20V6l6-2 6 2v14M9 10h.01M12 10h.01M15 10h.01M9 14h.01M12 14h.01M15 14h.01" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
            'shield' => '<svg viewBox="0 0 24 24"><path d="M12 3 5 6v5c0 4.5 3 7.8 7 9 4-1.2 7-4.5 7-9V6l-7-3Z" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>',
            'map' => '<svg viewBox="0 0 24 24"><path d="m9 4-5 2v14l5-2 6 2 5-2V4l-5 2-6-2Zm0 0v14m6-12v14" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>',
            'list' => '<svg viewBox="0 0 24 24"><path d="M8 7h12M8 12h12M8 17h12M4 7h.01M4 12h.01M4 17h.01" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
            'bell' => '<svg viewBox="0 0 24 24"><path d="M15 18a3 3 0 0 1-6 0m9 0H6a2 2 0 0 1-1.6-3.2C5.5 13.2 7 11.8 7 9a5 5 0 1 1 10 0c0 2.8 1.5 4.2 2.6 5.8A2 2 0 0 1 18 18Z" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>',
            'help' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.4 2.3c-.8.4-1.4 1-1.4 1.9V14m0 3h.01" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
            'search' => '<svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="6.5" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="m16 16 4 4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
            'logout' => '<svg viewBox="0 0 24 24"><path d="M10 7V5a2 2 0 0 1 2-2h7v18h-7a2 2 0 0 1-2-2v-2M14 12H3m0 0 3-3m-3 3 3 3" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'chevron' => '<svg viewBox="0 0 24 24"><path d="m9 6 6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
            'back' => '<svg viewBox="0 0 24 24"><path d="M15 6 9 12l6 6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
            'external' => '<svg viewBox="0 0 24 24"><path d="M14 5h5v5M19 5l-9 9M10 5H6a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
            'star' => '<svg viewBox="0 0 24 24"><path d="m12 3.5 2.4 4.9 5.4.8-3.9 3.8.9 5.4L12 16l-4.8 2.4.9-5.4L4.2 9.2l5.4-.8L12 3.5Z" fill="currentColor"/></svg>',
            'people' => '<svg viewBox="0 0 24 24"><path d="M8.5 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm9 1a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5ZM3.5 19a5 5 0 0 1 10 0m2-1a4 4 0 0 1 5 0" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
            'badge' => '<svg viewBox="0 0 24 24"><path d="M12 3 4.5 6.5v4.8c0 4.7 3.2 8.2 7.5 9.7 4.3-1.5 7.5-5 7.5-9.7V6.5L12 3Z" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="m9 12 2 2 4-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'lock' => '<svg viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="10" rx="2" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M8 11V8a4 4 0 0 1 8 0v3" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
            'sliders' => '<svg viewBox="0 0 24 24"><path d="M4 7h10M18 7h2M4 17h2M10 17h10M14 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM8 21a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
            'layers' => '<svg viewBox="0 0 24 24"><path d="m12 3 9 5-9 5-9-5 9-5Zm0 8 9 5-9 5-9-5 9-5Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>',
            'steps' => '<svg viewBox="0 0 24 24"><path d="M4 18h6v-4H4v4Zm5-7h6V7H9v4Zm5-7h6V3h-6v1Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>',
            'status' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="3" fill="currentColor"/></svg>',
            'file' => '<svg viewBox="0 0 24 24"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M14 3v5h5M9 13h6M9 17h4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
            'transfer' => '<svg viewBox="0 0 24 24"><path d="M7 7h11m0 0-3-3m3 3-3 3M17 17H6m0 0 3-3m-3 3 3 3" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        ];

        return $map[$name] ?? $map['grid'];
    };
    ?>

    <a class="skip-link" href="#bo-main">Skip</a>

    <div class="bo-shell">
        <div class="bo-nav-backdrop" data-bo-backdrop hidden></div>

        <aside id="bo-sidebar-nav" class="bo-sidebar" data-bo-nav>
            <div class="bo-sidebar-head">
                <a class="bo-brand" href="<?= site_url('backoffice') ?>">
                    <span class="bo-brand-mark" aria-hidden="true"></span>
                    <span>
                        <strong>JusticeHeritage</strong>
                        <small><?= esc(lang('Backoffice.app_tag')) ?></small>
                    </span>
                </a>
                <button class="bo-nav-close" type="button" data-bo-close aria-label="<?= esc(lang('Backoffice.close_menu')) ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <?= view('partials/backoffice_nav', ['active' => $active, 'icon' => $icon]) ?>

            <div class="bo-sidebar-foot">
                <a class="bo-logout" href="<?= site_url('backoffice/logout') ?>">
                    <span class="bo-ico"><?= $icon('logout') ?></span>
                    <?= esc(lang('Backoffice.logout')) ?>
                </a>
            </div>
        </aside>

        <div class="bo-mainwrap">
            <header class="bo-topbar">
                <div class="bo-top-left">
                    <button class="bo-menu-btn" type="button" data-bo-toggle aria-expanded="false" aria-controls="bo-sidebar-nav">
                        <?= esc(lang('Backoffice.menu')) ?>
                    </button>
                </div>

                <label class="bo-search">
                    <span class="bo-ico" aria-hidden="true"><?= $icon('search') ?></span>
                    <input type="search" placeholder="<?= esc(lang('Backoffice.search_app'), 'attr') ?>" aria-label="<?= esc(lang('Backoffice.search_app')) ?>">
                </label>

                <div class="bo-top-right">
                    <nav class="bo-lang" aria-label="Language">
                        <a href="<?= site_url('lang/en') ?>" <?= $locale === 'en' ? 'aria-current="true"' : '' ?>>EN</a>
                        <a href="<?= site_url('lang/fr') ?>" <?= $locale === 'fr' ? 'aria-current="true"' : '' ?>>FR</a>
                    </nav>

                    <div class="dropdown bo-top-dropdown" data-bo-notif-dropdown>
                        <button
                            class="bo-icon-btn dropdown-toggle"
                            type="button"
                            id="boNotifDropdown"
                            data-bs-toggle="dropdown"
                            data-bs-auto-close="outside"
                            aria-expanded="false"
                            aria-label="<?= esc(lang('Backoffice.notifications'), 'attr') ?>"
                        >
                            <?= $icon('bell') ?>
                            <span class="bo-badge<?= $unreadCount > 0 ? '' : ' d-none' ?>" data-bo-notif-badge aria-hidden="true"><?= (int) $unreadCount ?></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end bo-notif-menu" aria-labelledby="boNotifDropdown">
                            <div class="bo-notif-menu-head">
                                <strong><?= esc(lang('Backoffice.inbox_unread_title')) ?></strong>
                                <span class="bo-notif-count-pill<?= $unreadCount > 0 ? '' : ' d-none is-empty' ?>" data-bo-notif-count-label><?= (int) $unreadCount ?></span>
                            </div>
                            <div class="bo-notif-list" data-bo-notif-list>
                                <?php if ($unreadNotifications === []): ?>
                                    <div class="bo-notif-empty" data-bo-notif-empty><?= esc(lang('Backoffice.inbox_no_unread')) ?></div>
                                <?php else: ?>
                                    <?php foreach ($unreadNotifications as $n): ?>
                                        <a class="bo-notif-item" href="<?= esc($n['url']) ?>" data-bo-notif-item data-id="<?= (int) $n['id'] ?>">
                                            <div class="bo-notif-item-top">
                                                <strong><?= esc($n['subject']) ?></strong>
                                                <span class="bo-notif-channel"><?= esc($n['channel']) ?></span>
                                            </div>
                                            <p><?= esc($n['preview']) ?></p>
                                            <small><?= esc($n['created_fmt']) ?></small>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <div class="bo-notif-menu-foot">
                                <a href="<?= site_url('backoffice/my/notifications') ?>"><?= esc(lang('Backoffice.inbox_view_all')) ?></a>
                            </div>
                        </div>
                    </div>

                    <div class="dropdown bo-top-dropdown" data-bo-user-dropdown>
                        <button
                            class="bo-userchip dropdown-toggle"
                            type="button"
                            id="boUserDropdown"
                            data-bs-toggle="dropdown"
                            data-bs-auto-close="outside"
                            aria-expanded="false"
                            aria-label="<?= esc(lang('Backoffice.account_profile_title'), 'attr') ?>"
                        >
                            <div class="text-start">
                                <strong><?= esc($displayName) ?></strong>
                                <small><?= esc($displayRole !== '' ? $displayRole : '—') ?></small>
                            </div>
                            <span class="bo-avatar" aria-hidden="true"><?= esc($initial) ?></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end bo-user-panel" aria-labelledby="boUserDropdown">
                            <div class="bo-user-panel-hero">
                                <span class="bo-avatar bo-avatar-xl" aria-hidden="true"><?= esc($initial) ?></span>
                                <div class="bo-user-panel-identity">
                                    <strong><?= esc($displayName) ?></strong>
                                    <div class="bo-user-panel-badges">
                                        <span class="bo-role-chip"><?= esc($displayRole !== '' ? $displayRole : '—') ?></span>
                                        <span class="bo-status-pill <?= $statusActive ? 'is-active' : 'is-inactive' ?>"><?= esc($statusLabel) ?></span>
                                    </div>
                                    <p class="bo-user-panel-court">
                                        <i class="bi bi-building" aria-hidden="true"></i>
                                        <?= esc($topbarProfile['nom_juridiction'] ?? '—') ?>
                                    </p>
                                </div>
                            </div>

                            <div class="bo-user-panel-grid">
                                <section class="bo-user-panel-card">
                                    <h3><?= esc(lang('Backoffice.account_section_personal')) ?></h3>
                                    <dl>
                                        <div><dt><?= esc(lang('Backoffice.users_field_cni')) ?></dt><dd><?= esc($topbarProfile['numero_cni'] ?? '—') ?></dd></div>
                                        <div><dt><?= esc(lang('Backoffice.users_field_matricule')) ?></dt><dd><?= esc($topbarProfile['numero_matricule'] ?? '—') ?></dd></div>
                                        <div><dt><?= esc(lang('Backoffice.users_field_sex')) ?></dt><dd><?= esc($topbarProfile['description_sexe'] ?? '—') ?></dd></div>
                                        <div><dt><?= esc(lang('Backoffice.users_field_birth_date')) ?></dt><dd><?= esc($fmtTopbarDate($topbarProfile['date_naissance'] ?? '', 'd/m/Y')) ?></dd></div>
                                        <div><dt><?= esc(lang('Backoffice.users_field_phone')) ?></dt><dd><?= esc($topbarProfile['telephone'] ?? '—') ?></dd></div>
                                        <div><dt><?= esc(lang('Backoffice.users_field_email')) ?></dt><dd><?= esc($topbarProfile['email'] ?? '—') ?></dd></div>
                                    </dl>
                                </section>
                                <section class="bo-user-panel-card">
                                    <h3><?= esc(lang('Backoffice.account_section_professional')) ?></h3>
                                    <dl>
                                        <div><dt><?= esc(lang('Backoffice.users_field_profile')) ?></dt><dd><?= esc($displayRole !== '' ? $displayRole : '—') ?></dd></div>
                                        <div><dt><?= esc(lang('Backoffice.users_field_jurisdiction')) ?></dt><dd><?= esc($topbarProfile['nom_juridiction'] ?? '—') ?></dd></div>
                                        <div><dt><?= esc(lang('Backoffice.filter_jurisdiction_level')) ?></dt><dd><?= esc($topbarProfile['desc_niveau_juridiction'] ?? '—') ?></dd></div>
                                        <div><dt><?= esc(lang('Backoffice.account_created_at')) ?></dt><dd><?= esc($fmtTopbarDate($topbarProfile['created_at'] ?? '')) ?></dd></div>
                                        <div><dt><?= esc(lang('Backoffice.account_last_login')) ?></dt><dd><?= esc($fmtTopbarDate($topbarProfile['derniere_connexion'] ?? '')) ?></dd></div>
                                    </dl>
                                </section>
                            </div>

                            <div class="bo-user-panel-actions">
                                <a href="<?= site_url('backoffice/my/profile') ?>"><i class="bi bi-person" aria-hidden="true"></i> <?= esc(lang('Backoffice.account_my_profile')) ?></a>
                                <a href="<?= site_url('backoffice/my/profile/edit') ?>"><i class="bi bi-pencil-square" aria-hidden="true"></i> <?= esc(lang('Backoffice.account_edit_profile')) ?></a>
                                <a href="<?= site_url('backoffice/my/password') ?>"><i class="bi bi-shield-lock" aria-hidden="true"></i> <?= esc(lang('Backoffice.account_change_password')) ?></a>
                                <a href="<?= site_url('backoffice/my/notifications') ?>"><i class="bi bi-bell" aria-hidden="true"></i> <?= esc(lang('Backoffice.account_my_notifications')) ?></a>
                                <a class="is-danger" href="<?= site_url('backoffice/logout') ?>"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> <?= esc(lang('Backoffice.logout')) ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main id="bo-main" class="bo-content">
                <?= $this->renderSection('content') ?>
            </main>
        </div>
    </div>

    <div class="modal fade" id="boConfirmSaveModal" tabindex="-1" aria-labelledby="boConfirmSaveModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content bo-confirm-modal">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title fs-5" id="boConfirmSaveModalTitle"></h2>
                        <p class="bo-confirm-lead mb-0" id="boConfirmSaveModalLead"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc(lang('Backoffice.btn_cancel'), 'attr') ?>"></button>
                </div>
                <div class="modal-body" id="boConfirmSaveModalBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="boConfirmSaveBack">
                        <?= esc(lang('Backoffice.confirm_back_edit')) ?>
                    </button>
                    <button type="button" class="btn btn-bo-primary" id="boConfirmSaveSubmit"></button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.JH_DT = {
            language: {
                search: <?= json_encode(lang('Portal.dt_search')) ?>,
                lengthMenu: <?= json_encode(lang('Portal.dt_length')) ?>,
                info: <?= json_encode(lang('Portal.dt_info')) ?>,
                infoEmpty: <?= json_encode(lang('Portal.dt_info_empty')) ?>,
                infoFiltered: <?= json_encode(lang('Portal.dt_info_filtered')) ?>,
                zeroRecords: <?= json_encode(lang('Portal.dt_zero')) ?>,
                emptyTable: <?= json_encode(lang('Portal.dt_empty')) ?>,
                paginate: {
                    first: <?= json_encode(lang('Portal.dt_first')) ?>,
                    last: <?= json_encode(lang('Portal.dt_last')) ?>,
                    next: <?= json_encode(lang('Portal.dt_next')) ?>,
                    previous: <?= json_encode(lang('Portal.dt_prev')) ?>
                }
            }
        };
        window.JH_CONFIRM_I18N = {
            saveTitle: <?= json_encode(lang('Backoffice.confirm_save_title')) ?>,
            updateTitle: <?= json_encode(lang('Backoffice.confirm_update_title')) ?>,
            saveLead: <?= json_encode(lang('Backoffice.confirm_save_lead')) ?>,
            updateLead: <?= json_encode(lang('Backoffice.confirm_update_lead')) ?>,
            backEdit: <?= json_encode(lang('Backoffice.confirm_back_edit')) ?>,
            confirmSave: <?= json_encode(lang('Backoffice.confirm_and_save')) ?>,
            confirmUpdate: <?= json_encode(lang('Backoffice.confirm_and_update')) ?>,
            sectionDetails: <?= json_encode(lang('Backoffice.confirm_section_details')) ?>,
            emptyValue: <?= json_encode(lang('Backoffice.confirm_empty_value')) ?>,
            filesLabel: <?= json_encode(lang('Backoffice.confirm_files_label')) ?>,
            noFile: <?= json_encode(lang('Backoffice.confirm_no_file')) ?>,
            checkedCount: <?= json_encode(lang('Backoffice.confirm_checked_count')) ?>
        };
        window.JH_TOPBAR_I18N = {
            unreadUrl: <?= json_encode(site_url('backoffice/my/notifications/unread-json')) ?>,
            countUrl: <?= json_encode(site_url('backoffice/my/notifications/count-json')) ?>,
            markReadUrl: <?= json_encode(site_url('backoffice/my/notifications/__ID__/read')) ?>,
            viewAllUrl: <?= json_encode(site_url('backoffice/my/notifications')) ?>,
            noUnread: <?= json_encode(lang('Backoffice.inbox_no_unread')) ?>,
            csrfName: <?= json_encode(csrf_token()) ?>,
            csrfHash: <?= json_encode(csrf_hash()) ?>
        };
    </script>
    <script src="<?= public_asset('assets/vendor/jquery/jquery.min.js') ?>"></script>
    <script src="<?= public_asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= public_asset('assets/vendor/datatables/dataTables.min.js') ?>"></script>
    <script src="<?= public_asset('assets/vendor/datatables/dataTables.bootstrap5.min.js') ?>"></script>
    <script src="<?= public_asset('assets/js/backoffice.js') ?>"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
