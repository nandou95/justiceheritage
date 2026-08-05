<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.nav_hearings')) ?></p>
        <h1><?= esc(lang('Backoffice.hrg_process_title')) ?></h1>
        <p><?= esc(trim(($hearing['nom_juridiction'] ?? '') . ' — ' . ($hearing['date_audience'] ?? '') . ' ' . substr((string) ($hearing['heure_audience'] ?? ''), 0, 5))) ?></p>
    </div>
    <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/hearings') ?>"><i class="bi bi-arrow-left"></i> <?= esc(lang('Backoffice.hrg_back_list')) ?></a>
</section>

<section class="bo-panel bo-crud-panel">
    <form class="bo-form needs-validation" method="post" action="<?= site_url('backoffice/hearings/' . (int) $hearing['audience_id'] . '/process') ?>" enctype="multipart/form-data" novalidate data-bo-hrg-process>
        <?= csrf_field() ?>

        <h2 class="h5"><?= esc(lang('Backoffice.hrg_section_hearing_info')) ?></h2>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label"><?= esc(lang('Backoffice.hrg_field_held')) ?> *</label>
                <select class="form-select" name="hearing_held" id="hearing_held" data-hrg-held required>
                    <option value="1"><?= esc(lang('Backoffice.yes')) ?></option>
                    <option value="0"><?= esc(lang('Backoffice.no')) ?></option>
                </select>
            </div>
        </div>

        <div data-hrg-held-no class="d-none">
            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <label class="form-label" for="motif_report"><?= esc(lang('Backoffice.hrg_field_postpone_reason')) ?> *</label>
                    <textarea class="form-control" id="motif_report" name="motif_report" rows="3"></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="new_hearing_date"><?= esc(lang('Backoffice.hrg_field_new_date')) ?></label>
                    <input class="form-control" type="date" id="new_hearing_date" name="new_hearing_date">
                </div>
            </div>
        </div>

        <div data-hrg-held-yes>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label" for="date_tenue"><?= esc(lang('Backoffice.hrg_field_actual_date')) ?> *</label>
                    <input class="form-control" type="date" id="date_tenue" name="date_tenue" value="<?= esc($hearing['date_audience'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="heure_debut"><?= esc(lang('Backoffice.hrg_field_start_time')) ?> *</label>
                    <input class="form-control" type="time" id="heure_debut" name="heure_debut" value="<?= esc(substr((string) ($hearing['heure_audience'] ?? ''), 0, 5)) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="heure_fin"><?= esc(lang('Backoffice.hrg_field_end_time')) ?> *</label>
                    <input class="form-control" type="time" id="heure_fin" name="heure_fin">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="rapport_valide" name="rapport_valide" value="1">
                        <label class="form-check-label" for="rapport_valide"><?= esc(lang('Backoffice.hrg_field_report_validated')) ?></label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label" for="rapport"><?= esc(lang('Backoffice.hrg_field_report')) ?> *</label>
                    <textarea class="form-control" id="rapport" name="rapport" rows="4"></textarea>
                </div>
            </div>
        </div>

        <h2 class="h5"><?= esc(lang('Backoffice.hrg_section_complaints')) ?></h2>
        <?php foreach ($complaints as $c): ?>
            <?php
            $apId = (int) $c['audience_plainte_id'];
            $parties = $partiesByComplaint[$apId] ?? [];
            ?>
            <div class="border rounded p-3 mb-3" data-hrg-complaint="<?= $apId ?>">
                <h3 class="h6"><code class="bo-route-code"><?= esc($c['numero_dossier'] ?? '') ?></code> — <?= esc($c['objet'] ?? '') ?></h3>
                <div class="row g-2 mb-2">
                    <div class="col-md-3">
                        <label class="form-label"><?= esc(lang('Backoffice.hrg_field_complaint_heard')) ?></label>
                        <select class="form-select" name="complaints[<?= $apId ?>][heard]">
                            <option value="1"><?= esc(lang('Backoffice.yes')) ?></option>
                            <option value="0"><?= esc(lang('Backoffice.no')) ?></option>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label"><?= esc(lang('Backoffice.hrg_field_observations')) ?></label>
                        <input class="form-control" name="complaints[<?= $apId ?>][observations]">
                    </div>
                    <div class="col-md-9">
                        <label class="form-label"><?= esc(lang('Backoffice.hrg_field_complaint_report')) ?></label>
                        <textarea class="form-control" name="complaints[<?= $apId ?>][rapport]" rows="2"></textarea>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="complaints[<?= $apId ?>][rapport_valide]" value="1">
                            <label class="form-check-label"><?= esc(lang('Backoffice.hrg_field_report_validated')) ?></label>
                        </div>
                    </div>
                </div>

                <h4 class="h6 mt-3"><?= esc(lang('Backoffice.hrg_section_attendance')) ?></h4>
                <div class="table-responsive mb-2">
                    <table class="table table-sm">
                        <thead><tr><th><?= esc(lang('Backoffice.people_col_name')) ?></th><th><?= esc(lang('Backoffice.hrg_col_role')) ?></th><th><?= esc(lang('Backoffice.hrg_field_present')) ?></th><th><?= esc(lang('Backoffice.hrg_field_observations')) ?></th></tr></thead>
                        <tbody>
                        <?php foreach ($parties as $p): ?>
                            <?php $roleId = (int) ($p['plainte_role_personne_id'] ?? 0); ?>
                            <tr>
                                <td><?= esc(trim(($p['prenom_personne'] ?? '') . ' ' . ($p['nom_personne'] ?? ''))) ?></td>
                                <td><?= esc($p['description_role_personne'] ?? '—') ?></td>
                                <td>
                                    <select class="form-select form-select-sm" name="complaints[<?= $apId ?>][attendance][<?= $roleId ?>][present]">
                                        <option value="1"><?= esc(lang('Backoffice.yes')) ?></option>
                                        <option value="0" selected><?= esc(lang('Backoffice.no')) ?></option>
                                    </select>
                                </td>
                                <td><input class="form-control form-control-sm" name="complaints[<?= $apId ?>][attendance][<?= $roleId ?>][observations]"></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <h4 class="h6"><?= esc(lang('Backoffice.hrg_section_documents')) ?></h4>
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label"><?= esc(lang('Backoffice.hrg_field_doc_file')) ?></label>
                        <input class="form-control" type="file" name="documents[<?= $apId ?>][]" accept=".pdf,.jpg,.jpeg,.png" multiple>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?= esc(lang('Backoffice.hrg_field_doc_description')) ?></label>
                        <input class="form-control" name="complaints[<?= $apId ?>][document_parties][0][description]">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?= esc(lang('Backoffice.hrg_field_doc_party')) ?></label>
                        <select class="form-select" name="complaints[<?= $apId ?>][document_parties][0][party_id]">
                            <option value=""><?= esc(lang('Backoffice.select_placeholder')) ?></option>
                            <?php foreach ($parties as $p): ?>
                                <option value="<?= esc($p['plainte_role_personne_id']) ?>"><?= esc(trim(($p['prenom_personne'] ?? '') . ' ' . ($p['nom_personne'] ?? ''))) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="bo-form-actions">
            <button class="btn btn-bo-primary" type="submit"><i class="bi bi-check-lg"></i> <?= esc(lang('Backoffice.hrg_process_save')) ?></button>
            <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/hearings') ?>"><?= esc(lang('Backoffice.btn_cancel')) ?></a>
        </div>
    </form>
</section>
<?= $this->endSection() ?>
