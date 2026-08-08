<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_complaints')) ?></p>
        <h1><?= esc(lang('Backoffice.csc_title')) ?></h1>
        <p><?= esc(lang('Backoffice.csc_lead')) ?></p>
    </div>
    <?php if (can_access('backoffice/complaint-stage-configs/create')): ?>
    <button class="btn btn-bo-primary" type="button" data-bs-toggle="modal" data-bs-target="#cscFormModal" data-bo-csc-create>
        <i class="bi bi-plus-lg"></i> <?= esc(lang('Backoffice.csc_new')) ?>
    </button>
    <?php endif; ?>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-filters" method="get" action="<?= site_url('backoffice/complaint-stage-configs') ?>">
        <?= view('partials/bo_filters_head', [
            'filters' => $filters ?? [],
            'filterKeys' => ['niveau_juridiction_id', 'status'],
            'resetUrl' => site_url('backoffice/complaint-stage-configs'),
            'lead' => lang('Backoffice.filters_lead'),
        ]) ?>
        <div class="bo-filters-body">
            <div class="bo-filter-group">
                <p class="bo-filter-group-title"><i class="bi bi-building" aria-hidden="true"></i> <?= esc(lang('Backoffice.filter_group_court')) ?></p>
                <div class="bo-filter-fields">
                    <div class="bo-filter-field">
                        <label class="form-label"><?= esc(lang('Backoffice.csc_filter_level_current')) ?></label>
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
            <input type="search" class="form-control" id="csc-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>">
        </label>
    </div>

    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="csc-table" data-page-length="10" data-order-col="0" data-order-dir="asc" data-dom="lrtip">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.csc_col_stage_current')) ?></th>
                    <th><?= esc(lang('Backoffice.csc_col_level_current')) ?></th>
                    <th><?= esc(lang('Backoffice.csc_col_action_current')) ?></th>
                    <th><?= esc(lang('Backoffice.csc_col_stage_next')) ?></th>
                    <th><?= esc(lang('Backoffice.csc_col_level_next')) ?></th>
                    <th><?= esc(lang('Backoffice.csc_col_route')) ?></th>
                    <th><?= esc(lang('Backoffice.csc_col_status')) ?></th>
                    <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $row): ?>
                <tr>
                    <td><?= esc($row['etape_actuel']) ?></td>
                    <td><?= esc($row['niveau_actuel']) ?></td>
                    <td><?= esc($row['action_actuel']) ?></td>
                    <td><?= esc($row['etape_suivant']) ?></td>
                    <td><?= esc($row['niveau_suivant']) ?></td>
                    <td><code class="bo-route-code"><?= esc($row['url_route']) ?></code></td>
                    <td><span class="bo-status-pill <?= $row['is_active'] ? 'is-active' : 'is-inactive' ?>"><?= esc($row['status']) ?></span></td>
                    <td>
                        <div class="bo-action-group">
                            <?php if (can_access('backoffice/complaint-stage-configs/edit')): ?>
                            <button class="btn btn-bo-icon" type="button" data-bo-csc-edit
                                data-id="<?= esc($row['id']) ?>"
                                data-niveau-actuel="<?= esc($row['niveau_actuel_id']) ?>"
                                data-etape-actuel="<?= esc($row['etape_actuel_id']) ?>"
                                data-action-id="<?= esc($row['action_id']) ?>"
                                data-niveau-suivant="<?= esc($row['niveau_suivant_id']) ?>"
                                data-etape-suivant="<?= esc($row['etape_suivant_id']) ?>"
                                data-url-route="<?= esc($row['url_route'], 'attr') ?>"
                                data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.csc_action_edit'), 'attr') ?>">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <?php endif; ?>
                            <?php if (can_access('backoffice/complaint-stage-configs/toggle-status')): ?>
                            <button class="btn btn-bo-icon <?= $row['is_active'] ? 'is-danger' : 'is-success' ?>" type="button"
                                data-bo-toggle-csc
                                data-id="<?= esc($row['id']) ?>"
                                data-name="<?= esc($row['etape_actuel'] . ' → ' . $row['etape_suivant'], 'attr') ?>"
                                data-activate="<?= $row['is_active'] ? '0' : '1' ?>"
                                data-bs-toggle="tooltip"
                                title="<?= esc($row['is_active'] ? lang('Backoffice.csc_action_deactivate') : lang('Backoffice.csc_action_activate'), 'attr') ?>">
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

