<?= $this->extend('layouts/backoffice') ?>

<?= $this->section('content') ?>

<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_users')) ?></p>
        <h1><?= esc(lang('Backoffice.users_details_title')) ?></h1>
        <p><?= esc(trim(($record['prenom_utilisateur'] ?? '') . ' ' . ($record['nom_utilisateur'] ?? ''))) ?></p>
    </div>
    <div class="bo-crud-head-actions">
        <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/users') ?>">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            <?= esc(lang('Backoffice.users_back_list')) ?>
        </a>
        <?php if (can_access('backoffice/users/edit')): ?>
        <a class="btn btn-bo-primary" href="<?= site_url('backoffice/users/' . (int) $record['utilisateur_id'] . '/edit') ?>">
            <i class="bi bi-pencil-square" aria-hidden="true"></i>
            <?= esc(lang('Backoffice.users_action_edit')) ?>
        </a>
        <?php endif; ?>
    </div>
</section>

<section class="bo-panel bo-crud-panel">
    <div class="bo-detail-grid">
        <article>
            <h2><?= esc(lang('Backoffice.users_section_identity')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.users_field_first_name')) ?></dt><dd><?= esc($record['prenom_utilisateur'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.users_field_last_name')) ?></dt><dd><?= esc($record['nom_utilisateur'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.users_field_cni')) ?></dt><dd><?= esc($record['numero_cni'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.users_field_matricule')) ?></dt><dd><?= esc($record['numero_matricule'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.users_field_sex')) ?></dt><dd><?= esc($record['description_sexe'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.users_field_birth_date')) ?></dt><dd><?= esc($record['date_naissance'] ?? '—') ?></dd></div>
            </dl>
        </article>

        <article>
            <h2><?= esc(lang('Backoffice.users_section_contact')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.users_field_email')) ?></dt><dd><?= esc($record['email'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.users_field_phone')) ?></dt><dd><?= esc($record['telephone'] ?? '—') ?></dd></div>
            </dl>
        </article>

        <article>
            <h2><?= esc(lang('Backoffice.users_section_assignment')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.users_field_profile')) ?></dt><dd><?= esc($record['libelle_profil'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.users_col_status')) ?></dt><dd><?= esc($record['desc_statut_compte'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.users_field_jurisdiction')) ?></dt><dd><?= esc($record['nom_juridiction'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.filter_jurisdiction_level')) ?></dt><dd><?= esc($record['desc_niveau_juridiction'] ?? '—') ?></dd></div>
            </dl>
        </article>

        <article>
            <h2><?= esc(lang('Backoffice.users_section_birthplace')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.users_field_province')) ?></dt><dd><?= esc($record['province_naissance_name'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.users_field_commune')) ?></dt><dd><?= esc($record['commune_naissance_name'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.users_field_zone')) ?></dt><dd><?= esc($record['zone_naissance_name'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.users_field_colline')) ?></dt><dd><?= esc($record['colline_naissance_name'] ?? '—') ?></dd></div>
            </dl>
        </article>

        <article>
            <h2><?= esc(lang('Backoffice.users_section_audit')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.users_field_last_login')) ?></dt><dd><?= esc($record['derniere_connexion'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.users_field_created')) ?></dt><dd><?= esc($record['created_at'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.users_field_updated')) ?></dt><dd><?= esc($record['updated_at'] ?? '—') ?></dd></div>
            </dl>
        </article>
    </div>
</section>

<?= $this->endSection() ?>
