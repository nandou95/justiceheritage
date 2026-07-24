<?= $this->extend('layouts/portal') ?>

<?= $this->section('content') ?>

<div class="jh-portal-head">
    <h1><?= esc(lang('Portal.prov_h1')) ?></h1>
    <p><?= esc(lang('Portal.prov_lead')) ?></p>
</div>

<div class="jh-panel mb-3">
    <div class="jh-panel-head">
        <h2><?= esc(lang('Portal.list_h1')) ?></h2>
    </div>
    <div class="jh-table-wrap">
        <table class="table table-hover jh-table jh-datatable w-100" data-order-col="0" data-order-dir="desc">
            <thead>
                <tr>
                    <th><?= esc(lang('Portal.list_ref')) ?></th>
                    <th><?= esc(lang('Portal.list_subject')) ?></th>
                    <th><?= esc(lang('Portal.list_court')) ?></th>
                    <th><?= esc(lang('Portal.list_status')) ?></th>
                    <th data-orderable="false" data-searchable="false"><?= esc(lang('Portal.list_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cases as $case): ?>
                    <tr>
                        <td><span class="jh-case-ref"><?= esc($case['id']) ?></span></td>
                        <td><?= esc($case['subject']) ?></td>
                        <td><?= esc($case['court_label']) ?></td>
                        <td>
                            <span class="jh-status <?= $case['status'] === 'judgment' ? 'is-judgment' : '' ?>">
                                <?= esc($case['status_label']) ?>
                            </span>
                        </td>
                        <td>
                            <button class="jh-table-link btn btn-link p-0" type="button"
                                    data-dt-select="#case_id"
                                    data-value="<?= esc($case['id']) ?>">
                                <?= esc(lang('Portal.list_select')) ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="jh-panel" style="max-width:40rem;">
    <div class="jh-auth-info" role="status"><?= esc(lang('Portal.new_demo')) ?></div>

    <form class="jh-portal-form" method="post" action="<?= site_url('portal/appeals/provincial') ?>">
        <?= csrf_field() ?>
        <div class="jh-field mb-3">
            <label class="form-label" for="case_id"><?= esc(lang('Portal.prov_case')) ?></label>
            <select class="form-select" id="case_id" name="case_id" required>
                <option value="">—</option>
                <?php foreach ($cases as $case): ?>
                    <option value="<?= esc($case['id']) ?>"><?= esc($case['id'] . ' · ' . $case['court_label']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="jh-field mb-3">
            <label class="form-label" for="grounds"><?= esc(lang('Portal.prov_grounds')) ?></label>
            <textarea class="form-control" id="grounds" name="grounds" rows="6" required></textarea>
        </div>
        <button class="btn btn-jh-primary" type="submit"><?= esc(lang('Portal.prov_submit')) ?></button>
    </form>
</div>

<?= $this->endSection() ?>