<div class="modal fade" id="cscFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
        <form method="post" id="cscForm" class="needs-validation" novalidate
              data-api-stages="<?= esc(site_url('backoffice/api/complaint-stages'), 'attr') ?>"
              data-api-actions="<?= esc(site_url('backoffice/api/complaint-stage-actions'), 'attr') ?>">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="cscFormTitle"><?= esc(lang('Backoffice.csc_new')) ?></h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12"><h3 class="bo-form-section-title"><?= esc(lang('Backoffice.csc_section_current')) ?></h3></div>
                    <div class="col-md-6">
                        <label class="form-label" for="niveau_juridiction_actuel_id"><?= esc(lang('Backoffice.csc_field_level_current')) ?> *</label>
                        <select class="form-select" id="niveau_juridiction_actuel_id" name="niveau_juridiction_actuel_id" required>
                            <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                            <?php foreach ($levels as $opt): ?>
                                <option value="<?= esc($opt['id']) ?>"><?= esc($opt['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="etape_plainte_actuel_id"><?= esc(lang('Backoffice.csc_field_stage_current')) ?> *</label>
                        <select class="form-select" id="etape_plainte_actuel_id" name="etape_plainte_actuel_id" required>
                            <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                        </select>
                        <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="etape_plainte_action_id"><?= esc(lang('Backoffice.csc_field_action')) ?> *</label>
                        <select class="form-select" id="etape_plainte_action_id" name="etape_plainte_action_id" required>
                            <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                        </select>
                        <div class="form-text"><?= esc(lang('Backoffice.csc_hint_action')) ?></div>
                        <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                    </div>
                    <div class="col-12"><hr class="bo-form-divider"><h3 class="bo-form-section-title"><?= esc(lang('Backoffice.csc_section_next')) ?></h3></div>
                    <div class="col-md-6">
                        <label class="form-label" for="niveau_juridiction_suivant_id"><?= esc(lang('Backoffice.csc_field_level_next')) ?> *</label>
                        <select class="form-select" id="niveau_juridiction_suivant_id" name="niveau_juridiction_suivant_id" required>
                            <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                            <?php foreach ($levels as $opt): ?>
                                <option value="<?= esc($opt['id']) ?>"><?= esc($opt['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="etape_plainte_suivant_id"><?= esc(lang('Backoffice.csc_field_stage_next')) ?> *</label>
                        <select class="form-select" id="etape_plainte_suivant_id" name="etape_plainte_suivant_id" required>
                            <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                        </select>
                        <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="url_route"><?= esc(lang('Backoffice.csc_field_route')) ?> *</label>
                        <input class="form-control" id="url_route" name="url_route" required>
                        <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('Backoffice.btn_cancel')) ?></button>
                <button type="submit" class="btn btn-bo-primary" id="cscFormSubmit"><?= esc(lang('Backoffice.csc_save')) ?></button>
            </div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="cscStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form method="post" id="cscStatusForm"><?= csrf_field() ?>
            <div class="modal-header"><h2 class="modal-title fs-5" id="cscStatusModalTitle"></h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><p class="mb-0" id="cscStatusModalMessage"></p></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('Backoffice.btn_cancel')) ?></button>
                <button type="submit" class="btn" id="cscStatusModalConfirm"></button>
            </div>
        </form>
    </div></div>
</div>

<script>
window.JH_CSC_I18N = {
    createTitle: <?= json_encode(lang('Backoffice.csc_new')) ?>,
    editTitle: <?= json_encode(lang('Backoffice.csc_edit_title')) ?>,
    saveCreate: <?= json_encode(lang('Backoffice.csc_create')) ?>,
    saveEdit: <?= json_encode(lang('Backoffice.csc_save')) ?>,
    activateTitle: <?= json_encode(lang('Backoffice.csc_activate_title')) ?>,
    activateMessage: <?= json_encode(lang('Backoffice.csc_activate_message')) ?>,
    activateBtn: <?= json_encode(lang('Backoffice.csc_activate_btn')) ?>,
    deactivateTitle: <?= json_encode(lang('Backoffice.csc_deactivate_title')) ?>,
    deactivateMessage: <?= json_encode(lang('Backoffice.csc_deactivate_message')) ?>,
    deactivateBtn: <?= json_encode(lang('Backoffice.csc_deactivate_btn')) ?>,
    storeUrl: <?= json_encode(site_url('backoffice/complaint-stage-configs')) ?>,
    updateUrl: <?= json_encode(site_url('backoffice/complaint-stage-configs/__ID__')) ?>,
    toggleUrl: <?= json_encode(site_url('backoffice/complaint-stage-configs/__ID__/toggle-status')) ?>,
    stagesUrl: <?= json_encode(site_url('backoffice/api/complaint-stages')) ?>,
    actionsUrl: <?= json_encode(site_url('backoffice/api/complaint-stage-actions')) ?>
};
</script>
<?= $this->endSection() ?>
