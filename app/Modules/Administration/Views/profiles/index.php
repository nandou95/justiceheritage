<?= $this->extend('layouts/backoffice') ?>

<?= $this->section('content') ?>

<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_administration')) ?></p>
        <h1><?= esc(lang('Backoffice.profiles_title')) ?></h1>
        <p><?= esc(lang('Backoffice.profiles_lead')) ?></p>
    </div>
    <a class="btn btn-bo-primary" href="<?= site_url('backoffice/profiles/create') ?>">
        <i class="bi bi-plus-lg" aria-hidden="true"></i>
        <?= esc(lang('Backoffice.profiles_new')) ?>
    </a>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-filters" method="get" action="<?= site_url('backoffice/profiles') ?>">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4 col-xl-3">
                <label class="form-label" for="filter_profile_status"><?= esc(lang('Backoffice.filter_status')) ?></label>
                <select class="form-select" id="filter_profile_status" name="status">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <option value="true" <?= ($status ?? '') === 'true' || ($status ?? '') === '1' ? 'selected' : '' ?>><?= esc(lang('Backoffice.status_active')) ?></option>
                    <option value="false" <?= ($status ?? '') === 'false' || ($status ?? '') === '0' ? 'selected' : '' ?>><?= esc(lang('Backoffice.status_inactive')) ?></option>
                </select>
            </div>
            <div class="col-12 col-md-3 col-xl-2">
                <button class="btn btn-bo-secondary w-100" type="submit"><?= esc(lang('Backoffice.filter_apply')) ?></button>
            </div>
        </div>
    </form>

    <div class="bo-table-toolbar">
        <label class="bo-table-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" class="form-control" id="profiles-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>" aria-label="<?= esc(lang('Backoffice.search_placeholder')) ?>">
        </label>
    </div>

    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="profiles-table" data-page-length="10" data-order-col="1" data-order-dir="asc" data-dom="lrtip">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.profiles_col_code')) ?></th>
                    <th><?= esc(lang('Backoffice.profiles_col_name')) ?></th>
                    <th><?= esc(lang('Backoffice.profiles_col_description')) ?></th>
                    <th><?= esc(lang('Backoffice.profiles_col_permissions')) ?></th>
                    <th><?= esc(lang('Backoffice.profiles_col_status')) ?></th>
                    <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($profiles as $row): ?>
                    <tr>
                        <td><code class="bo-route-code"><?= esc($row['code']) ?></code></td>
                        <td><?= esc($row['name']) ?></td>
                        <td><?= esc($row['description'] !== '' ? $row['description'] : '—') ?></td>
                        <td><?= esc((string) $row['permissions_count']) ?></td>
                        <td>
                            <span class="bo-status-pill <?= $row['is_active'] ? 'is-active' : 'is-inactive' ?>">
                                <?= esc($row['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="bo-action-group">
                                <a class="btn btn-bo-icon" href="<?= site_url('backoffice/profiles/' . $row['id'] . '/edit') ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.profiles_action_edit'), 'attr') ?>">
                                    <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                    <span class="visually-hidden"><?= esc(lang('Backoffice.profiles_action_edit')) ?></span>
                                </a>
                                <button
                                    class="btn btn-bo-icon <?= $row['is_active'] ? 'is-danger' : 'is-success' ?>"
                                    type="button"
                                    data-bo-toggle-profile
                                    data-id="<?= esc($row['id']) ?>"
                                    data-name="<?= esc($row['name'], 'attr') ?>"
                                    data-activate="<?= $row['is_active'] ? '0' : '1' ?>"
                                    data-bs-toggle="tooltip"
                                    title="<?= esc($row['is_active'] ? lang('Backoffice.profiles_action_deactivate') : lang('Backoffice.profiles_action_activate'), 'attr') ?>"
                                >
                                    <i class="bi <?= $row['is_active'] ? 'bi-toggle-off' : 'bi-toggle-on' ?>" aria-hidden="true"></i>
                                    <span class="visually-hidden"><?= esc($row['is_active'] ? lang('Backoffice.profiles_action_deactivate') : lang('Backoffice.profiles_action_activate')) ?></span>
                                </button>
                                <a class="btn btn-bo-icon" href="<?= site_url('backoffice/profiles/' . $row['id']) ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.profiles_action_view'), 'attr') ?>">
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                    <span class="visually-hidden"><?= esc(lang('Backoffice.profiles_action_view')) ?></span>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<div class="modal fade" id="profileStatusModal" tabindex="-1" aria-labelledby="profileStatusModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" id="profileStatusForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="profileStatusModalTitle"></h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc(lang('Backoffice.close_menu'), 'attr') ?>"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0" id="profileStatusModalMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('Backoffice.btn_cancel')) ?></button>
                    <button type="submit" class="btn" id="profileStatusModalConfirm"></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.JH_PROFILES_I18N = {
    activateTitle: <?= json_encode(lang('Backoffice.profiles_activate_title')) ?>,
    activateMessage: <?= json_encode(lang('Backoffice.profiles_activate_message')) ?>,
    activateBtn: <?= json_encode(lang('Backoffice.profiles_activate_btn')) ?>,
    deactivateTitle: <?= json_encode(lang('Backoffice.profiles_deactivate_title')) ?>,
    deactivateMessage: <?= json_encode(lang('Backoffice.profiles_deactivate_message')) ?>,
    deactivateBtn: <?= json_encode(lang('Backoffice.profiles_deactivate_btn')) ?>,
    toggleUrl: <?= json_encode(site_url('backoffice/profiles/__ID__/toggle-status')) ?>
};
</script>

<?= $this->endSection() ?>
