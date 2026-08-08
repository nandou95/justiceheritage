<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_complaints')) ?></p>
        <h1><?= esc(lang('Backoffice.cs_title')) ?></h1>
        <p><?= esc(lang('Backoffice.cs_lead')) ?></p>
    </div>
    <?php if (can_access('backoffice/complaint-stages/create')): ?>
    <a class="btn btn-bo-primary" href="<?= site_url('backoffice/complaint-stages/create') ?>">
        <i class="bi bi-plus-lg"></i> <?= esc(lang('Backoffice.cs_new')) ?>
    </a>
    <?php endif; ?>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-filters" method="get" action="<?= site_url('backoffice/complaint-stages') ?>">
        <?= view('partials/bo_filters_head', [
            'filters' => array_merge($filters ?? [], ['status' => $filters['status'] ?? $status ?? '']),
            'filterKeys' => ['niveau_juridiction_id', 'status'],
            'resetUrl' => site_url('backoffice/complaint-stages'),
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
            <input type="search" class="form-control" id="cs-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>">
        </label>
    </div>

    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="cs-table" data-page-length="10" data-order='[[3,"asc"],[0,"asc"]]' data-dom="lrtip">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.cs_col_description')) ?></th>
                    <th><?= esc(lang('Backoffice.cs_col_profiles')) ?></th>
                    <th><?= esc(lang('Backoffice.cs_col_actions_count')) ?></th>
                    <th><?= esc(lang('Backoffice.cs_col_level')) ?></th>
                    <th><?= esc(lang('Backoffice.cs_col_summons')) ?></th>
                    <th><?= esc(lang('Backoffice.cs_col_hearing')) ?></th>
                    <th><?= esc(lang('Backoffice.cs_col_status')) ?></th>
                    <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $row): ?>
                <tr>
                    <td><?= esc($row['description']) ?></td>
                    <td>
                        <button type="button" class="btn btn-link p-0 bo-count-link" data-bo-cs-profiles data-id="<?= (int) $row['id'] ?>" data-name="<?= esc($row['description'], 'attr') ?>">
                            <?= (int) $row['profiles_count'] ?>
                        </button>
                    </td>
                    <td>
                        <button type="button" class="btn btn-link p-0 bo-count-link" data-bo-cs-actions data-id="<?= (int) $row['id'] ?>" data-name="<?= esc($row['description'], 'attr') ?>">
                            <?= (int) $row['actions_count'] ?>
                        </button>
                    </td>
                    <td data-order="<?= (int) $row['niveau_id'] ?>"><?= esc($row['level']) ?></td>
                    <td>
                        <span class="bo-status-pill <?= ! empty($row['is_convocation']) ? 'is-active' : 'is-inactive' ?>">
                            <?= esc(! empty($row['is_convocation']) ? lang('Backoffice.yes') : lang('Backoffice.no')) ?>
                        </span>
                    </td>
                    <td>
                        <span class="bo-status-pill <?= ! empty($row['is_audience']) ? 'is-active' : 'is-inactive' ?>">
                            <?= esc(! empty($row['is_audience']) ? lang('Backoffice.yes') : lang('Backoffice.no')) ?>
                        </span>
                    </td>
                    <td><span class="bo-status-pill <?= $row['is_active'] ? 'is-active' : 'is-inactive' ?>"><?= esc($row['status']) ?></span></td>
                    <td>
                        <div class="bo-action-group">
                            <?php if (can_access('backoffice/complaint-stages/edit')): ?>
                            <a class="btn btn-bo-icon" href="<?= site_url('backoffice/complaint-stages/' . $row['id'] . '/edit') ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.cs_action_edit'), 'attr') ?>"><i class="bi bi-pencil-square"></i></a>
                            <?php endif; ?>
                            <?php if (can_access('backoffice/complaint-stages/assign') || can_access('backoffice/complaint-stages/edit')): ?>
                            <a class="btn btn-bo-icon" href="<?= site_url('backoffice/complaint-stages/' . $row['id'] . '/actions') ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.cs_actions_manage'), 'attr') ?>">
                                <i class="bi bi-list-check"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (can_access('backoffice/complaint-stages/toggle-status')): ?>
                            <button class="btn btn-bo-icon <?= $row['is_active'] ? 'is-danger' : 'is-success' ?>" type="button" data-bo-toggle-cs data-id="<?= (int) $row['id'] ?>" data-name="<?= esc($row['description'], 'attr') ?>" data-activate="<?= $row['is_active'] ? '0' : '1' ?>" data-bs-toggle="tooltip" title="<?= esc($row['is_active'] ? lang('Backoffice.cs_action_deactivate') : lang('Backoffice.cs_action_activate'), 'attr') ?>">
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

<div class="modal fade" id="csStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form method="post" id="csStatusForm"><?= csrf_field() ?>
            <div class="modal-header"><h2 class="modal-title fs-5" id="csStatusModalTitle"></h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><p class="mb-0" id="csStatusModalMessage"></p></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('Backoffice.btn_cancel')) ?></button>
                <button type="submit" class="btn" id="csStatusModalConfirm"></button>
            </div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="csProfilesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title fs-5" id="csProfilesModalTitle"><?= esc(lang('Backoffice.cs_profiles_title')) ?></h2>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table class="table table-sm bo-table" id="csProfilesTable">
                    <thead>
                        <tr>
                            <th><?= esc(lang('Backoffice.cs_profile_code')) ?></th>
                            <th><?= esc(lang('Backoffice.cs_profile_name')) ?></th>
                            <th><?= esc(lang('Backoffice.cs_profile_description')) ?></th>
                            <th><?= esc(lang('Backoffice.cs_col_status')) ?></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <p class="text-muted mb-0 d-none" id="csProfilesEmpty"><?= esc(lang('Backoffice.cs_profiles_empty')) ?></p>
        </div>
    </div></div>
