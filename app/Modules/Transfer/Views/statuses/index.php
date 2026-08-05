<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_case_transfers')) ?></p>
        <h1><?= esc(lang('Backoffice.trf_st_title')) ?></h1>
        <p><?= esc(lang('Backoffice.trf_st_lead')) ?></p>
    </div>
    <button class="btn btn-bo-primary" type="button" data-bs-toggle="modal" data-bs-target="#trfStFormModal" data-bo-trf-st-create>
        <i class="bi bi-plus-lg"></i> <?= esc(lang('Backoffice.trf_st_new')) ?>
    </button>
</section>

<section class="bo-panel bo-crud-panel">
    <div class="bo-table-toolbar">
        <label class="bo-table-search"><i class="bi bi-search"></i>
            <input type="search" class="form-control" id="trf-st-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>">
        </label>
    </div>

    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="trf-st-table" data-page-length="10" data-order-col="0" data-order-dir="asc" data-dom="lrtip">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.trf_st_col_description')) ?></th>
                    <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $row): ?>
                <tr>
                    <td><?= esc($row['description']) ?></td>
                    <td>
                        <div class="bo-action-group">
                            <button class="btn btn-bo-icon" type="button" data-bo-trf-st-edit
                                data-id="<?= esc($row['id']) ?>"
                                data-description="<?= esc($row['description'], 'attr') ?>"
                                data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.trf_st_action_edit'), 'attr') ?>">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (! $items): ?>
            <p class="bo-empty-hint"><?= esc(lang('Backoffice.trf_st_empty')) ?></p>
        <?php endif; ?>
    </div>
</section>

<div class="modal fade" id="trfStFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form method="post" id="trfStForm" class="needs-validation" novalidate><?= csrf_field() ?>
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="trfStFormTitle"><?= esc(lang('Backoffice.trf_st_new')) ?></h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-0">
                    <label class="form-label" for="description_statut_transfert_dossier"><?= esc(lang('Backoffice.trf_st_field_description')) ?> *</label>
                    <input class="form-control" id="description_statut_transfert_dossier" name="description_statut_transfert_dossier" maxlength="50" required>
                    <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('Backoffice.btn_cancel')) ?></button>
                <button type="submit" class="btn btn-bo-primary" id="trfStFormSubmit"><?= esc(lang('Backoffice.trf_st_save')) ?></button>
            </div>
        </form>
    </div></div>
</div>

<script>
window.JH_TRF_ST_I18N = {
    createTitle: <?= json_encode(lang('Backoffice.trf_st_new')) ?>,
    editTitle: <?= json_encode(lang('Backoffice.trf_st_edit_title')) ?>,
    saveCreate: <?= json_encode(lang('Backoffice.trf_st_create')) ?>,
    saveEdit: <?= json_encode(lang('Backoffice.trf_st_save')) ?>,
    storeUrl: <?= json_encode(site_url('backoffice/transfer-statuses')) ?>,
    updateUrl: <?= json_encode(site_url('backoffice/transfer-statuses/__ID__')) ?>
};
</script>
<?= $this->endSection() ?>
