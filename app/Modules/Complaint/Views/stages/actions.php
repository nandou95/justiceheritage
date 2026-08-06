<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_complaint_stages')) ?></p>
        <h1><?= esc(lang('Backoffice.cs_actions_manage_title')) ?></h1>
        <p><?= esc($record['description_etape_plainte'] ?? '') ?></p>
    </div>
    <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/complaint-stages') ?>">
        <i class="bi bi-arrow-left"></i> <?= esc(lang('Backoffice.cs_back_list')) ?>
    </a>
</section>

<section class="bo-panel bo-crud-panel mb-3">
    <form method="post" action="<?= site_url('backoffice/complaint-stages/' . (int) $record['etape_plainte_id'] . '/actions') ?>" class="needs-validation" novalidate>
        <?= csrf_field() ?>
        <div class="row g-2 align-items-end">
            <div class="col-md-8">
                <label class="form-label" for="desc_etape_plainte_action"><?= esc(lang('Backoffice.cs_action_field_description')) ?> *</label>
                <input class="form-control" type="text" id="desc_etape_plainte_action" name="desc_etape_plainte_action" value="<?= esc(old('desc_etape_plainte_action') ?? '') ?>" required maxlength="255">
                <div class="invalid-feedback"><?= esc(lang('Backoffice.validation_required')) ?></div>
            </div>
            <div class="col-md-4">
                <button class="btn btn-bo-primary w-100" type="submit">
                    <i class="bi bi-plus-lg"></i> <?= esc(lang('Backoffice.cs_action_add')) ?>
                </button>
            </div>
        </div>
    </form>
</section>

<section class="bo-panel bo-crud-panel">
    <div class="bo-table-toolbar">
        <label class="bo-table-search"><i class="bi bi-search"></i>
            <input type="search" class="form-control" id="cs-actions-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>">
        </label>
    </div>
    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="cs-actions-table" data-page-length="10" data-dom="lrtip">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.cs_action_col_description')) ?></th>
                    <th><?= esc(lang('Backoffice.cs_col_status')) ?></th>
                    <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($actions as $row): ?>
                <tr>
                    <td><?= esc($row['description']) ?></td>
                    <td><span class="bo-status-pill <?= $row['is_active'] ? 'is-active' : 'is-inactive' ?>"><?= esc($row['status']) ?></span></td>
                    <td>
                        <div class="bo-action-group">
                            <form method="post" action="<?= site_url('backoffice/complaint-stages/' . (int) $record['etape_plainte_id'] . '/actions/' . (int) $row['id'] . '/toggle-status') ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-bo-icon <?= $row['is_active'] ? 'is-danger' : 'is-success' ?>" type="submit" data-bs-toggle="tooltip" title="<?= esc($row['is_active'] ? lang('Backoffice.cs_sa_deactivate') : lang('Backoffice.cs_sa_activate'), 'attr') ?>">
                                    <i class="bi <?= $row['is_active'] ? 'bi-toggle-off' : 'bi-toggle-on' ?>"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<script>
document.querySelector('.needs-validation')?.addEventListener('submit', function (event) {
    if (!this.checkValidity()) { event.preventDefault(); event.stopPropagation(); }
    this.classList.add('was-validated');
});
</script>
<?= $this->endSection() ?>
