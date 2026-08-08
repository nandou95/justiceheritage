<?= $this->extend('layouts/backoffice') ?>

<?= $this->section('content') ?>

<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_administration')) ?></p>
        <h1><?= esc(lang('Backoffice.users_title')) ?></h1>
        <p><?= esc(lang('Backoffice.users_lead')) ?></p>
    </div>
    <?php if (can_access('backoffice/users/create')): ?>
    <a class="btn btn-bo-primary" href="<?= site_url('backoffice/users/create') ?>">
        <i class="bi bi-plus-lg" aria-hidden="true"></i>
        <?= esc(lang('Backoffice.users_new')) ?>
    </a>
    <?php endif; ?>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-filters" method="get" action="<?= site_url('backoffice/users') ?>" data-bo-user-filters
          data-api-communes="<?= esc(site_url('api/communes'), 'attr') ?>">
        <?= view('partials/bo_filters_head', [
            'filters' => $filters,
            'filterKeys' => ['province_id', 'commune_id', 'juridiction_id', 'statut_compte_id'],
            'resetUrl' => site_url('backoffice/users'),
            'lead' => lang('Backoffice.filters_lead'),
        ]) ?>
        <div class="bo-filters-body">
            <div class="bo-filter-group">
                <p class="bo-filter-group-title"><i class="bi bi-geo-alt" aria-hidden="true"></i> <?= esc(lang('Backoffice.filter_group_location')) ?></p>
                <div class="bo-filter-fields">
                    <div class="bo-filter-field">
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
                    <div class="bo-filter-field">
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
                </div>
            </div>
            <div class="bo-filter-group">
                <p class="bo-filter-group-title"><i class="bi bi-building" aria-hidden="true"></i> <?= esc(lang('Backoffice.filter_group_court')) ?></p>
                <div class="bo-filter-fields">
                    <div class="bo-filter-field">
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
                </div>
            </div>
            <div class="bo-filter-group">
                <p class="bo-filter-group-title"><i class="bi bi-toggle2-on" aria-hidden="true"></i> <?= esc(lang('Backoffice.filter_group_account')) ?></p>
                <div class="bo-filter-fields">
                    <div class="bo-filter-field">
                        <label class="form-label" for="filter_status"><?= esc(lang('Backoffice.filter_account_status')) ?></label>
                        <select class="form-select" id="filter_status" name="statut_compte_id" data-filter="status">
                            <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                            <?php foreach ($accountStatuses as $opt): ?>
                                <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['statut_compte_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>>
                                    <?= esc($opt['label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
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
                                    <?php if (can_access('backoffice/users/edit')): ?>
                                    <a class="btn btn-bo-icon" href="<?= site_url('backoffice/users/' . $row['id'] . '/edit') ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.users_action_edit'), 'attr') ?>">
                                        <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                        <span class="visually-hidden"><?= esc(lang('Backoffice.users_action_edit')) ?></span>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (can_access('backoffice/users/toggle-status')): ?>
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
                                    <?php endif; ?>
                                    <?php if (can_access('backoffice/users/show')): ?>
                                    <a class="btn btn-bo-icon" href="<?= site_url('backoffice/users/' . $row['id']) ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.users_action_view'), 'attr') ?>">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                        <span class="visually-hidden"><?= esc(lang('Backoffice.users_action_view')) ?></span>
                                    </a>
                                    <?php endif; ?>
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
