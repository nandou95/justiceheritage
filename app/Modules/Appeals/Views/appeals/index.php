<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_appeals')) ?></p>
        <h1><?= esc(lang('Backoffice.apl_title')) ?></h1>
        <p><?= esc(lang('Backoffice.apl_lead')) ?></p>
    </div>
    <?php if (can_access('backoffice/appeals/create')): ?>
    <a class="btn btn-bo-primary" href="<?= site_url('backoffice/appeals/create') ?>">
        <i class="bi bi-plus-lg"></i> <?= esc(lang('Backoffice.apl_new')) ?>
    </a>
    <?php endif; ?>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-filters" method="get" action="<?= site_url('backoffice/appeals') ?>" data-bo-apl-filters
          data-api-communes="<?= esc(site_url('api/communes'), 'attr') ?>"
          data-api-jurisdictions="<?= esc(site_url('backoffice/api/court-jurisdictions'), 'attr') ?>">
        <div class="row g-2">
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label"><?= esc(lang('Backoffice.filter_jurisdiction_level')) ?></label>
                <select class="form-select" name="niveau_juridiction_id" data-filter="niveau">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($levels as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['niveau_juridiction_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label"><?= esc(lang('Backoffice.filter_province')) ?></label>
                <select class="form-select" name="province_id" data-filter="province">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($provinces as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['province_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label"><?= esc(lang('Backoffice.filter_commune')) ?></label>
                <select class="form-select" name="commune_id" data-filter="commune">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($communes as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['commune_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label"><?= esc(lang('Backoffice.filter_court_jurisdiction')) ?></label>
                <select class="form-select" name="juridiction_id" data-filter="juridiction">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($jurisdictions as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['juridiction_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label"><?= esc(lang('Backoffice.apl_filter_date')) ?></label>
                <input class="form-control" type="date" name="date_recours" value="<?= esc($filters['date_recours'] ?? '') ?>">
            </div>
            <div class="col-12 col-md-6 col-xl-1">
                <label class="form-label"><?= esc(lang('Backoffice.apl_filter_deadline')) ?></label>
                <select class="form-select" name="dans_les_delais">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <option value="true" <?= ($filters['dans_les_delais'] ?? '') === 'true' ? 'selected' : '' ?>><?= esc(lang('Backoffice.yes')) ?></option>
                    <option value="false" <?= ($filters['dans_les_delais'] ?? '') === 'false' ? 'selected' : '' ?>><?= esc(lang('Backoffice.no')) ?></option>
                </select>
            </div>
        </div>
    </form>

    <div class="bo-table-toolbar">
        <label class="bo-table-search"><i class="bi bi-search"></i>
            <input type="search" class="form-control" id="apl-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>">
        </label>
    </div>

    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="apl-table" data-page-length="10" data-order-col="5" data-order-dir="desc" data-dom="lrtip">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.apl_col_number')) ?></th>
                    <th><?= esc(lang('Backoffice.apl_col_subject')) ?></th>
                    <th><?= esc(lang('Backoffice.apl_col_verdict')) ?></th>
                    <th><?= esc(lang('Backoffice.apl_col_previous')) ?></th>
                    <th><?= esc(lang('Backoffice.apl_col_court')) ?></th>
                    <th><?= esc(lang('Backoffice.apl_col_date')) ?></th>
                    <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $row): ?>
                <tr>
                    <td><code class="bo-route-code"><?= esc($row['appeal_number']) ?></code></td>
                    <td><?= esc($row['subject']) ?></td>
                    <td><?= esc($row['challenged_verdict']) ?></td>
                    <td><?= esc($row['previous_complaint']) ?></td>
                    <td><?= esc($row['court']) ?></td>
                    <td><?= esc($row['appeal_date']) ?></td>
                    <td>
                        <div class="bo-action-group">
                            <?php if (can_access('backoffice/appeals/show')): ?>
                            <a class="btn btn-bo-icon" href="<?= site_url('backoffice/appeals/' . $row['id']) ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.apl_action_view'), 'attr') ?>"><i class="bi bi-card-heading"></i></a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?= $this->endSection() ?>
