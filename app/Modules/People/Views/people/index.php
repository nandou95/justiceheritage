<?= $this->extend('layouts/backoffice') ?>

<?= $this->section('content') ?>

<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_people')) ?></p>
        <h1><?= esc(lang('Backoffice.people_title')) ?></h1>
        <p><?= esc(lang('Backoffice.people_lead')) ?></p>
    </div>
    <a class="btn btn-bo-primary" href="<?= site_url('backoffice/people/create') ?>">
        <i class="bi bi-plus-lg" aria-hidden="true"></i>
        <?= esc(lang('Backoffice.people_new')) ?>
    </a>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-filters" method="get" action="<?= site_url('backoffice/people') ?>" data-bo-people-filters
          data-api-communes="<?= esc(site_url('api/communes'), 'attr') ?>"
          data-api-zones="<?= esc(site_url('api/zones'), 'attr') ?>"
          data-api-collines="<?= esc(site_url('api/collines'), 'attr') ?>">
        <div class="row g-2">
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label" for="filter_province"><?= esc(lang('Backoffice.filter_province_birth')) ?></label>
                <select class="form-select" id="filter_province" name="province_naissance_id" data-filter="province">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($provinces as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['province_naissance_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>>
                            <?= esc($opt['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label" for="filter_commune"><?= esc(lang('Backoffice.filter_commune_birth')) ?></label>
                <select class="form-select" id="filter_commune" name="commune_naissance_id" data-filter="commune">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($communes as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['commune_naissance_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>>
                            <?= esc($opt['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label" for="filter_zone"><?= esc(lang('Backoffice.filter_zone_birth')) ?></label>
                <select class="form-select" id="filter_zone" name="zone_naissance_id" data-filter="zone">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($zones as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['zone_naissance_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>>
                            <?= esc($opt['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label" for="filter_colline"><?= esc(lang('Backoffice.filter_colline_birth')) ?></label>
                <select class="form-select" id="filter_colline" name="colline_naissance_id" data-filter="colline">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($collines as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['colline_naissance_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>>
                            <?= esc($opt['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-1">
                <label class="form-label" for="filter_sexe"><?= esc(lang('Backoffice.filter_gender')) ?></label>
                <select class="form-select" id="filter_sexe" name="sexe_id">
                    <option value=""><?= esc(lang('Backoffice.filter_all')) ?></option>
                    <?php foreach ($sexes as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>" <?= (string) ($filters['sexe_id'] ?? '') === (string) $opt['id'] ? 'selected' : '' ?>>
                            <?= esc($opt['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label" for="filter_dob"><?= esc(lang('Backoffice.filter_date_of_birth')) ?></label>
                <input class="form-control" type="date" id="filter_dob" name="date_naissance" value="<?= esc($filters['date_naissance'] ?? '') ?>">
            </div>
            <div class="col-12 col-md-6 col-xl-1 d-flex align-items-end">
                <button class="btn btn-bo-secondary w-100" type="submit"><?= esc(lang('Backoffice.filter_apply')) ?></button>
            </div>
        </div>
    </form>

    <div class="bo-table-toolbar">
        <label class="bo-table-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" class="form-control" id="people-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>" aria-label="<?= esc(lang('Backoffice.search_placeholder')) ?>">
        </label>
    </div>

    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="people-table" data-page-length="10" data-order-col="0" data-order-dir="asc" data-dom="lrtip">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.people_col_name')) ?></th>
                    <th><?= esc(lang('Backoffice.people_col_gender')) ?></th>
                    <th><?= esc(lang('Backoffice.people_col_cni')) ?></th>
                    <th data-orderable="false"><?= esc(lang('Backoffice.people_col_cni_file')) ?></th>
                    <th><?= esc(lang('Backoffice.people_col_dob')) ?></th>
                    <th><?= esc(lang('Backoffice.people_col_contact')) ?></th>
                    <th><?= esc(lang('Backoffice.people_col_birthplace')) ?></th>
                    <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($people as $row): ?>
                    <tr>
                        <td><?= esc($row['full_name']) ?></td>
                        <td><?= esc($row['gender']) ?></td>
                        <td><?= esc($row['numero_cni']) ?></td>
                        <td>
                            <?php if ($row['has_cni_file']): ?>
                                <div class="bo-action-group">
                                    <a class="btn btn-bo-icon" href="<?= site_url('backoffice/people/' . $row['id'] . '/cni/view') ?>" target="_blank" rel="noopener" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.people_action_view_cni'), 'attr') ?>">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </a>
                                    <a class="btn btn-bo-icon" href="<?= site_url('backoffice/people/' . $row['id'] . '/cni/download') ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.people_action_download_cni'), 'attr') ?>">
                                        <i class="bi bi-download" aria-hidden="true"></i>
                                    </a>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($row['date_naissance']) ?></td>
                        <td>
                            <div class="bo-contact-cell">
                                <span><?= esc($row['email']) ?></span>
                                <small><?= esc($row['telephone']) ?></small>
                            </div>
                        </td>
                        <td><?= esc($row['place_of_birth']) ?></td>
                        <td>
                            <div class="bo-action-group">
                                <a class="btn btn-bo-icon" href="<?= site_url('backoffice/people/' . $row['id'] . '/edit') ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.people_action_edit'), 'attr') ?>">
                                    <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                </a>
                                <a class="btn btn-bo-icon" href="<?= site_url('backoffice/people/' . $row['id']) ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.people_action_view'), 'attr') ?>">
                                    <i class="bi bi-card-heading" aria-hidden="true"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?= $this->endSection() ?>
