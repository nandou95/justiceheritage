<?= $this->extend('layouts/backoffice') ?>

<?= $this->section('content') ?>

<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_administration')) ?></p>
        <h1><?= esc(lang('Backoffice.users_title')) ?></h1>
        <p><?= esc(lang('Backoffice.users_lead')) ?></p>
    </div>
    <a class="btn btn-bo-primary" href="<?= site_url('backoffice/users/create') ?>">
        <i class="bi bi-plus-lg" aria-hidden="true"></i>
        <?= esc(lang('Backoffice.users_new')) ?>
    </a>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-filters" method="get" action="<?= site_url('backoffice/users') ?>" data-bo-user-filters
          data-api-communes="<?= esc(site_url('api/communes'), 'attr') ?>"
          data-api-jurisdictions="<?= esc(site_url('backoffice/api/jurisdictions'), 'attr') ?>">
        <div class="row g-2">
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label" for="filter_province"><?= esc(lang('Backoffice.filter_province')) ?></label>
                <select class="form-select" id="filter_province" name="province_id" data-filter="province">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($provinces as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['province_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>>
                            <?= esc($opt['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label" for="filter_commune"><?= esc(lang('Backoffice.filter_commune')) ?></label>
                <select class="form-select" id="filter_commune" name="commune_id" data-filter="commune">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($communes as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['commune_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>>
                            <?= esc($opt['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label" for="filter_niveau"><?= esc(lang('Backoffice.filter_jurisdiction_level')) ?></label>
                <select class="form-select" id="filter_niveau" name="niveau_juridiction_id" data-filter="niveau">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($niveaux as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['niveau_juridiction_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>>
                            <?= esc($opt['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label" for="filter_juridiction"><?= esc(lang('Backoffice.filter_court_jurisdiction')) ?></label>
                <select class="form-select" id="filter_juridiction" name="juridiction_id" data-filter="juridiction">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($jurisdictions as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['juridiction_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>>
                            <?= esc($opt['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label" for="filter_status"><?= esc(lang('Backoffice.filter_account_status')) ?></label>
                <select class="form-select" id="filter_status" name="account_status">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <option value="true" <?= ($filters['account_status'] ?? '') === 'true' || ($filters['account_status'] ?? '') === '1' ? 'selected' : '' ?>>
                        <?= esc(lang('Backoffice.status_active')) ?>
                    </option>
                    <option value="false" <?= ($filters['account_status'] ?? '') === 'false' || ($filters['account_status'] ?? '') === '0' ? 'selected' : '' ?>>
                        <?= esc(lang('Backoffice.status_inactive')) ?>
                    </option>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-1 d-flex align-items-end">
                <button class="btn btn-bo-secondary w-100" type="submit"><?= esc(lang('Backoffice.filter_apply')) ?></button>
            </div>
        </div>
    </form>

    <div class="bo-table-toolbar">
        <label class="bo-table-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" class="form-control" id="users-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>" aria-label="<?= esc(lang('Backoffice.search_placeholder')) ?>">
        </label>
    </div>

    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="users-table" data-page-length="10" data-order-col="0" data-order-dir="asc" data-dom="lrtip">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.users_col_name')) ?></th>
                    <th><?= esc(lang('Backoffice.users_col_cni')) ?></th>
                    <th><?= esc(lang('Backoffice.users_col_matricule')) ?></th>
                    <th><?= esc(lang('Backoffice.users_col_contact')) ?></th>
                    <th><?= esc(lang('Backoffice.users_col_profile')) ?></th>
                    <th><?= esc(lang('Backoffice.users_col_status')) ?></th>
                    <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $row): ?>
                        <tr>
                            <td><?= esc($row['full_name']) ?></td>
                            <td><?= esc($row['numero_cni']) ?></td>
                            <td><?= esc($row['numero_matricule']) ?></td>
                            <td>
                                <div class="bo-contact-cell">
                                    <span><?= esc($row['email']) ?></span>
                                    <small><?= esc($row['telephone']) ?></small>
                                </div>
                            </td>
                            <td><?= esc($row['profile']) ?></td>
                            <td>
                                <span class="bo-status-pill <?= $row['is_active'] ? 'is-active' : 'is-inactive' ?>">
                                    <?= esc($row['status_label']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="bo-action-group">
                                    <a class="btn btn-bo-icon" href="<?= site_url('backoffice/users/' . $row['id'] . '/edit') ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.users_action_edit'), 'attr') ?>">
                                        <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                        <span class="visually-hidden"><?= esc(lang('Backoffice.users_action_edit')) ?></span>
                                    </a>
                                    <button
                                        class="btn btn-bo-icon <?= $row['is_active'] ? 'is-danger' : 'is-success' ?>"
                                        type="button"
                                        data-bo-toggle-user
                                        data-user-id="<?= esc($row['id']) ?>"
                                        data-user-name="<?= esc($row['full_name'], 'attr') ?>"
                                        data-activate="<?= $row['is_active'] ? '0' : '1' ?>"
                                        data-bs-toggle="tooltip"
                                        title="<?= esc($row['is_active'] ? lang('Backoffice.users_action_deactivate') : lang('Backoffice.users_action_activate'), 'attr') ?>"
                                    >
                                        <i class="bi <?= $row['is_active'] ? 'bi-person-x' : 'bi-person-check' ?>" aria-hidden="true"></i>
                                        <span class="visually-hidden"><?= esc($row['is_active'] ? lang('Backoffice.users_action_deactivate') : lang('Backoffice.users_action_activate')) ?></span>
                                    </button>
                                    <a class="btn btn-bo-icon" href="<?= site_url('backoffice/users/' . $row['id']) ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.users_action_view'), 'attr') ?>">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                        <span class="visually-hidden"><?= esc(lang('Backoffice.users_action_view')) ?></span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<div class="modal fade" id="userStatusModal" tabindex="-1" aria-labelledby="userStatusModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" id="userStatusForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="userStatusModalTitle"></h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc(lang('Backoffice.close_menu'), 'attr') ?>"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0" id="userStatusModalMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('Backoffice.btn_cancel')) ?></button>
                    <button type="submit" class="btn" id="userStatusModalConfirm"></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.JH_USERS_I18N = {
    activateTitle: <?= json_encode(lang('Backoffice.users_activate_title')) ?>,
    activateMessage: <?= json_encode(lang('Backoffice.users_activate_message')) ?>,
    activateBtn: <?= json_encode(lang('Backoffice.users_activate_btn')) ?>,
    deactivateTitle: <?= json_encode(lang('Backoffice.users_deactivate_title')) ?>,
    deactivateMessage: <?= json_encode(lang('Backoffice.users_deactivate_message')) ?>,
    deactivateBtn: <?= json_encode(lang('Backoffice.users_deactivate_btn')) ?>,
    toggleUrl: <?= json_encode(site_url('backoffice/users/__ID__/toggle-status')) ?>
};
</script>

<?= $this->endSection() ?>
