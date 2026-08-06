<?= $this->extend('layouts/backoffice') ?>

<?= $this->section('content') ?>

<?= view('Modules\AuditLog\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_logs')) ?></p>
        <h1><?= esc(lang('Backoffice.logs_complainant_title')) ?></h1>
        <p><?= esc(lang('Backoffice.logs_complainant_lead')) ?></p>
    </div>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-filters" method="get" action="<?= site_url('backoffice/system-logs/complainants') ?>" data-bo-log-filters
          data-api-communes="<?= esc(site_url('api/communes'), 'attr') ?>">
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
                <label class="form-label" for="filter_personne"><?= esc(lang('Backoffice.logs_filter_complainant')) ?></label>
                <select class="form-select" id="filter_personne" name="personne_id">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($complainants as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['personne_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>>
                            <?= esc($opt['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label" for="filter_action"><?= esc(lang('Backoffice.logs_filter_action')) ?></label>
                <select class="form-select" id="filter_action" name="action">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($actions as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['action'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>>
                            <?= esc($opt['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label" for="filter_table"><?= esc(lang('Backoffice.logs_filter_table')) ?></label>
                <select class="form-select" id="filter_table" name="table_cible">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($tables as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['table_cible'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>>
                            <?= esc($opt['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3 col-xl-1">
                <label class="form-label" for="filter_date_from"><?= esc(lang('Backoffice.logs_filter_date_from')) ?></label>
                <input class="form-control" type="date" id="filter_date_from" name="date_from" value="<?= esc((string) ($filters['date_from'] ?? ''), 'attr') ?>">
            </div>
            <div class="col-6 col-md-3 col-xl-1">
                <label class="form-label" for="filter_date_to"><?= esc(lang('Backoffice.logs_filter_date_to')) ?></label>
                <input class="form-control" type="date" id="filter_date_to" name="date_to" value="<?= esc((string) ($filters['date_to'] ?? ''), 'attr') ?>">
            </div>
        </div>
    </form>

    <div class="bo-table-toolbar">
        <label class="bo-table-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" class="form-control" id="logs-c-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>" aria-label="<?= esc(lang('Backoffice.search_placeholder')) ?>">
        </label>
        <?= view('Modules\AuditLog\Views\partials\export_toolbar', ['tableId' => 'logs-c-table', 'filename' => 'complainant-system-logs']) ?>
    </div>

    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="logs-c-table" data-page-length="10" data-order-col="0" data-order-dir="desc" data-dom="lrtip">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.logs_col_datetime')) ?></th>
                    <th><?= esc(lang('Backoffice.logs_col_complainant')) ?></th>
                    <th><?= esc(lang('Backoffice.logs_col_action')) ?></th>
                    <th><?= esc(lang('Backoffice.logs_col_table')) ?></th>
                    <th><?= esc(lang('Backoffice.logs_col_record_id')) ?></th>
                    <th><?= esc(lang('Backoffice.logs_col_ip')) ?></th>
                    <th><?= esc(lang('Backoffice.logs_col_browser')) ?></th>
                    <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $row): ?>
                    <tr>
                        <td data-order="<?= esc($row['created_at'], 'attr') ?>"><?= esc($row['created_at']) ?></td>
                        <td><?= esc($row['complainant_name']) ?></td>
                        <td><span class="bo-status-pill"><?= esc($row['action']) ?></span></td>
                        <td><code class="bo-route-code"><?= esc($row['table_cible']) ?></code></td>
                        <td><?= esc($row['enregistrement_id'] !== null ? (string) $row['enregistrement_id'] : '—') ?></td>
                        <td><?= esc($row['adresse_ip'] ?: '—') ?></td>
                        <td><span class="bo-ua-cell" title="<?= esc($row['user_agent'], 'attr') ?>"><?= esc(mb_strimwidth($row['user_agent'] ?: '—', 0, 48, '…')) ?></span></td>
                        <td>
                            <div class="bo-row-actions">
                                <?php if (can_access('backoffice/system-logs/complainants/show')): ?>
                                <a class="btn btn-bo-icon" href="<?= site_url('backoffice/system-logs/complainants/' . $row['id']) ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.logs_action_view'), 'attr') ?>">
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (! $items): ?>
            <p class="bo-empty-hint"><?= esc(lang('Backoffice.logs_empty')) ?></p>
        <?php endif; ?>
    </div>
</section>

<?= $this->endSection() ?>
