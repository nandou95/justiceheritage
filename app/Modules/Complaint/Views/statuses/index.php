<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_complaints')) ?></p>
        <h1><?= esc(lang('Backoffice.cst_title')) ?></h1>
        <p><?= esc(lang('Backoffice.cst_lead')) ?></p>
    </div>
    <?php if (can_access('backoffice/complaint-statuses/create')): ?>
    <button class="btn btn-bo-primary" type="button" data-bs-toggle="modal" data-bs-target="#cstFormModal" data-bo-cst-create>
        <i class="bi bi-plus-lg"></i> <?= esc(lang('Backoffice.cst_new')) ?>
    </button>
    <?php endif; ?>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-filters" method="get" action="<?= site_url('backoffice/complaint-statuses') ?>">
        <?= view('partials/bo_filters_head', [
            'filters' => array_merge($filters ?? [], ['status' => $filters['status'] ?? $status ?? '']),
            'filterKeys' => ['niveau_juridiction_id', 'status'],
            'resetUrl' => site_url('backoffice/complaint-statuses'),
            'lead' => lang('Backoffice.filters_lead'),
        ]) ?>
        <div class="bo-filters-body">
            <div class="bo-filter-group">
                <p class="bo-filter-group-title"><i class="bi bi-building" aria-hidden="true"></i> <?= esc(lang('Backoffice.filter_group_court')) ?></p>
                <div class="bo-filter-fields">
                    <div class="bo-filter-field">
                        <label class="form-label"><?= esc(lang('Backoffice.filter_jurisdiction_level')) ?></label>
                        <select class="form-select" name="niveau_juridiction_id">
                            <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                            <?php foreach ($levels as $opt): ?>
                                <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['niveau_juridiction_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="bo-filter-group">
                <p class="bo-filter-group-title"><i class="bi bi-toggle2-on" aria-hidden="true"></i> <?= esc(lang('Backoffice.filter_group_status')) ?></p>
                <div class="bo-filter-fields">
                    <div class="bo-filter-field">
                        <label class="form-label"><?= esc(lang('Backoffice.filter_status')) ?></label>
                        <select class="form-select" name="status">
                            <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                            <option value="true" <?= ($filters['status'] ?? $status ?? '') === 'true' ? 'selected' : '' ?>><?= esc(lang('Backoffice.status_active')) ?></option>
                            <option value="false" <?= ($filters['status'] ?? $status ?? '') === 'false' ? 'selected' : '' ?>><?= esc(lang('Backoffice.status_inactive')) ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="bo-table-toolbar">
        <label class="bo-table-search"><i class="bi bi-search"></i>
            <input type="search" class="form-control" id="cst-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>">
        </label>
    </div>

    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="cst-table" data-page-length="10" data-order='[[1,"asc"],[0,"asc"]]' data-dom="lrtip">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.cst_col_description')) ?></th>
                    <th><?= esc(lang('Backoffice.cst_col_level')) ?></th>
                    <th><?= esc(lang('Backoffice.cst_col_status')) ?></th>
                    <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $row): ?>
                <tr>
                    <td><?= esc($row['description']) ?></td>
                    <td data-order="<?= (int) $row['niveau_id'] ?>"><?= esc($row['level']) ?></td>
                    <td><span class="bo-status-pill <?= $row['is_active'] ? 'is-active' : 'is-inactive' ?>"><?= esc($row['status']) ?></span></td>
                    <td>
                        <div class="bo-action-group">
                            <?php if (can_access('backoffice/complaint-statuses/edit')): ?>
                            <button class="btn btn-bo-icon" type="button" data-bo-cst-edit
                                data-id="<?= esc($row['id']) ?>"
                                data-description="<?= esc($row['description'], 'attr') ?>"
                                data-niveau="<?= esc($row['niveau_id'], 'attr') ?>"
                                data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.cst_action_edit'), 'attr') ?>">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <?php endif; ?>
                            <?php if (can_access('backoffice/complaint-statuses/toggle-status')): ?>
                            <button class="btn btn-bo-icon <?= $row['is_active'] ? 'is-danger' : 'is-success' ?>" type="button"
                                data-bo-toggle-cst
                                data-id="<?= esc($row['id']) ?>"
                                data-name="<?= esc($row['description'], 'attr') ?>"
                                data-activate="<?= $row['is_active'] ? '0' : '1' ?>"
                                data-bs-toggle="tooltip"
                                title="<?= esc($row['is_active'] ? lang('Backoffice.cst_action_deactivate') : lang('Backoffice.cst_action_activate'), 'attr') ?>">
                                <i class="bi <?= $row['is_active'] ? 'bi-toggle-off' : 'bi-toggle-on' ?>"></i>
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

<div class="modal fade" id="cstFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form method="post" id="cstForm" class="needs-validation" novalidate><?= csrf_field() ?>
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="cstFormTitle"><?= esc(lang('Backoffice.cst_new')) ?></h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="description_statut_plainte"><?= esc(lang('Backoffice.cst_field_description')) ?> *</label>
                    <input class="form-control" id="description_statut_plainte" name="description_statut_plainte" required>
                    <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                </div>
                <div class="mb-0">
                    <label class="form-label" for="niveau_juridiction_id"><?= esc(lang('Backoffice.cst_field_level')) ?> *</label>
                    <select class="form-select" id="niveau_juridiction_id" name="niveau_juridiction_id" required>
                        <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                        <?php foreach ($levels as $opt): ?>
                            <option value="<?= esc($opt['id']) ?>"><?= esc($opt['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('Backoffice.btn_cancel')) ?></button>
                <button type="submit" class="btn btn-bo-primary" id="cstFormSubmit"><?= esc(lang('Backoffice.cst_save')) ?></button>
            </div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="cstStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form method="post" id="cstStatusForm"><?= csrf_field() ?>
            <div class="modal-header"><h2 class="modal-title fs-5" id="cstStatusModalTitle"></h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><p class="mb-0" id="cstStatusModalMessage"></p></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('Backoffice.btn_cancel')) ?></button>
                <button type="submit" class="btn" id="cstStatusModalConfirm"></button>
            </div>
        </form>
    </div></div>
</div>

<script>
window.JH_CST_I18N = {
    createTitle: <?= json_encode(lang('Backoffice.cst_new')) ?>,
    editTitle: <?= json_encode(lang('Backoffice.cst_edit_title')) ?>,
    saveCreate: <?= json_encode(lang('Backoffice.cst_create')) ?>,
    saveEdit: <?= json_encode(lang('Backoffice.cst_save')) ?>,
    activateTitle: <?= json_encode(lang('Backoffice.cst_activate_title')) ?>,
    activateMessage: <?= json_encode(lang('Backoffice.cst_activate_message')) ?>,
    activateBtn: <?= json_encode(lang('Backoffice.cst_activate_btn')) ?>,
    deactivateTitle: <?= json_encode(lang('Backoffice.cst_deactivate_title')) ?>,
    deactivateMessage: <?= json_encode(lang('Backoffice.cst_deactivate_message')) ?>,
    deactivateBtn: <?= json_encode(lang('Backoffice.cst_deactivate_btn')) ?>,
    storeUrl: <?= json_encode(site_url('backoffice/complaint-statuses')) ?>,
    updateUrl: <?= json_encode(site_url('backoffice/complaint-statuses/__ID__')) ?>,
    toggleUrl: <?= json_encode(site_url('backoffice/complaint-statuses/__ID__/toggle-status')) ?>
};
</script>
<?= $this->endSection() ?>
