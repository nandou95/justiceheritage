<?= $this->extend('layouts/portal') ?>

<?= $this->section('content') ?>

<?php
$firstName  = (string) ($user['first_name'] ?? explode(' ', (string) ($user['name'] ?? ''), 2)[0] ?? '');
$lastName   = (string) ($user['last_name'] ?? (explode(' ', (string) ($user['name'] ?? ''), 2)[1] ?? ''));
$fullName   = trim($firstName . ' ' . $lastName) !== '' ? trim($firstName . ' ' . $lastName) : (string) ($user['name'] ?? '');
$initial    = mb_strtoupper(mb_substr($fullName !== '' ? $fullName : 'C', 0, 1));
$nationalId = (string) ($user['national_id'] ?? $user['id'] ?? '');
$email      = (string) ($user['email'] ?? '');
$phone      = (string) ($user['phone'] ?? '');
?>

<section class="jh-profile-hero" aria-label="<?= esc(lang('Portal.profile_h1')) ?>">
    <div class="jh-profile-hero-main">
        <span class="jh-profile-avatar" aria-hidden="true"><?= esc($initial) ?></span>
        <div class="jh-profile-hero-copy">
            <p class="jh-profile-kicker"><?= esc(lang('Portal.profile_kicker')) ?></p>
            <h1><?= esc($fullName !== '' ? $fullName : lang('Portal.profile_h1')) ?></h1>
            <p class="jh-profile-hero-lead"><?= esc(lang('Portal.profile_lead')) ?></p>
            <ul class="jh-profile-contact">
                <?php if ($email !== ''): ?>
                    <li>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16v12H4V6Zm0 0 8 7 8-7" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                        <span><?= esc($email) ?></span>
                    </li>
                <?php endif; ?>
                <?php if ($phone !== ''): ?>
                    <li>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h4l1.5 4-2.5 1.5a12 12 0 0 0 5.5 5.5L17 11.5 21 13v4a2 2 0 0 1-2.2 2A16 16 0 0 1 5 7.2 2 2 0 0 1 7 3Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                        <span><?= esc($phone) ?></span>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <div class="jh-profile-hero-aside">
        <span class="jh-profile-2fa-chip" title="<?= esc(lang('Portal.profile_2fa')) ?>">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v5c0 4.5 3 7.8 7 9 4-1.2 7-4.5 7-9V6l-7-3Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m9.5 12 1.8 1.8 3.7-3.8" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <?= esc(lang('Portal.profile_2fa')) ?> · <?= esc(lang('Portal.profile_2fa_on')) ?>
        </span>
    </div>
</section>

<div class="jh-profile-layout">
    <section class="jh-dash-panel jh-profile-panel" aria-labelledby="profile-personal-heading">
        <div class="jh-dash-panel-head">
            <div>
                <h2 id="profile-personal-heading"><?= esc(lang('Portal.profile_sec_personal')) ?></h2>
                <p><?= esc(lang('Portal.profile_sec_personal_lead')) ?></p>
            </div>
        </div>

        <form class="jh-portal-form jh-profile-form" method="post" action="<?= site_url('portal/profile') ?>">
            <?= csrf_field() ?>
            <div class="jh-auth-grid">
                <div class="jh-field">
                    <label class="form-label" for="first_name"><?= esc(lang('Site.label_first_name')) ?></label>
                    <input class="form-control" type="text" id="first_name" name="first_name" value="<?= esc($firstName) ?>" autocomplete="given-name">
                </div>
                <div class="jh-field">
                    <label class="form-label" for="last_name"><?= esc(lang('Site.label_last_name')) ?></label>
                    <input class="form-control" type="text" id="last_name" name="last_name" value="<?= esc($lastName) ?>" autocomplete="family-name">
                </div>
                <div class="jh-field jh-field--full">
                    <label class="form-label" for="email"><?= esc(lang('Site.label_email')) ?></label>
                    <input class="form-control" type="email" id="email" name="email" value="<?= esc($email) ?>" autocomplete="email">
                </div>
                <div class="jh-field">
                    <label class="form-label" for="phone"><?= esc(lang('Site.label_phone')) ?></label>
                    <input class="form-control" type="tel" id="phone" name="phone" value="<?= esc($phone) ?>" autocomplete="tel">
                </div>
                <div class="jh-field">
                    <label class="form-label" for="national_id"><?= esc(lang('Site.label_national_id')) ?></label>
                    <input class="form-control" type="text" id="national_id" name="national_id" value="<?= esc($nationalId) ?>" readonly>
                </div>
            </div>
            <div class="jh-portal-actions jh-profile-actions">
                <button class="btn btn-jh-primary jh-profile-save" type="submit"><?= esc(lang('Portal.profile_save')) ?></button>
            </div>
        </form>
    </section>

    <aside class="jh-profile-security" aria-labelledby="profile-security-heading">
        <div class="jh-profile-security-card">
            <span class="jh-profile-security-ico" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M12 3 5 6v5c0 4.5 3 7.8 7 9 4-1.2 7-4.5 7-9V6l-7-3Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
            </span>
            <h2 id="profile-security-heading"><?= esc(lang('Portal.profile_sec_security')) ?></h2>
            <p><?= esc(lang('Portal.profile_sec_security_lead')) ?></p>
            <div class="jh-profile-security-status">
                <strong><?= esc(lang('Portal.profile_2fa')) ?></strong>
                <span class="jh-profile-2fa-chip jh-profile-2fa-chip--compact">
                    <?= esc(lang('Portal.profile_2fa_on')) ?>
                </span>
            </div>
            <p class="jh-profile-security-note"><?= esc(lang('Portal.profile_2fa_help')) ?></p>
        </div>
    </aside>
</div>

<?= $this->endSection() ?>
