<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>
<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_court_jurisdiction')) ?></p>
        <h1><?= esc(lang('Backoffice.jl_title')) ?></h1>
        <p><?= esc(lang('Backoffice.jl_lead')) ?></p>
    </div>
    <?php if (can_access('backoffice/jurisdiction-levels/create')): ?>
    <button class="btn btn-bo-primary" type="button" data-bs-toggle="modal" data-bs-target="#jlFormModal" data-bo-jl-create>
        <i class="bi bi-plus-lg"></i> <?= esc(lang('Backoffice.jl_new')) ?>
    </button>
    <?php endif; ?>
</section>
<section class="bo-panel bo-crud-panel">
    <form class="bo-filters" method="get" action="<?= site_url('backoffice/jurisdiction-levels') ?>">
        <div class="row g-2 align-items-end">
            <div class="col-md-3"><label class="form-label"><?= esc(lang('Backoffice.filter_status')) ?></label>
                <select class="form-select" name="status">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <option value="true" <?= ($status ?? '') === 'true' ? 'selected' : '' ?>><?= esc(lang('Backoffice.status_active')) ?></option>
                    <option value="false" <?= ($status ?? '') === 'false' ? 'selected' : '' ?>><?= esc(lang('Backoffice.status_inactive')) ?></option>
                </select></div>
        </div>
    </form>
    <div class="bo-table-toolbar"><label class="bo-table-search"><i class="bi bi-search"></i><input type="search" class="form-control" id="jl-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>"></label></div>
    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="jl-table" data-page-length="10" data-order-col="0" data-order-dir="asc" data-dom="lrtip">
            <thead><tr>
                <th><?= esc(lang('Backoffice.jl_col_description')) ?></th>
                <th><?= esc(lang('Backoffice.jl_col_appeal')) ?></th>
                <th><?= esc(lang('Backoffice.jl_col_status')) ?></th>
                <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($items as $row): ?>
                <tr>
                    <td><?= esc($row['description']) ?></td>
                    <td><?= esc($row['is_appeal']) ?></td>
                    <td><span class="bo-status-pill <?= $row['is_active'] ? 'is-active' : 'is-inactive' ?>"><?= esc($row['status']) ?></span></td>
                    <td><div class="bo-action-group">
                        <?php if (can_access('backoffice/jurisdiction-levels/edit')): ?>
                        <button class="btn btn-bo-icon" type="button" data-bo-jl-edit data-id="<?= esc($row['id']) ?>" data-description="<?= esc($row['description'], 'attr') ?>" data-recours="<?= $row['is_recours'] ? '1' : '0' ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.jl_action_edit'), 'attr') ?>"><i class="bi bi-pencil-square"></i></button>
                        <?php endif; ?>
                        <?php if (can_access('backoffice/jurisdiction-levels/toggle-status')): ?>
                        <button class="btn btn-bo-icon <?= $row['is_active'] ? 'is-danger' : 'is-success' ?>" type="button" data-bo-toggle-jl data-id="<?= esc($row['id']) ?>" data-name="<?= esc($row['description'], 'attr') ?>" data-activate="<?= $row['is_active'] ? '0' : '1' ?>" data-bs-toggle="tooltip" title="<?= esc($row['is_active'] ? lang('Backoffice.jl_action_deactivate') : lang('Backoffice.jl_action_activate'), 'attr') ?>">
                            <i class="bi <?= $row['is_active'] ? 'bi-toggle-off' : 'bi-toggle-on' ?>"></i>
                        </button>
                        <?php endif; ?>
                    </div></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<div class="modal fade" id="jlFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form method="post" id="jlForm" class="needs-validation" novalidate><?= csrf_field() ?>
            <div class="modal-header"><h2 class="modal-title fs-5" id="jlFormTitle"><?= esc(lang('Backoffice.jl_new')) ?></h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label" for="desc_niveau_juridiction"><?= esc(lang('Backoffice.jl_field_description')) ?> *</label>
                    <input class="form-control" id="desc_niveau_juridiction" name="desc_niveau_juridiction" required><div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div></div>
                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="is_recours" name="is_recours" value="1">
                    <label class="form-check-label" for="is_recours"><?= esc(lang('Backoffice.jl_field_appeal')) ?></label></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('Backoffice.btn_cancel')) ?></button>
                <button type="submit" class="btn btn-bo-primary" id="jlFormSubmit"><?= esc(lang('Backoffice.jl_save')) ?></button>
            </div>
        </form>
    </div></div>
</div>
<div class="modal fade" id="jlStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form method="post" id="jlStatusForm"><?= csrf_field() ?>
            <div class="modal-header"><h2 class="modal-title fs-5" id="jlStatusModalTitle"></h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><p class="mb-0" id="jlStatusModalMessage"></p></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('Backoffice.btn_cancel')) ?></button>
                <button type="submit" class="btn" id="jlStatusModalConfirm"></button>
            </div>
        </form>
    </div></div>
</div>
<script>
window.JH_JL_I18N = {
    createTitle: <?= json_encode(lang('Backoffice.jl_new')) ?>,
    editTitle: <?= json_encode(lang('Backoffice.jl_edit_title')) ?>,
    saveCreate: <?= json_encode(lang('Backoffice.jl_create')) ?>,
    saveEdit: <?= json_encode(lang('Backoffice.jl_save')) ?>,
    activateTitle: <?= json_encode(lang('Backoffice.jl_activate_title')) ?>,
    activateMessage: <?= json_encode(lang('Backoffice.jl_activate_message')) ?>,
    activateBtn: <?= json_encode(lang('Backoffice.jl_activate_btn')) ?>,
    deactivateTitle: <?= json_encode(lang('Backoffice.jl_deactivate_title')) ?>,
    deactivateMessage: <?= json_encode(lang('Backoffice.jl_deactivate_message')) ?>,
    deactivateBtn: <?= json_encode(lang('Backoffice.jl_deactivate_btn')) ?>,
    storeUrl: <?= json_encode(site_url('backoffice/jurisdiction-levels')) ?>,
    updateUrl: <?= json_encode(site_url('backoffice/jurisdiction-levels/__ID__')) ?>,
    toggleUrl: <?= json_encode(site_url('backoffice/jurisdiction-levels/__ID__/toggle-status')) ?>
};
</script>
<?= $this->endSection() ?>
