<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>
<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_court_jurisdiction')) ?></p>
        <h1><?= esc(lang('Backoffice.jlc_title')) ?></h1>
        <p><?= esc(lang('Backoffice.jlc_lead')) ?></p>
    </div>
    <?php if (can_access('backoffice/jurisdiction-level-configs/create')): ?>
    <button class="btn btn-bo-primary" type="button" data-bs-toggle="modal" data-bs-target="#jlcFormModal" data-bo-jlc-create>
        <i class="bi bi-plus-lg"></i> <?= esc(lang('Backoffice.jlc_new')) ?>
    </button>
    <?php endif; ?>
</section>
<section class="bo-panel bo-crud-panel">
    <form class="bo-filters" method="get" action="<?= site_url('backoffice/jurisdiction-level-configs') ?>">
        <div class="row g-2 align-items-end">
            <div class="col-md-3"><label class="form-label"><?= esc(lang('Backoffice.filter_status')) ?></label>
                <select class="form-select" name="status">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <option value="true" <?= ($status ?? '') === 'true' ? 'selected' : '' ?>><?= esc(lang('Backoffice.status_active')) ?></option>
                    <option value="false" <?= ($status ?? '') === 'false' ? 'selected' : '' ?>><?= esc(lang('Backoffice.status_inactive')) ?></option>
                </select></div>
        </div>
    </form>
    <div class="bo-table-toolbar"><label class="bo-table-search"><i class="bi bi-search"></i><input type="search" class="form-control" id="jlc-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>"></label></div>
    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="jlc-table" data-page-length="10" data-order-col="0" data-order-dir="asc" data-dom="lrtip">
            <thead><tr>
                <th><?= esc(lang('Backoffice.jlc_col_level')) ?></th>
                <th><?= esc(lang('Backoffice.jlc_col_parent')) ?></th>
                <th><?= esc(lang('Backoffice.jlc_col_status')) ?></th>
                <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($items as $row): ?>
                <tr>
                    <td><?= esc($row['level']) ?></td>
                    <td><?= esc($row['parent_level']) ?></td>
                    <td><span class="bo-status-pill <?= $row['is_active'] ? 'is-active' : 'is-inactive' ?>"><?= esc($row['status']) ?></span></td>
                    <td><div class="bo-action-group">
                        <?php if (can_access('backoffice/jurisdiction-level-configs/edit')): ?>
                        <button class="btn btn-bo-icon" type="button" data-bo-jlc-edit data-id="<?= esc($row['id']) ?>" data-niveau="<?= esc($row['niveau_juridiction_id']) ?>" data-parent="<?= esc($row['niveau_juridiction_parent_id']) ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.jlc_action_edit'), 'attr') ?>"><i class="bi bi-pencil-square"></i></button>
                        <?php endif; ?>
                        <?php if (can_access('backoffice/jurisdiction-level-configs/toggle-status')): ?>
                        <button class="btn btn-bo-icon <?= $row['is_active'] ? 'is-danger' : 'is-success' ?>" type="button" data-bo-toggle-jlc data-id="<?= esc($row['id']) ?>" data-name="<?= esc($row['level'], 'attr') ?>" data-activate="<?= $row['is_active'] ? '0' : '1' ?>" data-bs-toggle="tooltip" title="<?= esc($row['is_active'] ? lang('Backoffice.jlc_action_deactivate') : lang('Backoffice.jlc_action_activate'), 'attr') ?>">
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

<div class="modal fade" id="jlcFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form method="post" id="jlcForm" class="needs-validation" novalidate><?= csrf_field() ?>
            <div class="modal-header"><h2 class="modal-title fs-5" id="jlcFormTitle"><?= esc(lang('Backoffice.jlc_new')) ?></h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label" for="niveau_juridiction_id"><?= esc(lang('Backoffice.jlc_field_level')) ?> *</label>
                    <select class="form-select" id="niveau_juridiction_id" name="niveau_juridiction_id" required>
                        <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                        <?php foreach ($niveaux as $opt): ?><option value="<?= esc($opt['id']) ?>"><?= esc($opt['label']) ?></option><?php endforeach; ?>
                    </select><div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div></div>
                <div class="mb-0"><label class="form-label" for="niveau_juridiction_parent_id"><?= esc(lang('Backoffice.jlc_field_parent')) ?> *</label>
                    <select class="form-select" id="niveau_juridiction_parent_id" name="niveau_juridiction_parent_id" required>
                        <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                        <?php foreach ($niveaux as $opt): ?><option value="<?= esc($opt['id']) ?>"><?= esc($opt['label']) ?></option><?php endforeach; ?>
                    </select><div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('Backoffice.btn_cancel')) ?></button>
                <button type="submit" class="btn btn-bo-primary" id="jlcFormSubmit"><?= esc(lang('Backoffice.jlc_save')) ?></button>
            </div>
        </form>
    </div></div>
</div>
<div class="modal fade" id="jlcStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form method="post" id="jlcStatusForm"><?= csrf_field() ?>
            <div class="modal-header"><h2 class="modal-title fs-5" id="jlcStatusModalTitle"></h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><p class="mb-0" id="jlcStatusModalMessage"></p></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('Backoffice.btn_cancel')) ?></button>
                <button type="submit" class="btn" id="jlcStatusModalConfirm"></button>
            </div>
        </form>
    </div></div>
</div>
<script>
window.JH_JLC_I18N = {
    createTitle: <?= json_encode(lang('Backoffice.jlc_new')) ?>,
    editTitle: <?= json_encode(lang('Backoffice.jlc_edit_title')) ?>,
    saveCreate: <?= json_encode(lang('Backoffice.jlc_create')) ?>,
    saveEdit: <?= json_encode(lang('Backoffice.jlc_save')) ?>,
    activateTitle: <?= json_encode(lang('Backoffice.jlc_activate_title')) ?>,
    activateMessage: <?= json_encode(lang('Backoffice.jlc_activate_message')) ?>,
    activateBtn: <?= json_encode(lang('Backoffice.jlc_activate_btn')) ?>,
    deactivateTitle: <?= json_encode(lang('Backoffice.jlc_deactivate_title')) ?>,
    deactivateMessage: <?= json_encode(lang('Backoffice.jlc_deactivate_message')) ?>,
    deactivateBtn: <?= json_encode(lang('Backoffice.jlc_deactivate_btn')) ?>,
    storeUrl: <?= json_encode(site_url('backoffice/jurisdiction-level-configs')) ?>,
    updateUrl: <?= json_encode(site_url('backoffice/jurisdiction-level-configs/__ID__')) ?>,
    toggleUrl: <?= json_encode(site_url('backoffice/jurisdiction-level-configs/__ID__/toggle-status')) ?>
};
</script>
<?= $this->endSection() ?>
