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
                    <button class="btn btn-sm btn-bo-secondary" type="button" data-perm-select-all>
                        <?= esc(lang('Backoffice.profiles_permissions_select_all')) ?>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-perm-deselect-all>
                        <?= esc(lang('Backoffice.profiles_permissions_deselect_all')) ?>
                    </button>
                    <span class="bo-perm-selected-count" data-perm-selected-count>0</span>
                </div>
            </div>

            <?php if (empty($permissionGroups)): ?>
                <p class="text-muted mb-0"><?= esc(lang('Backoffice.profiles_permissions_empty')) ?></p>
            <?php else: ?>
                <div class="accordion bo-perm-groups" id="profilePermAccordion">
                    <?php foreach ($permissionGroups as $index => $group): ?>
                        <?php
                        $groupKey   = preg_replace('/[^a-z0-9_-]+/i', '-', (string) ($group['module_key'] ?? $index)) ?: (string) $index;
                        $collapseId = 'perm-group-' . $groupKey;
                        $headingId  = $collapseId . '-heading';
                        $permCount  = count($group['permissions'] ?? []);
                        $moduleTitle = (string) ($group['module'] ?? '');
                        ?>
                        <div class="accordion-item card bo-perm-group" data-perm-group data-group-title="<?= esc(mb_strtolower($moduleTitle), 'attr') ?>">
                            <h3 class="accordion-header bo-perm-group-head" id="<?= esc($headingId, 'attr') ?>">
                                <button class="accordion-button<?= $index === 0 ? '' : ' collapsed' ?>" type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#<?= esc($collapseId, 'attr') ?>"
                                        aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>"
                                        aria-controls="<?= esc($collapseId, 'attr') ?>">
                                    <span class="bo-perm-group-title"><?= esc($moduleTitle) ?></span>
                                    <span class="badge text-bg-light bo-perm-group-count ms-2">
                                        <?= esc(lang('Backoffice.profiles_permissions_count', [$permCount])) ?>
                                    </span>
                                </button>
                            </h3>
                            <div id="<?= esc($collapseId, 'attr') ?>"
                                 class="accordion-collapse collapse<?= $index === 0 ? ' show' : '' ?>"
                                 aria-labelledby="<?= esc($headingId, 'attr') ?>">
                                <div class="accordion-body">
                                    <div class="bo-perm-group-actions">
                                        <button class="btn btn-sm btn-bo-secondary" type="button" data-perm-group-select>
                                            <?= esc(lang('Backoffice.profiles_permissions_select_group')) ?>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-perm-group-deselect>
                                            <?= esc(lang('Backoffice.profiles_permissions_deselect_group')) ?>
                                        </button>
                                    </div>

                                    <div class="row g-3 bo-perm-list">
                                        <?php foreach ($group['permissions'] as $perm): ?>
                                            <?php
                                            $checked = isset($selectedMap[(int) $perm['id']]);
                                            $enabled = ! empty($perm['is_active']);
                                            $search  = mb_strtolower(
                                                $moduleTitle . ' '
                                                . ($perm['description'] ?? '') . ' '
                                                . ($perm['url_route'] ?? '')
                                            );
                                            ?>
                                            <div class="col-12 col-md-6 col-xl-3" data-perm-item data-search="<?= esc($search, 'attr') ?>">
                                                <label class="bo-perm-item<?= $checked ? ' is-selected' : '' ?><?= $enabled ? '' : ' is-disabled' ?>">
                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        name="permission_ids[]"
                                                        value="<?= esc($perm['id']) ?>"
                                                        data-perm-checkbox
                                                        <?= $checked ? 'checked' : '' ?>
                                                        <?= $enabled ? '' : 'disabled' ?>
                                                    >
                                                    <span class="bo-perm-item-body">
                                                        <strong><?= esc($perm['description']) ?></strong>
                                                        <code><?= esc($perm['url_route']) ?></code>
                                                        <span class="bo-status-pill <?= $enabled ? 'is-active' : 'is-inactive' ?>">
                                                            <?= esc($perm['status']) ?>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
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
