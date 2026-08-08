<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_hearings')) ?></p>
        <h1><?= esc(lang('Backoffice.hrg_title')) ?></h1>
        <p><?= esc(lang('Backoffice.hrg_lead')) ?></p>
    </div>
    <?php if (can_access('backoffice/hearings/create')): ?>
    <a class="btn btn-bo-primary" href="<?= site_url('backoffice/hearings/create') ?>">
        <i class="bi bi-plus-lg"></i> <?= esc(lang('Backoffice.hrg_new')) ?>
    </a>
    <?php endif; ?>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-filters" method="get" action="<?= site_url('backoffice/hearings') ?>" data-bo-hrg-filters
          data-api-communes="<?= esc(site_url('api/communes'), 'attr') ?>"
          data-api-jurisdictions="<?= esc(site_url('backoffice/api/court-jurisdictions'), 'attr') ?>">
        <?= view('partials/bo_filters_head', [
            'filters' => $filters,
            'filterKeys' => ['niveau_juridiction_id', 'province_id', 'commune_id', 'juridiction_id', 'date_audience', 'statut_audience_id'],
            'resetUrl' => site_url('backoffice/hearings'),
            'lead' => lang('Backoffice.filters_lead'),
        ]) ?>
        <div class="bo-filters-body">
            <div class="bo-filter-group">
                <p class="bo-filter-group-title"><i class="bi bi-geo-alt" aria-hidden="true"></i> <?= esc(lang('Backoffice.filter_group_location')) ?></p>
                <div class="bo-filter-fields">
                    <div class="bo-filter-field">
                        <label class="form-label"><?= esc(lang('Backoffice.filter_province')) ?></label>
                        <select class="form-select" name="province_id" data-filter="province">
                            <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                            <?php foreach ($provinces as $opt): ?>
                                <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['province_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="bo-filter-field">
                        <label class="form-label"><?= esc(lang('Backoffice.filter_commune')) ?></label>
                        <select class="form-select" name="commune_id" data-filter="commune">
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
                        <label class="form-label"><?= esc(lang('Backoffice.filter_jurisdiction_level')) ?></label>
                        <select class="form-select" name="niveau_juridiction_id" data-filter="niveau">
                            <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                            <?php foreach ($levels as $opt): ?>
                                <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['niveau_juridiction_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="bo-filter-field">
                        <label class="form-label"><?= esc(lang('Backoffice.filter_court_jurisdiction')) ?></label>
                        <select class="form-select" name="juridiction_id" data-filter="juridiction">
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
                        <label class="form-label"><?= esc(lang('Backoffice.hrg_filter_date')) ?></label>
                        <input class="form-control" type="date" name="date_audience" value="<?= esc($filters['date_audience'] ?? '') ?>">
                    </div>
                    <div class="bo-filter-field">
                        <label class="form-label"><?= esc(lang('Backoffice.hrg_filter_status')) ?></label>
                        <select class="form-select" name="statut_audience_id">
                            <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                            <?php foreach ($statuses as $opt): ?>
                                <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['statut_audience_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="bo-table-toolbar">
        <label class="bo-table-search"><i class="bi bi-search"></i>
            <input type="search" class="form-control" id="hrg-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>">
        </label>
    </div>

    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="hrg-table" data-page-length="10" data-order-col="1" data-order-dir="desc" data-dom="lrtip">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.hrg_col_court')) ?></th>
                    <th><?= esc(lang('Backoffice.hrg_col_hearing')) ?></th>
                    <th><?= esc(lang('Backoffice.hrg_col_complaints')) ?></th>
                    <th><?= esc(lang('Backoffice.hrg_col_venue')) ?></th>
                    <th><?= esc(lang('Backoffice.hrg_col_period')) ?></th>
                    <th><?= esc(lang('Backoffice.hrg_col_status')) ?></th>
                    <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $row): ?>
                <tr>
                    <td><?= esc($row['court']) ?></td>
                    <td><?= esc($row['hearing_at']) ?></td>
                    <td>
                        <button type="button" class="btn btn-link p-0 fw-semibold" data-bo-hrg-complaints
                            data-bs-toggle="modal" data-bs-target="#hrgComplaintsModal"
                            data-title="<?= esc($row['hearing_at'], 'attr') ?>"
                            data-rows="<?= esc(json_encode($row['complaints'], JSON_UNESCAPED_UNICODE), 'attr') ?>">
                            <?= esc((string) $row['complaints_count']) ?>
                        </button>
                    </td>
                    <td><?= esc($row['venue']) ?></td>
                    <td><?= esc($row['period']) ?></td>
                    <td><?= esc($row['status']) ?></td>
                    <td>
                        <div class="bo-action-group">
                            <?php if (can_access('backoffice/hearings/show')): ?>
                            <a class="btn btn-bo-icon" href="<?= site_url('backoffice/hearings/' . $row['id']) ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.hrg_action_view'), 'attr') ?>"><i class="bi bi-card-heading"></i></a>
                            <?php endif; ?>
                            <?php if (can_access('backoffice/hearings/assign')): ?>
                            <a class="btn btn-bo-icon" href="<?= site_url('backoffice/hearings/' . $row['id'] . '/assignments') ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.hrg_action_assign'), 'attr') ?>"><i class="bi bi-people"></i></a>
                            <?php endif; ?>
                            <?php if (can_access('backoffice/hearings/process')): ?>
                            <a class="btn btn-bo-icon" href="<?= site_url('backoffice/hearings/' . $row['id'] . '/process') ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.hrg_action_process'), 'attr') ?>"><i class="bi bi-clipboard-check"></i></a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<div class="modal fade" id="hrgComplaintsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title fs-5"><?= esc(lang('Backoffice.hrg_complaints_modal_title')) ?> — <span id="hrgComplaintsModalTitle"></span></h2>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th><?= esc(lang('Backoffice.hrg_col_case')) ?></th>
                            <th><?= esc(lang('Backoffice.hrg_col_subject')) ?></th>
                            <th><?= esc(lang('Backoffice.hrg_col_court')) ?></th>
                            <th><?= esc(lang('Backoffice.hrg_col_stage')) ?></th>
                            <th><?= esc(lang('Backoffice.hrg_col_complaint_status')) ?></th>
                            <th><?= esc(lang('Backoffice.hrg_col_filing')) ?></th>
                        </tr>
                    </thead>
                    <tbody id="hrgComplaintsModalBody"></tbody>
                </table>
            </div>
        </div>
    </div></div>
</div>
<?= $this->endSection() ?>
