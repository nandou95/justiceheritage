<!DOCTYPE html>
<html lang="<?= esc(service('request')->getLocale()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= esc(lang('Portal.app_name')) ?>">
    <title><?= esc($title ?? lang('Portal.app_name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= public_asset('assets/vendor/bootstrap/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= public_asset('assets/vendor/datatables/dataTables.bootstrap5.min.css') ?>">
    <link rel="stylesheet" href="<?= public_asset('assets/css/public.css') ?>">
    <link rel="stylesheet" href="<?= public_asset('assets/css/portal.css') ?>">
</head>
<body class="jh-portal-body">
    <?php
    $locale = service('request')->getLocale();
    $user   = $user ?? ['name' => 'Complainant', 'email' => ''];
    $icon = static function (string $name): string {
        $icons = [
            'overview' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1v-9.5Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>',
            'new' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
            'complaints' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 7h11M8 12h11M8 17h7M5 7h.01M5 12h.01M5 17h.01" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
            'provincial' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m7 14 5-5 5 5M5 20h14" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'regional' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 4 9v11h6v-6h4v6h6V9l-8-6Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>',
            'ministry' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h16M6 20V10l6-5 6 5v10M9 20v-5h6v5M10 10h.01M14 10h.01M12 13h.01" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'profile' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M5 19a7 7 0 0 1 14 0" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
            'logout' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 7V5a2 2 0 0 1 2-2h7v18h-7a2 2 0 0 1-2-2v-2M14 12H3m0 0 3-3m-3 3 3 3" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'bell' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18a3 3 0 0 1-6 0m9 0H6a2 2 0 0 1-1.6-3.2C5.5 13.2 7 11.8 7 9a5 5 0 0 1 10 0c0 2.8 1.5 4.2 2.6 5.8A2 2 0 0 1 18 18Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>',
            'menu' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
            'close' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
        ];

        return $icons[$name] ?? '';
    };
    ?>
    <a class="skip-link" href="#portal-main"><?= esc(lang('Site.skip_to_content')) ?></a>

    <div class="jh-portal">
        <div class="jh-portal-backdrop" data-portal-backdrop hidden></div>

        <aside id="portal-sidebar" class="jh-portal-sidebar" data-portal-nav>
            <div class="jh-portal-sidebar-head">
                <div class="jh-portal-menu-label"><?= esc(lang('Portal.menu')) ?></div>
                <button
                    class="jh-portal-close-btn"
                    type="button"
                    data-portal-close
                    aria-label="<?= esc(lang('Portal.menu_close')) ?>"
                >
                    <?= $icon('close') ?>
                </button>
            </div>

            <nav class="jh-portal-nav" aria-label="<?= esc(lang('Portal.app_name')) ?>">
                <a href="<?= site_url('portal') ?>" class="<?= ($active ?? '') === 'overview' ? 'is-active' : '' ?>">
                    <span class="jh-nav-ico"><?= $icon('overview') ?></span>
                    <?= esc(lang('Portal.nav_overview')) ?>
                </a>
                <a href="<?= site_url('portal/complaints/new') ?>" class="<?= ($active ?? '') === 'new' ? 'is-active' : '' ?>">
                    <span class="jh-nav-ico"><?= $icon('new') ?></span>
                    <?= esc(lang('Portal.nav_new')) ?>
                </a>
                <a href="<?= site_url('portal/complaints') ?>" class="<?= ($active ?? '') === 'complaints' ? 'is-active' : '' ?>">
                    <span class="jh-nav-ico"><?= $icon('complaints') ?></span>
                    <?= esc(lang('Portal.nav_complaints')) ?>
                </a>
                <a href="<?= site_url('portal/appeals/provincial') ?>" class="<?= ($active ?? '') === 'provincial' ? 'is-active' : '' ?>">
                    <span class="jh-nav-ico"><?= $icon('provincial') ?></span>
                    <?= esc(lang('Portal.nav_provincial')) ?>
                </a>
                <a href="<?= site_url('portal/appeals/regional') ?>" class="<?= ($active ?? '') === 'regional' ? 'is-active' : '' ?>">
                    <span class="jh-nav-ico"><?= $icon('regional') ?></span>
                    <?= esc(lang('Portal.nav_regional')) ?>
                </a>
                <a href="<?= site_url('portal/ministry') ?>" class="<?= ($active ?? '') === 'ministry' ? 'is-active' : '' ?>">
                    <span class="jh-nav-ico"><?= $icon('ministry') ?></span>
                    <?= esc(lang('Portal.nav_ministry')) ?>
                </a>
                <a href="<?= site_url('portal/profile') ?>" class="<?= ($active ?? '') === 'profile' ? 'is-active' : '' ?>">
                    <span class="jh-nav-ico"><?= $icon('profile') ?></span>
                    <?= esc(lang('Portal.nav_profile')) ?>
                </a>
                <a href="<?= site_url('logout') ?>">
                    <span class="jh-nav-ico"><?= $icon('logout') ?></span>
                    <?= esc(lang('Portal.nav_signout')) ?>
                </a>
            </nav>

            <div class="jh-portal-profile-card">
                <span class="jh-portal-avatar" aria-hidden="true"><?= esc(mb_substr($user['name'], 0, 1)) ?></span>
                <div>
                    <strong><?= esc($user['name']) ?></strong>
                    <small><?= esc($user['email'] ?? '') ?></small>
                </div>
            </div>
        </aside>

        <div class="jh-portal-mainwrap">
            <header class="jh-portal-top">
                <div class="jh-portal-top-left">
                    <button
                        class="jh-portal-menu-btn"
                        type="button"
                        data-portal-toggle
                        aria-controls="portal-sidebar"
                        aria-expanded="false"
                        aria-label="<?= esc(lang('Portal.menu_open')) ?>"
                    >
                        <span class="jh-portal-menu-btn-ico" aria-hidden="true"><?= $icon('menu') ?></span>
                        <span class="jh-portal-menu-btn-text"><?= esc(lang('Portal.menu')) ?></span>
                    </button>
                    <a class="jh-portal-top-brand" href="<?= site_url('portal') ?>">
                        <span class="jh-brand-mark" aria-hidden="true"></span>
                        <span>
                            <strong>JusticeHeritage</strong>
                            <small><?= esc(lang('Portal.app_name')) ?></small>
                        </span>
                    </a>
                </div>

                <div class="jh-portal-top-actions">
                    <nav class="jh-lang" aria-label="<?= esc(lang('Site.nav_language')) ?>">
                        <a href="<?= site_url('lang/en') ?>" hreflang="en" <?= $locale === 'en' ? 'aria-current="true"' : '' ?>>EN</a>
                        <a href="<?= site_url('lang/fr') ?>" hreflang="fr" <?= $locale === 'fr' ? 'aria-current="true"' : '' ?>>FR</a>
                    </nav>
                    <button class="jh-icon-btn" type="button" aria-label="<?= esc(lang('Portal.notifications')) ?>">
                        <?= $icon('bell') ?>
                        <span class="jh-icon-dot" aria-hidden="true"></span>
                    </button>
                    <a class="jh-portal-top-user" href="<?= site_url('portal/profile') ?>">
                        <span class="jh-portal-avatar jh-portal-avatar--sm" aria-hidden="true"><?= esc(mb_substr($user['name'], 0, 1)) ?></span>
                    </a>
                </div>
            </header>

            <main id="portal-main" class="jh-portal-content">
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
    <script src="<?= public_asset('assets/js/public.js') ?>"></script>
    <script src="<?= public_asset('assets/js/portal.js') ?>"></script>
</body>
</html>
