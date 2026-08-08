<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<?php
$firstName = trim((string) ($record['prenom_utilisateur'] ?? ''));
$lastName  = trim((string) ($record['nom_utilisateur'] ?? ''));
$fullName  = trim($firstName . ' ' . $lastName);
$fmt = static function ($value, string $format = 'd/m/Y H:i'): string {
    $value = trim((string) $value);
    if ($value === '') {
        return '—';
    }
    $ts = strtotime($value);

    return $ts ? date($format, $ts) : $value;
};
$initials = static function (string $name): string {
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
$statusLabel  = (string) ($record['desc_statut_compte'] ?? '—');
$statusActive = stripos($statusLabel, 'actif') !== false || stripos($statusLabel, 'active') !== false;
$addressParts = array_values(array_filter([
    $record['colline_naissance_name'] ?? '',
    $record['zone_naissance_name'] ?? '',
    $record['commune_naissance_name'] ?? '',
    $record['province_naissance_name'] ?? '',
], static fn ($v) => trim((string) $v) !== ''));
$address = $addressParts !== [] ? implode(', ', $addressParts) : '—';
$permissionGroups = $permissionGroups ?? [];
?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.account_kicker')) ?></p>
        <h1><?= esc(lang('Backoffice.account_profile_title')) ?></h1>
        <nav class="bo-breadcrumb" aria-label="breadcrumb">
            <a href="<?= site_url('backoffice') ?>"><?= esc(lang('Backoffice.nav_dashboard')) ?></a>
            <span aria-hidden="true">/</span>
            <span><?= esc(lang('Backoffice.account_profile_title')) ?></span>
        </nav>
    </div>
</section>

<section class="bo-profile-page">
    <article class="bo-profile-hero-card">
        <div class="bo-profile-hero-main">
            <span class="bo-avatar bo-avatar-xl" aria-hidden="true"><?= esc($initials($fullName)) ?></span>
            <div>
                <h2><?= esc($fullName !== '' ? $fullName : '—') ?></h2>
                <p class="bo-profile-hero-role"><?= esc($record['libelle_profil'] ?? '—') ?></p>
                <p class="bo-profile-hero-meta">
                    <i class="bi bi-building" aria-hidden="true"></i>
                    <?= esc($record['nom_juridiction'] ?? '—') ?>
                    <span class="bo-dot" aria-hidden="true">·</span>
                    <i class="bi bi-layers" aria-hidden="true"></i>
                    <?= esc($record['desc_niveau_juridiction'] ?? '—') ?>
                </p>
                <div class="bo-profile-hero-badges">
                    <span class="bo-status-pill <?= $statusActive ? 'is-active' : 'is-inactive' ?>">
                        <?= esc($statusLabel) ?>
                    </span>
                    <span class="bo-profile-meta-chip">
                        <i class="bi bi-clock-history" aria-hidden="true"></i>
                        <?= esc(lang('Backoffice.account_last_login')) ?>:
                        <?= esc($fmt($record['derniere_connexion'] ?? '')) ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="bo-profile-hero-actions">
            <a class="btn btn-bo-primary" href="<?= site_url('backoffice/my/profile/edit') ?>">
                <i class="bi bi-pencil-square" aria-hidden="true"></i>
                <?= esc(lang('Backoffice.account_edit_profile')) ?>
            </a>
            <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/my/password') ?>">
                <i class="bi bi-shield-lock" aria-hidden="true"></i>
                <?= esc(lang('Backoffice.account_change_password')) ?>
            </a>
        </div>
    </article>

    <div class="bo-profile-grid">
        <article class="bo-profile-card">
            <header>
                <h3><i class="bi bi-person-vcard" aria-hidden="true"></i> <?= esc(lang('Backoffice.account_section_personal')) ?></h3>
            </header>
            <ul class="bo-profile-info-list">
                <li><i class="bi bi-person" aria-hidden="true"></i><div><span><?= esc(lang('Backoffice.users_field_last_name')) ?></span><strong><?= esc($lastName !== '' ? $lastName : '—') ?></strong></div></li>
                <li><i class="bi bi-person" aria-hidden="true"></i><div><span><?= esc(lang('Backoffice.users_field_first_name')) ?></span><strong><?= esc($firstName !== '' ? $firstName : '—') ?></strong></div></li>
                <li><i class="bi bi-gender-ambiguous" aria-hidden="true"></i><div><span><?= esc(lang('Backoffice.users_field_sex')) ?></span><strong><?= esc($record['description_sexe'] ?? '—') ?></strong></div></li>
                <li><i class="bi bi-calendar-event" aria-hidden="true"></i><div><span><?= esc(lang('Backoffice.users_field_birth_date')) ?></span><strong><?= esc($fmt($record['date_naissance'] ?? '', 'd/m/Y')) ?></strong></div></li>
                <li><i class="bi bi-person-badge" aria-hidden="true"></i><div><span><?= esc(lang('Backoffice.users_field_cni')) ?></span><strong><?= esc($record['numero_cni'] ?? '—') ?></strong></div></li>
                <li><i class="bi bi-upc" aria-hidden="true"></i><div><span><?= esc(lang('Backoffice.users_field_matricule')) ?></span><strong><?= esc($record['numero_matricule'] ?? '—') ?></strong></div></li>
            </ul>
        </article>

        <article class="bo-profile-card">
            <header>
                <h3><i class="bi bi-telephone" aria-hidden="true"></i> <?= esc(lang('Backoffice.account_section_contact')) ?></h3>
            </header>
            <ul class="bo-profile-contact-list">
                <li>
                    <i class="bi bi-envelope" aria-hidden="true"></i>
                    <div>
                        <span><?= esc(lang('Backoffice.users_field_email')) ?></span>
                        <strong><?= esc($record['email'] ?? '—') ?></strong>
                    </div>
                </li>
                <li>
                    <i class="bi bi-phone" aria-hidden="true"></i>
                    <div>
                        <span><?= esc(lang('Backoffice.users_field_phone')) ?></span>
                        <strong><?= esc($record['telephone'] ?? '—') ?></strong>
                    </div>
                </li>
                <li>
                    <i class="bi bi-geo-alt" aria-hidden="true"></i>
                    <div>
                        <span><?= esc(lang('Backoffice.account_field_address')) ?></span>
                        <strong><?= esc($address) ?></strong>
                    </div>
                </li>
            </ul>
        </article>
    </div>

    <article class="bo-profile-card">
        <header>
            <h3><i class="bi bi-briefcase" aria-hidden="true"></i> <?= esc(lang('Backoffice.account_section_professional')) ?></h3>
        </header>
        <div class="bo-profile-pro-grid">
            <div>
                <i class="bi bi-person-badge" aria-hidden="true"></i>
                <span><?= esc(lang('Backoffice.users_field_profile')) ?></span>
                <strong><?= esc($record['libelle_profil'] ?? '—') ?></strong>
            </div>
            <div>
                <i class="bi bi-building" aria-hidden="true"></i>
                <span><?= esc(lang('Backoffice.users_field_jurisdiction')) ?></span>
                <strong><?= esc($record['nom_juridiction'] ?? '—') ?></strong>
            </div>
            <div>
                <i class="bi bi-layers" aria-hidden="true"></i>
                <span><?= esc(lang('Backoffice.filter_jurisdiction_level')) ?></span>
                <strong><?= esc($record['desc_niveau_juridiction'] ?? '—') ?></strong>
            </div>
            <div>
                <i class="bi bi-calendar-plus" aria-hidden="true"></i>
                <span><?= esc(lang('Backoffice.account_created_at')) ?></span>
                <strong><?= esc($fmt($record['created_at'] ?? '')) ?></strong>
            </div>
            <div>
                <i class="bi bi-shield-check" aria-hidden="true"></i>
                <span><?= esc(lang('Backoffice.filter_account_status')) ?></span>
                <strong><span class="bo-status-pill <?= $statusActive ? 'is-active' : 'is-inactive' ?>"><?= esc($statusLabel) ?></span></strong>
            </div>
        </div>
    </article>

    <article class="bo-profile-card">
        <header>
            <h3><i class="bi bi-key" aria-hidden="true"></i> <?= esc(lang('Backoffice.account_section_permissions')) ?></h3>
        </header>
        <?php if ($permissionGroups === []): ?>
            <p class="bo-profile-empty"><?= esc(lang('Backoffice.account_permissions_empty')) ?></p>
        <?php else: ?>
            <div class="accordion bo-profile-perm-accordion" id="accountPermissionsAccordion">
                <?php foreach ($permissionGroups as $index => $group): ?>
                    <?php
                    $collapseId = 'permGroup' . $index;
                    $isOpen = $index < 3;
                    ?>
                    <div class="accordion-item">
                        <h4 class="accordion-header" id="heading<?= esc($collapseId) ?>">
                            <button
                                class="accordion-button<?= $isOpen ? '' : ' collapsed' ?>"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#<?= esc($collapseId) ?>"
                                aria-expanded="<?= $isOpen ? 'true' : 'false' ?>"
                                aria-controls="<?= esc($collapseId) ?>"
                            >
                                <span><?= esc($group['module']) ?></span>
                                <span class="bo-profile-perm-count"><?= count($group['permissions']) ?></span>
                            </button>
                        </h4>
                        <div id="<?= esc($collapseId) ?>" class="accordion-collapse collapse<?= $isOpen ? ' show' : '' ?>" data-bs-parent="#accountPermissionsAccordion">
                            <div class="accordion-body">
                                <ul class="bo-profile-perm-list">
                                    <?php foreach ($group['permissions'] as $perm): ?>
                                        <li>
                                            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                                            <span><?= esc($perm['description'] !== '' ? $perm['description'] : $perm['url_route']) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>

    <article class="bo-profile-card" id="account-security">
        <header>
            <h3><i class="bi bi-shield-lock" aria-hidden="true"></i> <?= esc(lang('Backoffice.account_section_security')) ?></h3>
        </header>
        <div class="bo-profile-security-grid">
            <div>
                <span><?= esc(lang('Backoffice.account_field_2fa')) ?></span>
                <strong>
                    <span class="bo-status-pill <?= ! empty($twoFactorEnabled) ? 'is-active' : 'is-inactive' ?>">
                        <?= esc(! empty($twoFactorEnabled) ? lang('Backoffice.account_2fa_enabled') : lang('Backoffice.account_2fa_disabled')) ?>
                    </span>
                </strong>
                <small><?= esc(lang('Backoffice.account_2fa_method_email')) ?></small>
            </div>
            <div>
                <span><?= esc(lang('Backoffice.account_last_login')) ?></span>
                <strong><?= esc($fmt($record['derniere_connexion'] ?? '')) ?></strong>
            </div>
            <div>
                <span><?= esc(lang('Backoffice.account_password_changed_at')) ?></span>
                <strong><?= esc($fmt($passwordChangedAt ?? '')) ?></strong>
            </div>
        </div>
        <div class="bo-profile-security-actions">
            <a class="btn btn-bo-primary" href="<?= site_url('backoffice/my/password') ?>">
                <i class="bi bi-key" aria-hidden="true"></i>
                <?= esc(lang('Backoffice.account_change_password')) ?>
            </a>
            <button class="btn btn-bo-secondary" type="button" data-bs-toggle="modal" data-bs-target="#account2faInfoModal">
                <i class="bi bi-shield-check" aria-hidden="true"></i>
                <?= esc(lang('Backoffice.account_configure_2fa')) ?>
            </button>
        </div>
    </article>
</section>

<div class="modal fade" id="account2faInfoModal" tabindex="-1" aria-labelledby="account2faInfoModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="account2faInfoModalTitle"><?= esc(lang('Backoffice.account_configure_2fa')) ?></h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc(lang('Backoffice.btn_cancel'), 'attr') ?>"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0"><?= esc(lang('Backoffice.account_2fa_info')) ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-bo-primary" data-bs-dismiss="modal"><?= esc(lang('Backoffice.btn_ok')) ?></button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
