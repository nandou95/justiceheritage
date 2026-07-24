<!DOCTYPE html>
<html lang="<?= esc(service('request')->getLocale()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= esc($metaDescription ?? lang('Site.home_meta')) ?>">
    <title><?= esc($title ?? 'JusticeHeritage') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= public_asset('assets/vendor/bootstrap/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= public_asset('assets/css/public.css') ?>">
</head>
<body class="d-flex flex-column min-vh-100">
    <?php $locale = service('request')->getLocale(); ?>
    <a class="skip-link" href="#main"><?= esc(lang('Site.skip_to_content')) ?></a>

    <div class="jh-trustbar" role="note">
        <div class="jh-container">
            <span><strong><?= esc(lang('Site.trust_official')) ?></strong> — <?= esc(lang('Site.trust_ministry')) ?></span>
            <span><?= esc(lang('Site.trust_secure')) ?></span>
        </div>
    </div>

    <header class="jh-header" data-header>
        <div class="jh-container jh-header-inner">
            <a class="jh-brand" href="<?= site_url('/') ?>">
                <span class="jh-brand-mark" aria-hidden="true"></span>
                <span class="jh-brand-text">
                    <span class="jh-brand-name">JusticeHeritage</span>
                    <span class="jh-brand-tag"><?= esc(lang('Site.brand_tag')) ?></span>
                </span>
            </a>

            <div class="jh-header-actions">
                <nav class="jh-lang" aria-label="<?= esc(lang('Site.nav_language')) ?>">
                    <a href="<?= site_url('lang/en') ?>" hreflang="en" lang="en" <?= $locale === 'en' ? 'aria-current="true"' : '' ?>>EN</a>
                    <a href="<?= site_url('lang/fr') ?>" hreflang="fr" lang="fr" <?= $locale === 'fr' ? 'aria-current="true"' : '' ?>>FR</a>
                </nav>

                <button
                    class="jh-nav-toggle"
                    type="button"
                    data-nav-toggle
                    aria-controls="primary-nav"
                    aria-expanded="false"
                    aria-label="<?= esc(lang('Site.nav_menu')) ?>"
                >
                    <?= esc(lang('Site.nav_menu')) ?>
                </button>

                <nav id="primary-nav" class="jh-nav" data-nav aria-label="<?= esc(lang('Site.nav_primary')) ?>">
                    <a href="<?= site_url('/') ?>" <?= ($active ?? '') === 'home' ? 'aria-current="page"' : '' ?>><?= esc(lang('Site.nav_home')) ?></a>
                    <a href="<?= site_url('dispute-management') ?>" <?= ($active ?? '') === 'dispute' ? 'aria-current="page"' : '' ?>><?= esc(lang('Site.nav_dispute')) ?></a>
                    <a href="<?= site_url('register') ?>" <?= ($active ?? '') === 'register' ? 'aria-current="page"' : '' ?>><?= esc(lang('Site.nav_register')) ?></a>
                    <a class="btn btn-jh-primary btn-sm" href="<?= site_url('login') ?>"><?= esc(lang('Site.nav_signin')) ?></a>
                </nav>
            </div>
        </div>
    </header>

    <main id="main">
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="jh-footer">
        <div class="jh-container">
            <div class="jh-footer-grid">
                <div>
                    <h2>JusticeHeritage</h2>
                    <p><?= esc(lang('Site.footer_about')) ?></p>
                </div>
                <div>
                    <h3><?= esc(lang('Site.footer_services')) ?></h3>
                    <ul>
                        <li><a href="<?= site_url('dispute-management') ?>"><?= esc(lang('Site.footer_how')) ?></a></li>
                        <li><a href="<?= site_url('register') ?>"><?= esc(lang('Site.footer_register')) ?></a></li>
                        <li><a href="<?= site_url('login') ?>"><?= esc(lang('Site.footer_signin')) ?></a></li>
                        <li><a href="<?= site_url('backoffice') ?>">Backoffice</a></li>
                        <li><a href="<?= site_url('portal') ?>">Complainant portal</a></li>
                    </ul>
                </div>
                <div>
                    <h3><?= esc(lang('Site.footer_courts')) ?></h3>
                    <ul>
                        <li><?= esc(lang('Site.footer_communal')) ?></li>
                        <li><?= esc(lang('Site.footer_provincial')) ?></li>
                        <li><?= esc(lang('Site.footer_regional')) ?></li>
                    </ul>
                </div>
            </div>
            <div class="jh-footer-bottom">
                <span>&copy; <span data-year></span> <?= esc(lang('Site.footer_copy')) ?></span>
                <span><?= esc(lang('Site.footer_tagline')) ?></span>
            </div>
        </div>
    </footer>

    <script src="<?= public_asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= public_asset('assets/js/public.js') ?>"></script>
</body>
</html>
