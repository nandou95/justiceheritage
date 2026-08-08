<?= $this->extend('layouts/backoffice') ?>

<?= $this->section('content') ?>

<?= view('Modules\Administration\Views\partials\flash') ?>



<section class="bo-crud-head">

    <div>

        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_complaints')) ?></p>

        <h1><?= esc(lang('Backoffice.dt_title')) ?></h1>

        <p><?= esc(lang('Backoffice.dt_lead')) ?></p>

    </div>

    <?php if (can_access('backoffice/document-types/create')): ?>
    <button class="btn btn-bo-primary" type="button" data-bs-toggle="modal" data-bs-target="#dtFormModal" data-bo-dt-create>

        <i class="bi bi-plus-lg"></i> <?= esc(lang('Backoffice.dt_new')) ?>

    </button>
    <?php endif; ?>

</section>



<section class="bo-panel bo-crud-panel">

    <form class="bo-filters" method="get" action="<?= site_url('backoffice/document-types') ?>">
        <?= view('partials/bo_filters_head', [
            'filters' => $filters ?? [],
            'filterKeys' => ['niveau_juridiction_id', 'status'],
            'resetUrl' => site_url('backoffice/document-types'),
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
                            <option value="true" <?= ($filters['status'] ?? '') === 'true' ? 'selected' : '' ?>><?= esc(lang('Backoffice.status_active')) ?></option>
                            <option value="false" <?= ($filters['status'] ?? '') === 'false' ? 'selected' : '' ?>><?= esc(lang('Backoffice.status_inactive')) ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>



    <div class="bo-table-toolbar">

        <label class="bo-table-search"><i class="bi bi-search"></i>

            <input type="search" class="form-control" id="dt-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>">

        </label>

    </div>



    <div class="table-responsive bo-table-wrap">

        <table class="table table-hover bo-table jh-datatable w-100" id="dt-table" data-page-length="10" data-order-col="0" data-order-dir="asc" data-dom="lrtip">

            <thead>

                <tr>

                    <th><?= esc(lang('Backoffice.dt_col_code')) ?></th>

                    <th><?= esc(lang('Backoffice.dt_col_description')) ?></th>

                    <th><?= esc(lang('Backoffice.dt_col_level')) ?></th>

                    <th><?= esc(lang('Backoffice.dt_col_status')) ?></th>

                    <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>

                </tr>

            </thead>

            <tbody>

            <?php foreach ($items as $row): ?>

                <tr>

                    <td><?= esc($row['code']) ?></td>

                    <td><?= esc($row['description']) ?></td>

                    <td><?= esc($row['level']) ?></td>

                    <td><span class="bo-status-pill <?= $row['is_active'] ? 'is-active' : 'is-inactive' ?>"><?= esc($row['status']) ?></span></td>

                    <td>

                        <div class="bo-action-group">

                            <?php if (can_access('backoffice/document-types/edit')): ?>
                            <button class="btn btn-bo-icon" type="button" data-bo-dt-edit

                                data-id="<?= esc($row['id']) ?>"

                                data-code="<?= esc($row['code'], 'attr') ?>"

                                data-description="<?= esc($row['description'], 'attr') ?>"

                                data-niveau="<?= esc($row['niveau_id']) ?>"

                                data-obligatoire="<?= $row['is_obligatoire'] ? '1' : '0' ?>"

                                data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.dt_action_edit'), 'attr') ?>">

                                <i class="bi bi-pencil-square"></i>

                            </button>
                            <?php endif; ?>

                            <?php if (can_access('backoffice/document-types/toggle-status')): ?>
                            <button class="btn btn-bo-icon <?= $row['is_active'] ? 'is-danger' : 'is-success' ?>" type="button"

                                data-bo-toggle-dt

                                data-id="<?= esc($row['id']) ?>"

                                data-name="<?= esc($row['code'], 'attr') ?>"

                                data-activate="<?= $row['is_active'] ? '0' : '1' ?>"

                                data-bs-toggle="tooltip"

                                title="<?= esc($row['is_active'] ? lang('Backoffice.dt_action_deactivate') : lang('Backoffice.dt_action_activate'), 'attr') ?>">

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



<div class="modal fade" id="dtFormModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">

        <form method="post" id="dtForm" class="needs-validation" novalidate><?= csrf_field() ?>

            <div class="modal-header">

                <h2 class="modal-title fs-5" id="dtFormTitle"><?= esc(lang('Backoffice.dt_new')) ?></h2>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body">

                <div class="mb-3">

                    <label class="form-label" for="code_type_document"><?= esc(lang('Backoffice.dt_field_code')) ?> *</label>

                    <input class="form-control" id="code_type_document" name="code_type_document" required>

                    <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>

                </div>

                <div class="mb-3">

                    <label class="form-label" for="libelle_type_document"><?= esc(lang('Backoffice.dt_field_description')) ?> *</label>

                    <input class="form-control" id="libelle_type_document" name="libelle_type_document" required>

                    <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>

                </div>

                <div class="mb-3">

                    <label class="form-label" for="niveau_juridiction_id"><?= esc(lang('Backoffice.dt_field_level')) ?> *</label>

                    <select class="form-select" id="niveau_juridiction_id" name="niveau_juridiction_id" required>

                        <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>

                        <?php foreach ($levels as $opt): ?>

                            <option value="<?= esc($opt['id']) ?>"><?= esc($opt['label']) ?></option>

                        <?php endforeach; ?>

                    </select>

                    <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>

                </div>

                <div class="form-check form-switch">

                    <input class="form-check-input" type="checkbox" role="switch" id="is_obligatoire" name="is_obligatoire" value="1">

                    <label class="form-check-label" for="is_obligatoire"><?= esc(lang('Backoffice.dt_field_obligatoire')) ?></label>

                </div>

            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('Backoffice.btn_cancel')) ?></button>

                <button type="submit" class="btn btn-bo-primary" id="dtFormSubmit"><?= esc(lang('Backoffice.dt_save')) ?></button>

            </div>

        </form>

    </div></div>

</div>



<div class="modal fade" id="dtStatusModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">

        <form method="post" id="dtStatusForm"><?= csrf_field() ?>

            <div class="modal-header"><h2 class="modal-title fs-5" id="dtStatusModalTitle"></h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>

            <div class="modal-body"><p class="mb-0" id="dtStatusModalMessage"></p></div>

            <div class="modal-footer">

                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('Backoffice.btn_cancel')) ?></button>

                <button type="submit" class="btn" id="dtStatusModalConfirm"></button>

            </div>

        </form>

    </div></div>

