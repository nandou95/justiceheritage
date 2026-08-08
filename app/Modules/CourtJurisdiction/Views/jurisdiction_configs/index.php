<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>
<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_court_jurisdiction')) ?></p>
        <h1><?= esc(lang('Backoffice.cjc_title')) ?></h1>
        <p><?= esc(lang('Backoffice.cjc_lead')) ?></p>
    </div>
    <?php if (can_access('backoffice/court-jurisdiction-configs/create')): ?>
    <button class="btn btn-bo-primary" type="button" data-bs-toggle="modal" data-bs-target="#cjcFormModal" data-bo-cjc-create>
        <i class="bi bi-plus-lg"></i> <?= esc(lang('Backoffice.cjc_new')) ?>
    </button>
    <?php endif; ?>
</section>
<section class="bo-panel bo-crud-panel">
    <form class="bo-filters" method="get" action="<?= site_url('backoffice/court-jurisdiction-configs') ?>" data-bo-cjc-filters data-api-communes="<?= esc(site_url('api/communes'), 'attr') ?>">
        <?= view('partials/bo_filters_head', [
            'filters' => $filters,
            'filterKeys' => ['province_id', 'commune_id', 'niveau_juridiction_id', 'status'],
            'resetUrl' => site_url('backoffice/court-jurisdiction-configs'),
            'lead' => lang('Backoffice.filters_lead'),
        ]) ?>
        <div class="bo-filters-body">
            <div class="bo-filter-group">
                <p class="bo-filter-group-title"><i class="bi bi-geo-alt" aria-hidden="true"></i> <?= esc(lang('Backoffice.filter_group_location')) ?></p>
                <div class="bo-filter-fields">
                    <div class="bo-filter-field">
                        <label class="form-label"><?= esc(lang('Backoffice.filter_province')) ?></label>
                        <select class="form-select" name="province_id" data-filter="province">
                            <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                            <?php foreach ($provinces as $opt): ?><option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['province_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="bo-filter-field">
                        <label class="form-label"><?= esc(lang('Backoffice.filter_commune')) ?></label>
                        <select class="form-select" name="commune_id" data-filter="commune">
                            <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                            <?php foreach ($communes as $opt): ?><option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['commune_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="bo-filter-group">
                <p class="bo-filter-group-title"><i class="bi bi-building" aria-hidden="true"></i> <?= esc(lang('Backoffice.filter_group_court')) ?></p>
                <div class="bo-filter-fields">
                    <div class="bo-filter-field">
                        <label class="form-label"><?= esc(lang('Backoffice.filter_jurisdiction_level')) ?></label>
                        <select class="form-select" name="niveau_juridiction_id">
                            <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                            <?php foreach ($niveaux as $opt): ?><option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['niveau_juridiction_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option><?php endforeach; ?>
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
    <div class="bo-table-toolbar"><label class="bo-table-search"><i class="bi bi-search"></i><input type="search" class="form-control" id="cjc-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>"></label></div>
    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="cjc-table" data-page-length="10" data-order-col="0" data-order-dir="asc" data-dom="lrtip">
            <thead><tr>
                <th><?= esc(lang('Backoffice.cjc_col_court')) ?></th>
                <th><?= esc(lang('Backoffice.cjc_col_parent')) ?></th>
                <th><?= esc(lang('Backoffice.cjc_col_status')) ?></th>
                <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($items as $row): ?>
                <tr>
                    <td><?= esc($row['court']) ?></td>
                    <td><?= esc($row['parent_court']) ?></td>
                    <td><span class="bo-status-pill <?= $row['is_active'] ? 'is-active' : 'is-inactive' ?>"><?= esc($row['status']) ?></span></td>
                    <td><div class="bo-action-group">
                        <?php if (can_access('backoffice/court-jurisdiction-configs/edit')): ?>
                        <button class="btn btn-bo-icon" type="button" data-bo-cjc-edit
                            data-id="<?= esc($row['id']) ?>"
                            data-juridiction-id="<?= esc($row['juridiction_id']) ?>"
                            data-parent-id="<?= esc($row['juridiction_parent_id']) ?>"
                            data-province-id="<?= esc($row['province_id'] ?? '') ?>"
                            data-commune-id="<?= esc($row['commune_id'] ?? '') ?>"
                            data-niveau-id="<?= esc($row['niveau_juridiction_id'] ?? '') ?>"
                            data-parent-province-id="<?= esc($row['parent_province_id'] ?? '') ?>"
                            data-parent-commune-id="<?= esc($row['parent_commune_id'] ?? '') ?>"
                            data-parent-niveau-id="<?= esc($row['parent_niveau_id'] ?? '') ?>"
                            data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.cjc_action_edit'), 'attr') ?>"><i class="bi bi-pencil-square"></i></button>
                        <?php endif; ?>
                        <?php if (can_access('backoffice/court-jurisdiction-configs/toggle-status')): ?>
                        <button class="btn btn-bo-icon <?= $row['is_active'] ? 'is-danger' : 'is-success' ?>" type="button" data-bo-toggle-cjc data-id="<?= esc($row['id']) ?>" data-name="<?= esc($row['court'], 'attr') ?>" data-activate="<?= $row['is_active'] ? '0' : '1' ?>" data-bs-toggle="tooltip" title="<?= esc($row['is_active'] ? lang('Backoffice.cjc_action_deactivate') : lang('Backoffice.cjc_action_activate'), 'attr') ?>">
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

<div class="modal fade" id="cjcFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
        <form method="post" id="cjcForm" class="needs-validation" novalidate
              data-api-communes="<?= esc(site_url('api/communes'), 'attr') ?>"
              data-api-courts="<?= esc(site_url('backoffice/api/court-jurisdictions'), 'attr') ?>"
              data-api-parent-level="<?= esc(site_url('backoffice/api/parent-jurisdiction-level'), 'attr') ?>">
            <?= csrf_field() ?>
            <div class="modal-header"><h2 class="modal-title fs-5" id="cjcFormTitle"><?= esc(lang('Backoffice.cjc_new')) ?></h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label"><?= esc(lang('Backoffice.cjc_field_province')) ?> *</label>
                        <select class="form-select" id="cjc_province" name="province_juridiction_id" data-cjc="province" required>
                            <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                            <?php foreach ($provinces as $opt): ?><option value="<?= esc($opt['id']) ?>"><?= esc($opt['label']) ?></option><?php endforeach; ?>
                        </select><div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div></div>
                    <div class="col-md-6"><label class="form-label"><?= esc(lang('Backoffice.cjc_field_commune')) ?> *</label>
                        <select class="form-select" id="cjc_commune" name="commune_juridiction_id" data-cjc="commune" required><option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option></select>
                        <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div></div>
                    <div class="col-md-6"><label class="form-label"><?= esc(lang('Backoffice.filter_jurisdiction_level')) ?> *</label>
                        <select class="form-select" id="cjc_niveau" data-cjc="niveau" required>
                            <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                            <?php foreach ($niveaux as $opt): ?><option value="<?= esc($opt['id']) ?>"><?= esc($opt['label']) ?></option><?php endforeach; ?>
                        </select><div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div></div>
                    <div class="col-md-6"><label class="form-label"><?= esc(lang('Backoffice.cjc_field_court')) ?> *</label>
                        <select class="form-select" id="cjc_juridiction" name="juridiction_id" data-cjc="court" required><option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option></select>
                        <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div></div>
                    <div class="col-12"><hr class="bo-form-divider"><h3 class="bo-form-section-title"><?= esc(lang('Backoffice.cjc_section_parent')) ?></h3></div>
                    <div class="col-md-6"><label class="form-label"><?= esc(lang('Backoffice.cjc_field_parent_province')) ?> *</label>
                        <select class="form-select" id="cjc_parent_province" name="province_juridiction_parent_id" data-cjc="parent-province" required>
                            <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                            <?php foreach ($provinces as $opt): ?><option value="<?= esc($opt['id']) ?>"><?= esc($opt['label']) ?></option><?php endforeach; ?>
                        </select><div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div></div>
                    <div class="col-md-6"><label class="form-label"><?= esc(lang('Backoffice.cjc_field_parent_commune')) ?> *</label>
                        <select class="form-select" id="cjc_parent_commune" name="commune_juridiction_parent_id" data-cjc="parent-commune" required><option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option></select>
                        <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div></div>
                    <div class="col-md-6"><label class="form-label"><?= esc(lang('Backoffice.cjc_field_parent_level')) ?></label>
                        <input class="form-control" id="cjc_parent_niveau_label" type="text" readonly value="">
                        <input type="hidden" id="cjc_parent_niveau" data-cjc="parent-niveau"></div>
                    <div class="col-md-6"><label class="form-label"><?= esc(lang('Backoffice.cjc_field_parent_court')) ?> *</label>
                        <select class="form-select" id="cjc_parent_juridiction" name="juridiction_parent_id" data-cjc="parent-court" required><option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option></select>
                        <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('Backoffice.btn_cancel')) ?></button>
                <button type="submit" class="btn btn-bo-primary" id="cjcFormSubmit"><?= esc(lang('Backoffice.cjc_save')) ?></button>
            </div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="cjcStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form method="post" id="cjcStatusForm"><?= csrf_field() ?>
            <div class="modal-header"><h2 class="modal-title fs-5" id="cjcStatusModalTitle"></h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><p class="mb-0" id="cjcStatusModalMessage"></p></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('Backoffice.btn_cancel')) ?></button>
                <button type="submit" class="btn" id="cjcStatusModalConfirm"></button>
            </div>
        </form>
    </div></div>
</div>
<script>
window.JH_CJC_I18N = {
    createTitle: <?= json_encode(lang('Backoffice.cjc_new')) ?>,
    editTitle: <?= json_encode(lang('Backoffice.cjc_edit_title')) ?>,
    saveCreate: <?= json_encode(lang('Backoffice.cjc_create')) ?>,
    saveEdit: <?= json_encode(lang('Backoffice.cjc_save')) ?>,
    activateTitle: <?= json_encode(lang('Backoffice.cjc_activate_title')) ?>,
    activateMessage: <?= json_encode(lang('Backoffice.cjc_activate_message')) ?>,
    activateBtn: <?= json_encode(lang('Backoffice.cjc_activate_btn')) ?>,
    deactivateTitle: <?= json_encode(lang('Backoffice.cjc_deactivate_title')) ?>,
    deactivateMessage: <?= json_encode(lang('Backoffice.cjc_deactivate_message')) ?>,
    deactivateBtn: <?= json_encode(lang('Backoffice.cjc_deactivate_btn')) ?>,
    storeUrl: <?= json_encode(site_url('backoffice/court-jurisdiction-configs')) ?>,
    updateUrl: <?= json_encode(site_url('backoffice/court-jurisdiction-configs/__ID__')) ?>,
    toggleUrl: <?= json_encode(site_url('backoffice/court-jurisdiction-configs/__ID__/toggle-status')) ?>,
    niveaux: <?= json_encode($niveaux) ?>
};
</script>
<?= $this->endSection() ?>
