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
    <link rel="stylesheet" href="<?= public_asset('assets/vendor/datatables/dataTables.bootstrap5.min.css') ?>">
    <link rel="stylesheet" href="<?= public_asset('assets/css/backoffice.css') ?>">
</head>
<body class="bo-body">
    <?php
    $locale = service('request')->getLocale();
    $user   = $user ?? ['name' => 'Officer', 'role' => 'Staff'];
    $active = $active ?? 'portal';
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
        ];

        return $map[$name] ?? $map['grid'];
    };
    ?>

    <a class="skip-link" href="#bo-main">Skip</a>

    <div class="bo-shell">
        <aside class="bo-sidebar" data-bo-nav>
            <a class="bo-brand" href="<?= site_url('backoffice') ?>">
                <span class="bo-brand-mark" aria-hidden="true"></span>
                <span>
                    <strong>JusticeHeritage</strong>
                    <small><?= esc(lang('Backoffice.app_tag')) ?></small>
                </span>
            </a>

            <nav class="bo-nav" aria-label="<?= esc(lang('Backoffice.app_name')) ?>">
                <p class="bo-nav-group"><?= esc(lang('Backoffice.nav_digital')) ?></p>
                <a class="<?= $active === 'portal' ? 'is-active' : '' ?>" href="<?= site_url('backoffice') ?>">
                    <span class="bo-ico"><?= $icon('grid') ?></span>
                    <span><?= esc(lang('Backoffice.nav_portal')) ?></span>
                    <span class="bo-chev"><?= $icon('chevron') ?></span>
                </a>

                <p class="bo-nav-group"><?= esc(lang('Backoffice.nav_cases')) ?></p>
                <a href="<?= site_url('backoffice/module/reception') ?>">
                    <span class="bo-ico"><?= $icon('inbox') ?></span>
                    <span><?= esc(lang('Backoffice.nav_reception')) ?></span>
                    <span class="bo-chev"><?= $icon('chevron') ?></span>
                </a>
                <a href="<?= site_url('backoffice/module/hearing') ?>">
                    <span class="bo-ico"><?= $icon('calendar') ?></span>
                    <span><?= esc(lang('Backoffice.nav_hearings')) ?></span>
                    <span class="bo-chev"><?= $icon('chevron') ?></span>
                </a>
                <a href="<?= site_url('backoffice/module/judgment') ?>">
                    <span class="bo-ico"><?= $icon('scale') ?></span>
                    <span><?= esc(lang('Backoffice.nav_judgments')) ?></span>
                    <span class="bo-chev"><?= $icon('chevron') ?></span>
                </a>

                <p class="bo-nav-group"><?= esc(lang('Backoffice.nav_governance')) ?></p>
                <a href="<?= site_url('backoffice/module/users') ?>">
                    <span class="bo-ico"><?= $icon('shield') ?></span>
                    <span><?= esc(lang('Backoffice.nav_users')) ?></span>
                    <span class="bo-chev"><?= $icon('chevron') ?></span>
                </a>
                <a href="<?= site_url('backoffice/module/jurisdictions') ?>">
                    <span class="bo-ico"><?= $icon('map') ?></span>
                    <span><?= esc(lang('Backoffice.nav_jurisdictions')) ?></span>
                    <span class="bo-chev"><?= $icon('chevron') ?></span>
                </a>
                <a href="<?= site_url('backoffice/module/logs') ?>">
                    <span class="bo-ico"><?= $icon('list') ?></span>
                    <span><?= esc(lang('Backoffice.nav_logs')) ?></span>
                    <span class="bo-chev"><?= $icon('chevron') ?></span>
                </a>
            </nav>

            <div class="bo-sidebar-foot">
                <a href="<?= site_url('/') ?>">
                    <span class="bo-ico"><?= $icon('help') ?></span>
                    <?= esc(lang('Backoffice.help_center')) ?>
                </a>
                <a class="bo-logout" href="<?= site_url('/') ?>">
                    <span class="bo-ico"><?= $icon('logout') ?></span>
                    <?= esc(lang('Backoffice.logout')) ?>
                </a>
            </div>
        </aside>

        <div class="bo-mainwrap">
            <header class="bo-topbar">
                <div class="bo-top-left">
                    <button class="bo-menu-btn" type="button" data-bo-toggle aria-expanded="false"><?= esc(lang('Backoffice.menu')) ?></button>
                    <a class="bo-back" href="<?= site_url('backoffice') ?>">
                        <span class="bo-ico"><?= $icon('back') ?></span>
                        <?= esc(lang('Backoffice.back_dashboard')) ?>
                    </a>
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
                    <button class="bo-icon-btn" type="button" aria-label="<?= esc(lang('Backoffice.help')) ?>"><?= $icon('help') ?></button>
                    <button class="bo-icon-btn" type="button" aria-label="<?= esc(lang('Backoffice.notifications')) ?>">
                        <?= $icon('bell') ?>
                        <span class="bo-badge" aria-hidden="true">3</span>
                    </button>
                    <div class="bo-userchip">
                        <div>
                            <strong><?= esc($user['name']) ?></strong>
                            <small><?= esc($user['role']) ?></small>
                        </div>
                        <span class="bo-avatar" aria-hidden="true"><?= esc(mb_substr($user['name'], 0, 1)) ?></span>
                    </div>
                </div>
            </header>

            <main id="bo-main" class="bo-content">
                <?= $this->renderSection('content') ?>
            </main>
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
    </script>
    <script src="<?= public_asset('assets/vendor/jquery/jquery.min.js') ?>"></script>
    <script src="<?= public_asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= public_asset('assets/vendor/datatables/dataTables.min.js') ?>"></script>
    <script src="<?= public_asset('assets/vendor/datatables/dataTables.bootstrap5.min.js') ?>"></script>
    <script src="<?= public_asset('assets/js/backoffice.js') ?>"></script>
</body>
</html>
