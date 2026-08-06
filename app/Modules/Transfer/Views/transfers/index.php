<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_case_transfers')) ?></p>
        <h1><?= esc(lang('Backoffice.trf_title')) ?></h1>
        <p><?= esc(lang('Backoffice.trf_lead')) ?></p>
    </div>
    <?php if (can_access('backoffice/transfers/create')): ?>
    <a class="btn btn-bo-primary" href="<?= site_url('backoffice/transfers/create') ?>">
        <i class="bi bi-plus-lg"></i> <?= esc(lang('Backoffice.trf_new')) ?>
    </a>
    <?php endif; ?>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-filters" method="get" action="<?= site_url('backoffice/transfers') ?>" data-bo-trf-filters
          data-api-communes="<?= esc(site_url('api/communes'), 'attr') ?>"
          data-api-jurisdictions="<?= esc(site_url('backoffice/api/court-jurisdictions'), 'attr') ?>">
        <div class="row g-2">
            <div class="col-12"><p class="bo-filter-group-title mb-0"><?= esc(lang('Backoffice.trf_filter_source_group')) ?></p></div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label"><?= esc(lang('Backoffice.trf_filter_src_level')) ?></label>
                <select class="form-select" name="niveau_juridiction_source_id" data-filter="src-niveau">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($levels as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['niveau_juridiction_source_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label"><?= esc(lang('Backoffice.trf_filter_src_province')) ?></label>
                <select class="form-select" name="province_source_id" data-filter="src-province">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($provinces as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['province_source_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label"><?= esc(lang('Backoffice.trf_filter_src_commune')) ?></label>
                <select class="form-select" name="commune_source_id" data-filter="src-commune">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($sourceCommunes as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['commune_source_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label"><?= esc(lang('Backoffice.trf_filter_src_court')) ?></label>
                <select class="form-select" name="juridiction_source_id" data-filter="src-juridiction">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($sourceCourts as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['juridiction_source_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 mt-2"><p class="bo-filter-group-title mb-0"><?= esc(lang('Backoffice.trf_filter_dest_group')) ?></p></div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label"><?= esc(lang('Backoffice.trf_filter_dst_level')) ?></label>
                <select class="form-select" name="niveau_juridiction_dest_id" data-filter="dst-niveau">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($levels as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['niveau_juridiction_dest_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label"><?= esc(lang('Backoffice.trf_filter_dst_province')) ?></label>
                <select class="form-select" name="province_dest_id" data-filter="dst-province">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($provinces as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['province_dest_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label"><?= esc(lang('Backoffice.trf_filter_dst_commune')) ?></label>
                <select class="form-select" name="commune_dest_id" data-filter="dst-commune">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($destCommunes as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['commune_dest_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label"><?= esc(lang('Backoffice.trf_filter_dst_court')) ?></label>
                <select class="form-select" name="juridiction_dest_id" data-filter="dst-juridiction">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($destCourts as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['juridiction_dest_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label"><?= esc(lang('Backoffice.trf_filter_transfer_date')) ?></label>
                <input class="form-control" type="date" name="date_transfert" value="<?= esc((string) ($filters['date_transfert'] ?? ''), 'attr') ?>">
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label"><?= esc(lang('Backoffice.trf_filter_reception_date')) ?></label>
                <input class="form-control" type="date" name="date_reception" value="<?= esc((string) ($filters['date_reception'] ?? ''), 'attr') ?>">
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label"><?= esc(lang('Backoffice.trf_filter_status')) ?></label>
                <select class="form-select" name="statut_transfert_dossier_id">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($statuses as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['statut_transfert_dossier_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>

    <div class="bo-table-toolbar">
        <label class="bo-table-search"><i class="bi bi-search"></i>
            <input type="search" class="form-control" id="trf-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>">
        </label>
    </div>

    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="trf-table" data-page-length="10" data-order-col="4" data-order-dir="desc" data-dom="lrtip">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.trf_col_case')) ?></th>
                    <th><?= esc(lang('Backoffice.trf_col_subject')) ?></th>
                    <th><?= esc(lang('Backoffice.trf_col_source')) ?></th>
                    <th><?= esc(lang('Backoffice.trf_col_dest')) ?></th>
                    <th><?= esc(lang('Backoffice.trf_col_transfer_date')) ?></th>
                    <th><?= esc(lang('Backoffice.trf_col_reception_date')) ?></th>
                    <th><?= esc(lang('Backoffice.trf_col_status')) ?></th>
                    <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $row): ?>
                <tr>
                    <td><?= esc($row['case_number']) ?></td>
                    <td><?= esc($row['subject']) ?></td>
                    <td><?= esc($row['source_label']) ?></td>
                    <td><?= esc($row['dest_label']) ?></td>
                    <td data-order="<?= esc($row['date_transfert'], 'attr') ?>"><?= esc($row['date_transfert']) ?></td>
                    <td><?= esc($row['date_reception']) ?></td>
                    <td><span class="bo-status-pill"><?= esc($row['status_label']) ?></span></td>
                    <td>
                        <div class="bo-row-actions">
                            <?php if (can_access('backoffice/transfers/show')): ?>
                            <a class="btn btn-bo-icon" href="<?= site_url('backoffice/transfers/' . $row['id']) ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.trf_action_view'), 'attr') ?>">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ($row['can_process']): ?>
                                <?php if (can_access('backoffice/transfers/process')): ?>
                                <a class="btn btn-bo-icon" href="<?= site_url('backoffice/transfers/' . $row['id'] . '/process') ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.trf_action_process'), 'attr') ?>">
                                    <i class="bi bi-box-arrow-in-down"></i>
                                </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="btn btn-bo-icon" type="button" disabled data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.trf_action_process_done'), 'attr') ?>">
                                    <i class="bi bi-check2-circle"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (! $items): ?>
            <p class="bo-empty-hint"><?= esc(lang('Backoffice.trf_empty')) ?></p>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>
