<?= $this->extend('layouts/backoffice') ?>

<?= $this->section('content') ?>

<?= view('Modules\Administration\Views\partials\flash') ?>

<?php
$isEdit = ($mode ?? 'create') === 'edit';
$action = $isEdit
    ? site_url('backoffice/profiles/' . (int) ($record['profil_id'] ?? 0))
    : site_url('backoffice/profiles');
$selectedIds = array_map('intval', $record['permission_ids'] ?? []);
$selectedMap = array_fill_keys($selectedIds, true);
?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_profiles')) ?></p>
        <h1><?= esc($isEdit ? lang('Backoffice.profiles_edit_title') : lang('Backoffice.profiles_create_title')) ?></h1>
        <p><?= esc(lang('Backoffice.profiles_form_lead')) ?></p>
    </div>
    <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/profiles') ?>">
        <i class="bi bi-arrow-left" aria-hidden="true"></i>
        <?= esc(lang('Backoffice.profiles_back_list')) ?>
    </a>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-form needs-validation" method="post" action="<?= esc($action) ?>" novalidate data-bo-profile-form>
        <?= csrf_field() ?>

        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label class="form-label" for="code_profil"><?= esc(lang('Backoffice.profiles_field_code')) ?> *</label>
                <input class="form-control" type="text" id="code_profil" name="code_profil" value="<?= esc($record['code_profil'] ?? '') ?>" required pattern="[A-Za-z0-9_\-.]+">
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12 col-md-8">
                <label class="form-label" for="libelle_profil"><?= esc(lang('Backoffice.profiles_field_name')) ?> *</label>
                <input class="form-control" type="text" id="libelle_profil" name="libelle_profil" value="<?= esc($record['libelle_profil'] ?? '') ?>" required>
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-12">
                <label class="form-label" for="description_profil"><?= esc(lang('Backoffice.profiles_field_description')) ?></label>
                <textarea class="form-control" id="description_profil" name="description_profil" rows="3"><?= esc($record['description_profil'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="bo-perm-assign" data-bo-perm-assign>
            <div class="bo-perm-assign-head">
                <div>
                    <h2 class="bo-form-section-title"><?= esc(lang('Backoffice.profiles_permissions_title')) ?></h2>
                    <p class="bo-perm-assign-lead"><?= esc(lang('Backoffice.profiles_permissions_lead')) ?></p>
                </div>
                <div class="bo-perm-assign-tools">
                    <label class="bo-table-search">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input type="search" class="form-control" data-perm-search placeholder="<?= esc(lang('Backoffice.profiles_permissions_search'), 'attr') ?>" aria-label="<?= esc(lang('Backoffice.profiles_permissions_search')) ?>">
                    </label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="perm_select_all" data-perm-select-all>
                        <label class="form-check-label" for="perm_select_all"><?= esc(lang('Backoffice.profiles_permissions_select_all')) ?></label>
                    </div>
                    <span class="bo-perm-selected-count" data-perm-selected-count>0</span>
                </div>
            </div>

            <?php if (empty($permissionGroups)): ?>
                <p class="text-muted mb-0"><?= esc(lang('Backoffice.profiles_permissions_empty')) ?></p>
            <?php else: ?>
                <div class="bo-perm-groups">
                    <?php foreach ($permissionGroups as $group): ?>
                        <section class="bo-perm-group" data-perm-group>
                            <header class="bo-perm-group-head">
                                <h3><?= esc($group['module']) ?></h3>
                                <button class="btn btn-sm btn-bo-secondary" type="button" data-perm-group-toggle>
                                    <?= esc(lang('Backoffice.profiles_permissions_select_group')) ?>
                                </button>
                            </header>
                            <div class="bo-perm-list">
                                <?php foreach ($group['permissions'] as $perm): ?>
                                    <?php $checked = isset($selectedMap[(int) $perm['id']]); ?>
                                    <label class="bo-perm-item<?= $checked ? ' is-selected' : '' ?>" data-perm-item data-search="<?= esc(mb_strtolower(($perm['description'] ?? '') . ' ' . ($perm['url_route'] ?? '')), 'attr') ?>">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="permission_ids[]"
                                            value="<?= esc($perm['id']) ?>"
                                            data-perm-checkbox
                                            <?= $checked ? 'checked' : '' ?>
                                        >
                                        <span class="bo-perm-item-body">
                                            <strong><?= esc($perm['description']) ?></strong>
                                            <code><?= esc($perm['url_route']) ?></code>
                                            <span class="bo-status-pill <?= ! empty($perm['is_active']) ? 'is-active' : 'is-inactive' ?>">
                                                <?= esc($perm['status']) ?>
                                            </span>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="bo-form-actions">
            <a class="btn btn-outline-secondary" href="<?= site_url('backoffice/profiles') ?>"><?= esc(lang('Backoffice.btn_cancel')) ?></a>
            <button class="btn btn-bo-primary" type="submit">
                <?= esc($isEdit ? lang('Backoffice.profiles_save') : lang('Backoffice.profiles_create')) ?>
            </button>
        </div>
    </form>
</section>

<?= $this->endSection() ?>
