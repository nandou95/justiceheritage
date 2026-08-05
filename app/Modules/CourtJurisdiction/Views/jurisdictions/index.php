<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_court_jurisdiction')) ?></p>
        <h1><?= esc(lang('Backoffice.cj_title')) ?></h1>
        <p><?= esc(lang('Backoffice.cj_lead')) ?></p>
    </div>
    <a class="btn btn-bo-primary" href="<?= site_url('backoffice/court-jurisdictions/create') ?>">
        <i class="bi bi-plus-lg" aria-hidden="true"></i> <?= esc(lang('Backoffice.cj_new')) ?>
    </a>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-filters" method="get" action="<?= site_url('backoffice/court-jurisdictions') ?>" data-bo-cj-filters
          data-api-communes="<?= esc(site_url('api/communes'), 'attr') ?>">
        <div class="row g-2">
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label" for="f_province"><?= esc(lang('Backoffice.filter_province')) ?></label>
                <select class="form-select" id="f_province" name="province_id" data-filter="province">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($provinces as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['province_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label" for="f_commune"><?= esc(lang('Backoffice.filter_commune')) ?></label>
                <select class="form-select" id="f_commune" name="commune_id" data-filter="commune">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($communes as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['commune_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label" for="f_niveau"><?= esc(lang('Backoffice.filter_jurisdiction_level')) ?></label>
                <select class="form-select" id="f_niveau" name="niveau_juridiction_id">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($niveaux as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['niveau_juridiction_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label" for="f_status"><?= esc(lang('Backoffice.filter_status')) ?></label>
                <select class="form-select" id="f_status" name="status">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <option value="true" <?= ($filters['status'] ?? '') === 'true' || ($filters['status'] ?? '') === '1' ? 'selected' : '' ?>><?= esc(lang('Backoffice.status_active')) ?></option>
                    <option value="false" <?= ($filters['status'] ?? '') === 'false' || ($filters['status'] ?? '') === '0' ? 'selected' : '' ?>><?= esc(lang('Backoffice.status_inactive')) ?></option>
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
            <input type="search" class="form-control" id="cj-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>">
        </label>
    </div>

    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="cj-table" data-page-length="10" data-order-col="1" data-order-dir="asc" data-dom="lrtip">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.cj_col_code')) ?></th>
                    <th><?= esc(lang('Backoffice.cj_col_name')) ?></th>
                    <th><?= esc(lang('Backoffice.cj_col_level')) ?></th>
                    <th><?= esc(lang('Backoffice.cj_col_contact')) ?></th>
                    <th><?= esc(lang('Backoffice.cj_col_address')) ?></th>
                    <th><?= esc(lang('Backoffice.cj_col_status')) ?></th>
                    <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $row): ?>
                    <tr>
                        <td><code class="bo-route-code"><?= esc($row['code']) ?></code></td>
                        <td><?= esc($row['name']) ?></td>
                        <td><?= esc($row['level']) ?></td>
                        <td>
                            <div class="bo-contact-cell">
                                <span><?= esc($row['email']) ?></span>
                                <small><?= esc($row['phone']) ?></small>
                            </div>
                        </td>
                        <td><?= esc($row['address']) ?></td>
                        <td><span class="bo-status-pill <?= $row['is_active'] ? 'is-active' : 'is-inactive' ?>"><?= esc($row['status']) ?></span></td>
                        <td>
                            <div class="bo-action-group">
                                <a class="btn btn-bo-icon" href="<?= site_url('backoffice/court-jurisdictions/' . $row['id'] . '/edit') ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.cj_action_edit'), 'attr') ?>"><i class="bi bi-pencil-square"></i></a>
                                <button class="btn btn-bo-icon <?= $row['is_active'] ? 'is-danger' : 'is-success' ?>" type="button" data-bo-toggle-cj data-id="<?= esc($row['id']) ?>" data-name="<?= esc($row['name'], 'attr') ?>" data-activate="<?= $row['is_active'] ? '0' : '1' ?>" data-bs-toggle="tooltip" title="<?= esc($row['is_active'] ? lang('Backoffice.cj_action_deactivate') : lang('Backoffice.cj_action_activate'), 'attr') ?>">
                                    <i class="bi <?= $row['is_active'] ? 'bi-toggle-off' : 'bi-toggle-on' ?>"></i>
                                </button>
                                <a class="btn btn-bo-icon" href="<?= site_url('backoffice/court-jurisdictions/' . $row['id']) ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.cj_action_view'), 'attr') ?>"><i class="bi bi-eye"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<div class="modal fade" id="cjStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form method="post" id="cjStatusForm"><?= csrf_field() ?>
            <div class="modal-header"><h2 class="modal-title fs-5" id="cjStatusModalTitle"></h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><p class="mb-0" id="cjStatusModalMessage"></p></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('Backoffice.btn_cancel')) ?></button>
                <button type="submit" class="btn" id="cjStatusModalConfirm"></button>
            </div>
        </form>
    </div></div>
</div>
<script>
window.JH_CJ_I18N = {
    activateTitle: <?= json_encode(lang('Backoffice.cj_activate_title')) ?>,
    activateMessage: <?= json_encode(lang('Backoffice.cj_activate_message')) ?>,
    activateBtn: <?= json_encode(lang('Backoffice.cj_activate_btn')) ?>,
    deactivateTitle: <?= json_encode(lang('Backoffice.cj_deactivate_title')) ?>,
    deactivateMessage: <?= json_encode(lang('Backoffice.cj_deactivate_message')) ?>,
    deactivateBtn: <?= json_encode(lang('Backoffice.cj_deactivate_btn')) ?>,
    toggleUrl: <?= json_encode(site_url('backoffice/court-jurisdictions/__ID__/toggle-status')) ?>
};
</script>
<?= $this->endSection() ?>
