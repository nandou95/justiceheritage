<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_hearings')) ?></p>
        <h1><?= esc(lang('Backoffice.hrg_assign_title')) ?></h1>
        <p><?= esc(trim(($hearing['desc_niveau_juridiction'] ?? '') . ' / ' . ($hearing['nom_juridiction'] ?? '') . ' — ' . ($hearing['date_audience'] ?? ''))) ?></p>
        <?php if (! $canProcess): ?>
            <p class="text-warning mb-0"><i class="bi bi-exclamation-triangle"></i> <?= esc(lang('Backoffice.hrg_err_staff_required')) ?></p>
        <?php endif; ?>
    </div>
    <div class="bo-crud-head-actions">
        <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/hearings') ?>"><i class="bi bi-arrow-left"></i> <?= esc(lang('Backoffice.hrg_back_list')) ?></a>
        <?php if (can_access('backoffice/hearings/assign')): ?>
        <button class="btn btn-bo-primary" type="button" data-bs-toggle="modal" data-bs-target="#hrgAssignModal" data-bo-hrg-assign-create>
            <i class="bi bi-plus-lg"></i> <?= esc(lang('Backoffice.hrg_assign_new')) ?>
        </button>
        <?php endif; ?>
    </div>
</section>

<section class="bo-panel bo-crud-panel">
    <div class="bo-table-toolbar">
        <label class="bo-table-search"><i class="bi bi-search"></i>
            <input type="search" class="form-control" id="hrg-assign-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>">
        </label>
    </div>
    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="hrg-assign-table" data-page-length="10" data-dom="lrtip">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.hrg_assign_col_name')) ?></th>
                    <th><?= esc(lang('Backoffice.hrg_assign_col_profile')) ?></th>
                    <th><?= esc(lang('Backoffice.hrg_assign_col_status')) ?></th>
                    <th><?= esc(lang('Backoffice.hrg_assign_col_by')) ?></th>
                    <th><?= esc(lang('Backoffice.hrg_assign_col_date')) ?></th>
                    <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $row): ?>
                <tr>
                    <td><?= esc($row['full_name']) ?></td>
                    <td><?= esc($row['profile']) ?></td>
                    <td><span class="bo-status-pill <?= $row['is_active'] ? 'is-active' : 'is-inactive' ?>"><?= esc($row['status']) ?></span></td>
                    <td><?= esc($row['assigned_by']) ?></td>
                    <td><?= esc($row['assigned_at']) ?></td>
                    <td>
                        <div class="bo-action-group">
                            <?php if (can_access('backoffice/hearings/assign')): ?>
                            <button class="btn btn-bo-icon" type="button" data-bo-hrg-assign-edit
                                data-id="<?= esc($row['id']) ?>"
                                data-user="<?= esc($row['user_id']) ?>"
                                data-profil="<?= esc($row['profil_id']) ?>"
                                data-active="<?= $row['is_active'] ? '1' : '0' ?>"
                                data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.hrg_assign_action_edit'), 'attr') ?>">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <?php endif; ?>
                            <?php if (can_access('backoffice/hearings/assignments/toggle-status')): ?>
                            <button class="btn btn-bo-icon <?= $row['is_active'] ? 'is-danger' : 'is-success' ?>" type="button"
                                data-bo-toggle-hrg-assign
                                data-id="<?= esc($row['id']) ?>"
                                data-name="<?= esc($row['full_name'], 'attr') ?>"
                                data-activate="<?= $row['is_active'] ? '0' : '1' ?>"
                                data-bs-toggle="tooltip"
                                title="<?= esc($row['is_active'] ? lang('Backoffice.hrg_assign_action_deactivate') : lang('Backoffice.hrg_assign_action_activate'), 'attr') ?>">
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

<div class="modal fade" id="hrgAssignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form method="post" id="hrgAssignForm" class="needs-validation" novalidate><?= csrf_field() ?>
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="hrgAssignFormTitle"><?= esc(lang('Backoffice.hrg_assign_new')) ?></h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="utilisateur_affecte_id"><?= esc(lang('Backoffice.hrg_assign_field_user')) ?> *</label>
                    <select class="form-select" id="utilisateur_affecte_id" name="utilisateur_affecte_id" required>
                        <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                        <?php foreach ($users as $opt): ?>
                            <option value="<?= esc($opt['id']) ?>"><?= esc($opt['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="profil_id"><?= esc(lang('Backoffice.hrg_assign_field_profile')) ?> *</label>
                    <select class="form-select" id="profil_id" name="profil_id" required>
                        <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                        <?php foreach ($profiles as $opt): ?>
                            <option value="<?= esc($opt['id']) ?>"><?= esc($opt['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-check form-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked>
                    <label class="form-check-label" for="is_active"><?= esc(lang('Backoffice.hrg_assign_field_active')) ?></label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('Backoffice.btn_cancel')) ?></button>
                <button type="submit" class="btn btn-bo-primary" id="hrgAssignFormSubmit"><?= esc(lang('Backoffice.hrg_assign_save')) ?></button>
            </div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="hrgAssignStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form method="post" id="hrgAssignStatusForm"><?= csrf_field() ?>
            <div class="modal-header"><h2 class="modal-title fs-5" id="hrgAssignStatusModalTitle"></h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><p class="mb-0" id="hrgAssignStatusModalMessage"></p></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('Backoffice.btn_cancel')) ?></button>
                <button type="submit" class="btn" id="hrgAssignStatusModalConfirm"></button>
            </div>
        </form>
    </div></div>
</div>

<script>
window.JH_HRG_ASSIGN_I18N = {
    createTitle: <?= json_encode(lang('Backoffice.hrg_assign_new')) ?>,
    editTitle: <?= json_encode(lang('Backoffice.hrg_assign_edit_title')) ?>,
    saveCreate: <?= json_encode(lang('Backoffice.hrg_assign_create')) ?>,
    saveEdit: <?= json_encode(lang('Backoffice.hrg_assign_save')) ?>,
    activateTitle: <?= json_encode(lang('Backoffice.hrg_assign_activate_title')) ?>,
    activateMessage: <?= json_encode(lang('Backoffice.hrg_assign_activate_message')) ?>,
    activateBtn: <?= json_encode(lang('Backoffice.hrg_assign_activate_btn')) ?>,
    deactivateTitle: <?= json_encode(lang('Backoffice.hrg_assign_deactivate_title')) ?>,
    deactivateMessage: <?= json_encode(lang('Backoffice.hrg_assign_deactivate_message')) ?>,
    deactivateBtn: <?= json_encode(lang('Backoffice.hrg_assign_deactivate_btn')) ?>,
    storeUrl: <?= json_encode(site_url('backoffice/hearings/' . (int) $hearing['audience_id'] . '/assignments')) ?>,
    updateUrl: <?= json_encode(site_url('backoffice/hearings/' . (int) $hearing['audience_id'] . '/assignments/__ID__')) ?>,
    toggleUrl: <?= json_encode(site_url('backoffice/hearings/' . (int) $hearing['audience_id'] . '/assignments/__ID__/toggle-status')) ?>
};
</script>
<?= $this->endSection() ?>
