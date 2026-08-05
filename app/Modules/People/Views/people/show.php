<?= $this->extend('layouts/backoffice') ?>

<?= $this->section('content') ?>

<?= view('Modules\Administration\Views\partials\flash') ?>

<?php
$fullName = trim(($record['prenom_personne'] ?? '') . ' ' . ($record['nom_personne'] ?? ''));
?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_people')) ?></p>
        <h1><?= esc(lang('Backoffice.people_details_title')) ?></h1>
        <p><?= esc($fullName) ?></p>
    </div>
    <div class="bo-crud-head-actions">
        <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/people') ?>">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            <?= esc(lang('Backoffice.people_back_list')) ?>
        </a>
        <a class="btn btn-bo-primary" href="<?= site_url('backoffice/people/' . (int) $record['personne_id'] . '/edit') ?>">
            <i class="bi bi-pencil-square" aria-hidden="true"></i>
            <?= esc(lang('Backoffice.people_action_edit')) ?>
        </a>
    </div>
</section>

<section class="bo-panel bo-crud-panel">
    <div class="bo-detail-grid">
        <article>
            <h2><?= esc(lang('Backoffice.people_section_identity')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.people_field_first_name')) ?></dt><dd><?= esc($record['prenom_personne'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.people_field_last_name')) ?></dt><dd><?= esc($record['nom_personne'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.people_field_gender')) ?></dt><dd><?= esc($record['description_sexe'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.people_field_birth_date')) ?></dt><dd><?= esc($record['date_naissance'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.people_field_cni')) ?></dt><dd><?= esc($record['numero_cni'] ?? '—') ?></dd></div>
                <div>
                    <dt><?= esc(lang('Backoffice.people_field_cni_file')) ?></dt>
                    <dd>
                        <?php if (! empty($record['upload_cni'])): ?>
                            <a href="<?= site_url('backoffice/people/' . (int) $record['personne_id'] . '/cni/view') ?>" target="_blank" rel="noopener" class="me-2">
                                <i class="bi bi-eye" aria-hidden="true"></i> <?= esc(lang('Backoffice.people_action_view_cni')) ?>
                            </a>
                            <a href="<?= site_url('backoffice/people/' . (int) $record['personne_id'] . '/cni/download') ?>">
                                <i class="bi bi-download" aria-hidden="true"></i> <?= esc(lang('Backoffice.people_action_download_cni')) ?>
                            </a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </dd>
                </div>
            </dl>
        </article>

        <article>
            <h2><?= esc(lang('Backoffice.people_section_contact')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.people_field_email')) ?></dt><dd><?= esc($record['email'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.people_field_phone')) ?></dt><dd><?= esc($record['telephone'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.people_field_address')) ?></dt><dd><?= esc($record['adresse_residence'] ?? '—') ?></dd></div>
            </dl>
        </article>

        <article>
            <h2><?= esc(lang('Backoffice.people_section_birthplace')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.people_field_province')) ?></dt><dd><?= esc($record['province_naissance_name'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.people_field_commune')) ?></dt><dd><?= esc($record['commune_naissance_name'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.people_field_zone')) ?></dt><dd><?= esc($record['zone_naissance_name'] ?? '—') ?></dd></div>
                <div><dt><?= esc(lang('Backoffice.people_field_colline')) ?></dt><dd><?= esc($record['colline_naissance_name'] ?? '—') ?></dd></div>
            </dl>
        </article>

        <article>
            <h2><?= esc(lang('Backoffice.people_section_meta')) ?></h2>
            <dl class="bo-detail-list">
                <div><dt><?= esc(lang('Backoffice.people_field_created')) ?></dt><dd><?= esc($record['create_at'] ?? '—') ?></dd></div>
            </dl>
        </article>
    </div>
</section>

<section class="bo-panel bo-crud-panel mt-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h2 class="h5 mb-1"><?= esc(lang('Backoffice.people_complaints_title')) ?></h2>
            <p class="mb-0 text-muted"><?= esc(lang('Backoffice.people_complaints_lead')) ?></p>
        </div>
        <label class="bo-table-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" class="form-control" id="people-complaints-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>" aria-label="<?= esc(lang('Backoffice.search_placeholder')) ?>">
        </label>
    </div>

    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="people-complaints-table" data-page-length="10" data-order-col="0" data-order-dir="desc" data-dom="lrtip">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.people_comp_case')) ?></th>
                    <th><?= esc(lang('Backoffice.people_comp_subject')) ?></th>
                    <th><?= esc(lang('Backoffice.people_comp_description')) ?></th>
                    <th><?= esc(lang('Backoffice.people_comp_role')) ?></th>
                    <th><?= esc(lang('Backoffice.people_comp_court')) ?></th>
                    <th><?= esc(lang('Backoffice.people_comp_level')) ?></th>
                    <th><?= esc(lang('Backoffice.people_comp_stage')) ?></th>
                    <th><?= esc(lang('Backoffice.people_comp_status')) ?></th>
                    <th><?= esc(lang('Backoffice.people_comp_filing')) ?></th>
                    <th><?= esc(lang('Backoffice.people_comp_appeal')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($complaints as $row): ?>
                    <tr>
                        <td><code class="bo-route-code"><?= esc($row['case_number']) ?></code></td>
                        <td><?= esc($row['subject']) ?></td>
                        <td><?= esc($row['description']) ?></td>
                        <td><?= esc($row['role']) ?></td>
                        <td><?= esc($row['jurisdiction']) ?></td>
                        <td><?= esc($row['level']) ?></td>
                        <td><?= esc($row['stage']) ?></td>
                        <td><?= esc($row['status']) ?></td>
                        <td><?= esc($row['filing_date']) ?></td>
                        <td><?= esc($row['appeal_label']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?= $this->endSection() ?>
