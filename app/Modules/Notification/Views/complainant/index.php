<?= $this->extend('layouts/backoffice') ?>

<?= $this->section('content') ?>

<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_notifications')) ?></p>
        <h1><?= esc(lang('Backoffice.ntf_complainant_title')) ?></h1>
        <p><?= esc(lang('Backoffice.ntf_complainant_lead')) ?></p>
    </div>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-filters" method="get" action="<?= site_url('backoffice/notifications/complainants') ?>" data-bo-log-filters
          data-api-communes="<?= esc(site_url('api/communes'), 'attr') ?>">
        <div class="row g-2">
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label" for="filter_canal"><?= esc(lang('Backoffice.ntf_filter_channel')) ?></label>
                <select class="form-select" id="filter_canal" name="canal_notification_id">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($channels as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['canal_notification_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>>
                            <?= esc($opt['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label" for="filter_statut"><?= esc(lang('Backoffice.ntf_filter_status')) ?></label>
                <select class="form-select" id="filter_statut" name="statut_notification_id">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($statuses as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['statut_notification_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>>
                            <?= esc($opt['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
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
                <label class="form-label" for="filter_personne"><?= esc(lang('Backoffice.ntf_filter_complainant')) ?></label>
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
                <label class="form-label" for="filter_plainte"><?= esc(lang('Backoffice.ntf_filter_complaint')) ?></label>
                <select class="form-select" id="filter_plainte" name="plainte_id">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($complaints as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['plainte_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>>
                            <?= esc($opt['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label" for="filter_date_from"><?= esc(lang('Backoffice.ntf_filter_date_from')) ?></label>
                <input class="form-control" type="date" id="filter_date_from" name="date_from" value="<?= esc((string) ($filters['date_from'] ?? ''), 'attr') ?>">
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label" for="filter_date_to"><?= esc(lang('Backoffice.ntf_filter_date_to')) ?></label>
                <input class="form-control" type="date" id="filter_date_to" name="date_to" value="<?= esc((string) ($filters['date_to'] ?? ''), 'attr') ?>">
            </div>
            <div class="col-12 col-md-6 col-xl-2 d-flex align-items-end">
                <button class="btn btn-bo-secondary w-100" type="submit"><?= esc(lang('Backoffice.filter_apply')) ?></button>
            </div>
        </div>
    </form>

    <div class="bo-table-toolbar">
        <label class="bo-table-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" class="form-control" id="ntf-c-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>" aria-label="<?= esc(lang('Backoffice.search_placeholder')) ?>">
        </label>
        <?= view('Modules\AuditLog\Views\partials\export_toolbar', ['tableId' => 'ntf-c-table', 'filename' => 'complainant-notifications']) ?>
    </div>

    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="ntf-c-table" data-page-length="10" data-order-col="5" data-order-dir="desc" data-dom="lrtip">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.ntf_col_subject')) ?></th>
                    <th><?= esc(lang('Backoffice.ntf_col_recipient')) ?></th>
                    <th><?= esc(lang('Backoffice.ntf_col_channel')) ?></th>
                    <th><?= esc(lang('Backoffice.ntf_col_case')) ?></th>
                    <th><?= esc(lang('Backoffice.ntf_col_status')) ?></th>
                    <th><?= esc(lang('Backoffice.ntf_col_sent')) ?></th>
                    <th><?= esc(lang('Backoffice.ntf_col_read')) ?></th>
                    <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $row): ?>
                    <tr>
                        <td><?= esc($row['subject']) ?></td>
                        <td><?= esc($row['recipient']) ?></td>
                        <td><?= esc($row['channel']) ?></td>
                        <td><?= esc($row['case_number']) ?></td>
                        <td><span class="bo-status-pill"><?= esc($row['status_label']) ?></span></td>
                        <td data-order="<?= esc($row['sent_at'], 'attr') ?>"><?= esc($row['sent_at']) ?></td>
                        <td><?= esc($row['read_at']) ?></td>
                        <td>
                            <div class="bo-row-actions">
                                <a class="btn btn-bo-icon" href="<?= site_url('backoffice/notifications/complainants/' . $row['id']) ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.ntf_action_view'), 'attr') ?>">
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                </a>
                                <form method="post" action="<?= site_url('backoffice/notifications/complainants/' . $row['id'] . '/resend') ?>" class="d-inline" onsubmit="return confirm('<?= esc(lang('Backoffice.ntf_resend_confirm'), 'js') ?>');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-bo-icon" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.ntf_action_resend'), 'attr') ?>">
                                        <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (! $items): ?>
            <p class="bo-empty-hint"><?= esc(lang('Backoffice.ntf_empty')) ?></p>
        <?php endif; ?>
    </div>
</section>

<?= $this->endSection() ?>
