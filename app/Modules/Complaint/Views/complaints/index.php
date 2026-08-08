<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_complaints')) ?></p>
        <h1><?= esc(lang('Backoffice.cmp_title')) ?></h1>
        <p><?= esc(lang('Backoffice.cmp_lead')) ?></p>
    </div>
    <?php if (can_access('backoffice/complaints/create')): ?>
    <a class="btn btn-bo-primary" href="<?= site_url('backoffice/complaints/create') ?>">
        <i class="bi bi-plus-lg"></i> <?= esc(lang('Backoffice.cmp_new')) ?>
    </a>
    <?php endif; ?>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-filters" method="get" action="<?= site_url('backoffice/complaints') ?>" data-bo-cmp-filters
          data-api-communes="<?= esc(site_url('api/communes'), 'attr') ?>"
          data-api-jurisdictions="<?= esc(site_url('backoffice/api/court-jurisdictions'), 'attr') ?>"
          data-api-statuses="<?= esc(site_url('backoffice/api/complaint-statuses'), 'attr') ?>">
        <?= view('partials/bo_filters_head', [
            'filters' => $filters,
            'filterKeys' => ['province_id', 'commune_id', 'niveau_juridiction_id', 'juridiction_id', 'statut_plainte_id', 'date_depot'],
            'resetUrl' => site_url('backoffice/complaints'),
            'lead' => lang('Backoffice.cmp_filters_lead'),
        ]) ?>
        <div class="bo-filters-body">
            <div class="bo-filter-group">
                <p class="bo-filter-group-title"><i class="bi bi-geo-alt" aria-hidden="true"></i> <?= esc(lang('Backoffice.filter_group_location')) ?></p>
                <div class="bo-filter-fields">
                    <div class="bo-filter-field">
                        <label class="form-label" for="cmp_filter_province"><?= esc(lang('Backoffice.filter_province')) ?></label>
                        <select class="form-select" id="cmp_filter_province" name="province_id" data-filter="province">
                            <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                            <?php foreach ($provinces as $opt): ?>
                                <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['province_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="bo-filter-field">
                        <label class="form-label" for="cmp_filter_commune"><?= esc(lang('Backoffice.filter_commune')) ?></label>
                        <select class="form-select" id="cmp_filter_commune" name="commune_id" data-filter="commune">
                            <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                            <?php foreach ($communes as $opt): ?>
                                <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['commune_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bo-filter-group">
                <p class="bo-filter-group-title"><i class="bi bi-building" aria-hidden="true"></i> <?= esc(lang('Backoffice.filter_group_court')) ?></p>
                <div class="bo-filter-fields">
                    <div class="bo-filter-field">
                        <label class="form-label" for="cmp_filter_niveau"><?= esc(lang('Backoffice.filter_jurisdiction_level')) ?></label>
                        <select class="form-select" id="cmp_filter_niveau" name="niveau_juridiction_id" data-filter="niveau">
                            <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                            <?php foreach ($levels as $opt): ?>
                                <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['niveau_juridiction_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="bo-filter-field">
                        <label class="form-label" for="cmp_filter_juridiction"><?= esc(lang('Backoffice.filter_court_jurisdiction')) ?></label>
                        <select class="form-select" id="cmp_filter_juridiction" name="juridiction_id" data-filter="juridiction">
                            <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                            <?php foreach ($jurisdictions as $opt): ?>
                                <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['juridiction_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bo-filter-group">
                <p class="bo-filter-group-title"><i class="bi bi-folder2-open" aria-hidden="true"></i> <?= esc(lang('Backoffice.filter_group_case')) ?></p>
                <div class="bo-filter-fields">
                    <div class="bo-filter-field">
                        <label class="form-label" for="cmp_filter_status"><?= esc(lang('Backoffice.cmp_filter_status')) ?></label>
                        <select class="form-select" id="cmp_filter_status" name="statut_plainte_id" data-filter="status">
                            <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                            <?php foreach ($statuses as $opt): ?>
                                <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['statut_plainte_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="bo-filter-field">
                        <label class="form-label" for="cmp_filter_filing"><?= esc(lang('Backoffice.cmp_filter_filing')) ?></label>
                        <input class="form-control" type="date" id="cmp_filter_filing" name="date_depot" value="<?= esc($filters['date_depot'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="bo-table-toolbar">
        <label class="bo-table-search"><i class="bi bi-search"></i>
            <input type="search" class="form-control" id="cmp-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>">
        </label>
    </div>

    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="cmp-table" data-page-length="10" data-order-col="5" data-order-dir="desc" data-dom="lrtip">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.cmp_col_case')) ?></th>
                    <th><?= esc(lang('Backoffice.cmp_col_subject')) ?></th>
                    <th><?= esc(lang('Backoffice.cmp_col_people')) ?></th>
                    <th><?= esc(lang('Backoffice.cmp_col_parcels')) ?></th>
                    <th><?= esc(lang('Backoffice.cmp_col_court')) ?></th>
                    <th><?= esc(lang('Backoffice.cmp_col_filing')) ?></th>
                    <th><?= esc(lang('Backoffice.cmp_col_stage')) ?></th>
                    <th><?= esc(lang('Backoffice.cmp_col_status')) ?></th>
                    <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $row): ?>
                <tr>
                    <td><code class="bo-route-code"><?= esc($row['case_number']) ?></code></td>
                    <td><?= esc($row['subject']) ?></td>
                    <td><?= (int) $row['people_count'] ?></td>
                    <td><?= (int) $row['parcels_count'] ?></td>
                    <td><?= esc($row['court']) ?></td>
                    <td><?= esc($row['filing_date']) ?></td>
                    <td><?= esc($row['stage']) ?></td>
                    <td><?= esc($row['status']) ?></td>
                    <td>
                        <div class="bo-action-group">
                            <?php if (can_access('backoffice/complaints/show')): ?>
                            <a class="btn btn-bo-icon" href="<?= site_url('backoffice/complaints/' . $row['id']) ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.cmp_action_view'), 'attr') ?>"><i class="bi bi-card-heading"></i></a>
                            <?php endif; ?>
                            <?php if (can_access('backoffice/complaints/edit')): ?>
                            <a class="btn btn-bo-icon" href="<?= site_url('backoffice/complaints/' . $row['id'] . '/edit') ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.cmp_action_edit'), 'attr') ?>"><i class="bi bi-pencil-square"></i></a>
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