</div>

<div class="modal fade" id="csActionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title fs-5" id="csActionsModalTitle"><?= esc(lang('Backoffice.cs_actions_title')) ?></h2>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="bo-table-toolbar mb-2">
                <label class="bo-table-search"><i class="bi bi-search"></i>
                    <input type="search" class="form-control" id="cs-actions-modal-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>">
                </label>
            </div>
            <div class="table-responsive bo-table-wrap">
                <table class="table table-sm table-hover bo-table jh-datatable w-100" id="csActionsModalTable" data-page-length="5" data-dom="lrtip">
                    <thead>
                        <tr>
                            <th><?= esc(lang('Backoffice.cs_action_col_description')) ?></th>
                            <th><?= esc(lang('Backoffice.cs_col_status')) ?></th>
                            <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <p class="text-muted mb-0 d-none" id="csActionsEmpty"><?= esc(lang('Backoffice.cs_actions_empty')) ?></p>
        </div>
    </div></div>
</div>

<script>
window.JH_CS_I18N = {
    activateTitle: <?= json_encode(lang('Backoffice.cs_activate_title')) ?>,
    activateMessage: <?= json_encode(lang('Backoffice.cs_activate_message')) ?>,
    activateBtn: <?= json_encode(lang('Backoffice.cs_activate_btn')) ?>,
    deactivateTitle: <?= json_encode(lang('Backoffice.cs_deactivate_title')) ?>,
    deactivateMessage: <?= json_encode(lang('Backoffice.cs_deactivate_message')) ?>,
    deactivateBtn: <?= json_encode(lang('Backoffice.cs_deactivate_btn')) ?>,
    toggleUrl: <?= json_encode(site_url('backoffice/complaint-stages/__ID__/toggle-status')) ?>,
    profilesUrl: <?= json_encode(site_url('backoffice/complaint-stages/__ID__/profiles')) ?>,
    profilesTitle: <?= json_encode(lang('Backoffice.cs_profiles_title')) ?>,
    actionsUrl: <?= json_encode(site_url('backoffice/complaint-stages/__ID__/actions-json')) ?>,
    actionsTitle: <?= json_encode(lang('Backoffice.cs_actions_title')) ?>,
    actionToggleUrl: <?= json_encode(site_url('backoffice/complaint-stages/__ETAPE__/actions/__ID__/toggle-status')) ?>,
    actionActivate: <?= json_encode(lang('Backoffice.cs_sa_activate')) ?>,
    actionDeactivate: <?= json_encode(lang('Backoffice.cs_sa_deactivate')) ?>,
    csrfName: <?= json_encode(csrf_token()) ?>,
    csrfHash: <?= json_encode(csrf_hash()) ?>
};
</script>
<?= $this->endSection() ?>
