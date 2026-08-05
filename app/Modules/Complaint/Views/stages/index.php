<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_complaints')) ?></p>
        <h1><?= esc(lang('Backoffice.cs_title')) ?></h1>
        <p><?= esc(lang('Backoffice.cs_lead')) ?></p>
    </div>
    <a class="btn btn-bo-primary" href="<?= site_url('backoffice/complaint-stages/create') ?>">
        <i class="bi bi-plus-lg"></i> <?= esc(lang('Backoffice.cs_new')) ?>
    </a>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-filters" method="get" action="<?= site_url('backoffice/complaint-stages') ?>">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label"><?= esc(lang('Backoffice.filter_status')) ?></label>
                <select class="form-select" name="status">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <option value="true" <?= ($status ?? '') === 'true' ? 'selected' : '' ?>><?= esc(lang('Backoffice.status_active')) ?></option>
                    <option value="false" <?= ($status ?? '') === 'false' ? 'selected' : '' ?>><?= esc(lang('Backoffice.status_inactive')) ?></option>
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-bo-secondary w-100" type="submit"><?= esc(lang('Backoffice.filter_apply')) ?></button></div>
        </div>
    </form>

    <div class="bo-table-toolbar">
        <label class="bo-table-search"><i class="bi bi-search"></i>
            <input type="search" class="form-control" id="cs-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>">
        </label>
    </div>

    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="cs-table" data-page-length="10" data-order-col="0" data-order-dir="asc" data-dom="lrtip">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.cs_col_description')) ?></th>
                    <th><?= esc(lang('Backoffice.cs_col_profiles')) ?></th>
                    <th><?= esc(lang('Backoffice.cs_col_level')) ?></th>
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
                    <td><?= esc($row['level']) ?></td>
                    <td><span class="bo-status-pill <?= $row['is_active'] ? 'is-active' : 'is-inactive' ?>"><?= esc($row['status']) ?></span></td>
                    <td>
                        <div class="bo-action-group">
                            <a class="btn btn-bo-icon" href="<?= site_url('backoffice/complaint-stages/' . $row['id'] . '/edit') ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.cs_action_edit'), 'attr') ?>"><i class="bi bi-pencil-square"></i></a>
                            <button class="btn btn-bo-icon <?= $row['is_active'] ? 'is-danger' : 'is-success' ?>" type="button" data-bo-toggle-cs data-id="<?= (int) $row['id'] ?>" data-name="<?= esc($row['description'], 'attr') ?>" data-activate="<?= $row['is_active'] ? '0' : '1' ?>" data-bs-toggle="tooltip" title="<?= esc($row['is_active'] ? lang('Backoffice.cs_action_deactivate') : lang('Backoffice.cs_action_activate'), 'attr') ?>">
                                <i class="bi <?= $row['is_active'] ? 'bi-toggle-off' : 'bi-toggle-on' ?>"></i>
                            </button>
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
    profilesTitle: <?= json_encode(lang('Backoffice.cs_profiles_title')) ?>
};
</script>
<?= $this->endSection() ?>
