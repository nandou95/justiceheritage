<?= $this->extend('layouts/backoffice') ?>

<?= $this->section('content') ?>

<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_administration')) ?></p>
        <h1><?= esc(lang('Backoffice.perm_title')) ?></h1>
        <p><?= esc(lang('Backoffice.perm_lead')) ?></p>
    </div>
    <?php if (can_access('backoffice/permissions/create')): ?>
    <button class="btn btn-bo-primary" type="button" data-bs-toggle="modal" data-bs-target="#permissionFormModal" data-bo-perm-create>
        <i class="bi bi-plus-lg" aria-hidden="true"></i>
        <?= esc(lang('Backoffice.perm_new')) ?>
    </button>
    <?php endif; ?>
</section>

<section class="bo-panel bo-crud-panel" data-bo-perm-live>
    <div class="bo-filters">
        <?= view('partials/bo_filters_head', [
            'filters' => [],
            'filterKeys' => [],
            'resetUrl' => site_url('backoffice/permissions'),
            'lead' => lang('Backoffice.filters_lead'),
            'showMeta' => false,
        ]) ?>
        <div class="bo-filters-body bo-filters-body--single">
            <div class="bo-filter-group">
                <p class="bo-filter-group-title"><i class="bi bi-toggle2-on" aria-hidden="true"></i> <?= esc(lang('Backoffice.filter_group_status')) ?></p>
                <div class="bo-filter-fields">
                    <div class="bo-filter-field">
                        <label class="form-label" for="filter_perm_status"><?= esc(lang('Backoffice.filter_status')) ?></label>
                        <select class="form-select" id="filter_perm_status" name="status" data-perm-filter="status">
                            <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                            <option value="true"><?= esc(lang('Backoffice.perm_status_enabled')) ?></option>
                            <option value="false"><?= esc(lang('Backoffice.perm_status_disabled')) ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bo-table-toolbar">
        <label class="bo-table-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" class="form-control" id="permissions-table-search" data-perm-filter="q" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>" aria-label="<?= esc(lang('Backoffice.search_placeholder')) ?>">
        </label>
    </div>

    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="permissions-table" data-page-length="10" data-order-col="0" data-order-dir="asc" data-dom="lrtip" data-bo-perm-table>
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.perm_col_description')) ?></th>
                    <th><?= esc(lang('Backoffice.perm_col_route')) ?></th>
                    <th><?= esc(lang('Backoffice.perm_col_status')) ?></th>
                    <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($permissions as $row): ?>
                    <tr>
                        <td><?= esc($row['description']) ?></td>
                        <td><code class="bo-route-code"><?= esc($row['url_route']) ?></code></td>
                        <td>
                            <span class="bo-status-pill <?= $row['is_active'] ? 'is-active' : 'is-inactive' ?>">
                                <?= esc($row['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="bo-action-group">
                                <?php if (can_access('backoffice/permissions/edit')): ?>
                                <button
                                    class="btn btn-bo-icon"
                                    type="button"
                                    data-bo-perm-edit
                                    data-id="<?= esc($row['id']) ?>"
                                    data-description="<?= esc($row['description'], 'attr') ?>"
                                    data-route="<?= esc($row['url_route'], 'attr') ?>"
                                    data-bs-toggle="tooltip"
                                    title="<?= esc(lang('Backoffice.perm_action_edit'), 'attr') ?>"
                                >
                                    <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                    <span class="visually-hidden"><?= esc(lang('Backoffice.perm_action_edit')) ?></span>
                                </button>
                                <?php endif; ?>
                                <?php if (can_access('backoffice/permissions/toggle-status')): ?>
                                <button
                                    class="btn btn-bo-icon <?= $row['is_active'] ? 'is-danger' : 'is-success' ?>"
                                    type="button"
                                    data-bo-toggle-perm
                                    data-id="<?= esc($row['id']) ?>"
                                    data-description="<?= esc($row['description'], 'attr') ?>"
                                    data-activate="<?= $row['is_active'] ? '0' : '1' ?>"
                                    data-bs-toggle="tooltip"
                                    title="<?= esc($row['is_active'] ? lang('Backoffice.perm_action_deactivate') : lang('Backoffice.perm_action_activate'), 'attr') ?>"
                                >
                                    <i class="bi <?= $row['is_active'] ? 'bi-toggle-off' : 'bi-toggle-on' ?>" aria-hidden="true"></i>
                                    <span class="visually-hidden"><?= esc($row['is_active'] ? lang('Backoffice.perm_action_deactivate') : lang('Backoffice.perm_action_activate')) ?></span>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<div class="modal fade" id="permissionFormModal" tabindex="-1" aria-labelledby="permissionFormModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" id="permissionForm" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="permissionFormModalTitle"><?= esc(lang('Backoffice.perm_new')) ?></h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc(lang('Backoffice.close_menu'), 'attr') ?>"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="description_permission"><?= esc(lang('Backoffice.perm_field_description')) ?> *</label>
                        <input class="form-control" type="text" id="description_permission" name="description_permission" required>
                        <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="url_route"><?= esc(lang('Backoffice.perm_field_route')) ?> *</label>
                        <input class="form-control" type="text" id="url_route" name="url_route" required>
                        <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('Backoffice.btn_cancel')) ?></button>
                    <button type="submit" class="btn btn-bo-primary" id="permissionFormSubmit"><?= esc(lang('Backoffice.perm_save')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="permissionStatusModal" tabindex="-1" aria-labelledby="permissionStatusModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" id="permissionStatusForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="permissionStatusModalTitle"></h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc(lang('Backoffice.close_menu'), 'attr') ?>"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0" id="permissionStatusModalMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('Backoffice.btn_cancel')) ?></button>
                    <button type="submit" class="btn" id="permissionStatusModalConfirm"></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.JH_PERM_I18N = {
    createTitle: <?= json_encode(lang('Backoffice.perm_new')) ?>,
    editTitle: <?= json_encode(lang('Backoffice.perm_edit_title')) ?>,
    saveCreate: <?= json_encode(lang('Backoffice.perm_create')) ?>,
    saveEdit: <?= json_encode(lang('Backoffice.perm_save')) ?>,
    activateTitle: <?= json_encode(lang('Backoffice.perm_activate_title')) ?>,
    activateMessage: <?= json_encode(lang('Backoffice.perm_activate_message')) ?>,
    activateBtn: <?= json_encode(lang('Backoffice.perm_activate_btn')) ?>,
    deactivateTitle: <?= json_encode(lang('Backoffice.perm_deactivate_title')) ?>,
    deactivateMessage: <?= json_encode(lang('Backoffice.perm_deactivate_message')) ?>,
    deactivateBtn: <?= json_encode(lang('Backoffice.perm_deactivate_btn')) ?>,
    editAction: <?= json_encode(lang('Backoffice.perm_action_edit')) ?>,
    activateAction: <?= json_encode(lang('Backoffice.perm_action_activate')) ?>,
    deactivateAction: <?= json_encode(lang('Backoffice.perm_action_deactivate')) ?>,
    empty: <?= json_encode(lang('Backoffice.perm_empty')) ?>,
    storeUrl: <?= json_encode(site_url('backoffice/permissions')) ?>,
    updateUrl: <?= json_encode(site_url('backoffice/permissions/__ID__')) ?>,
    toggleUrl: <?= json_encode(site_url('backoffice/permissions/__ID__/toggle-status')) ?>,
    listUrl: <?= json_encode(site_url('backoffice/api/permissions')) ?>,
    csrfUrl: <?= json_encode(site_url('backoffice/api/csrf')) ?>
};
</script>

<?= $this->endSection() ?>
