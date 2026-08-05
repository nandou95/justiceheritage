<?= $this->extend('layouts/backoffice') ?>

<?= $this->section('content') ?>

<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_profiles')) ?></p>
        <h1><?= esc(lang('Backoffice.profiles_details_title')) ?></h1>
        <p><?= esc($record['libelle_profil'] ?? '') ?></p>
    </div>
    <div class="bo-crud-head-actions">
        <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/profiles') ?>">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            <?= esc(lang('Backoffice.profiles_back_list')) ?>
        </a>
        <a class="btn btn-bo-primary" href="<?= site_url('backoffice/profiles/' . (int) $record['profil_id'] . '/edit') ?>">
            <i class="bi bi-pencil-square" aria-hidden="true"></i>
            <?= esc(lang('Backoffice.profiles_action_edit')) ?>
        </a>
    </div>
</section>

<section class="bo-panel bo-crud-panel">
    <div class="bo-detail-grid">
        <article>
            <h2><?= esc(lang('Backoffice.profiles_section_info')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.profiles_field_code')) ?></dt><dd><code class="bo-route-code"><?= esc($record['code_profil'] ?? '—') ?></code></dd></div>
                <div><dt><?= esc(lang('Backoffice.profiles_field_name')) ?></dt><dd><?= esc($record['libelle_profil'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.profiles_field_description')) ?></dt><dd><?= esc(($record['description_profil'] ?? '') !== '' ? $record['description_profil'] : '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.profiles_col_status')) ?></dt><dd>
                    <span class="bo-status-pill <?= ! empty($record['is_active']) ? 'is-active' : 'is-inactive' ?>">
                        <?= esc($record['status'] ?? '—') ?>
                    </span>
                </dd></div>
                <div><dt><?= esc(lang('Backoffice.profiles_field_created')) ?></dt><dd><?= esc($record['created_at'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.profiles_col_permissions')) ?></dt><dd><?= esc((string) ($record['permissions_count'] ?? 0)) ?></dd></div>
            </dl>
        </article>
    </div>

    <div class="bo-profile-permissions mt-4">
        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-3">
            <h2 class="bo-form-section-title mb-0"><?= esc(lang('Backoffice.profiles_assigned_permissions')) ?></h2>
            <label class="bo-table-search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" class="form-control" id="profile-permissions-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>" aria-label="<?= esc(lang('Backoffice.search_placeholder')) ?>">
            </label>
        </div>

        <div class="table-responsive bo-table-wrap">
            <table class="table table-hover bo-table jh-datatable w-100" id="profile-permissions-table" data-page-length="10" data-order-col="0" data-order-dir="asc" data-dom="lrtip">
                <thead>
                    <tr>
                        <th><?= esc(lang('Backoffice.perm_col_description')) ?></th>
                        <th><?= esc(lang('Backoffice.perm_col_route')) ?></th>
                        <th><?= esc(lang('Backoffice.perm_col_status')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($record['permissions'] ?? []) as $perm): ?>
                        <tr>
                            <td><?= esc($perm['description']) ?></td>
                            <td><code class="bo-route-code"><?= esc($perm['url_route']) ?></code></td>
                            <td>
                                <span class="bo-status-pill <?= ! empty($perm['is_active']) ? 'is-active' : 'is-inactive' ?>">
                                    <?= esc($perm['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