</div>



<script>

window.JH_DT_I18N = {

    createTitle: <?= json_encode(lang('Backoffice.dt_new')) ?>,

    editTitle: <?= json_encode(lang('Backoffice.dt_edit_title')) ?>,

    saveCreate: <?= json_encode(lang('Backoffice.dt_create')) ?>,

    saveEdit: <?= json_encode(lang('Backoffice.dt_save')) ?>,

    activateTitle: <?= json_encode(lang('Backoffice.dt_activate_title')) ?>,

    activateMessage: <?= json_encode(lang('Backoffice.dt_activate_message')) ?>,

    activateBtn: <?= json_encode(lang('Backoffice.dt_activate_btn')) ?>,

    deactivateTitle: <?= json_encode(lang('Backoffice.dt_deactivate_title')) ?>,

    deactivateMessage: <?= json_encode(lang('Backoffice.dt_deactivate_message')) ?>,

    deactivateBtn: <?= json_encode(lang('Backoffice.dt_deactivate_btn')) ?>,

    storeUrl: <?= json_encode(site_url('backoffice/document-types')) ?>,

    updateUrl: <?= json_encode(site_url('backoffice/document-types/__ID__')) ?>,

    toggleUrl: <?= json_encode(site_url('backoffice/document-types/__ID__/toggle-status')) ?>

};

</script>

<?= $this->endSection() ?>

