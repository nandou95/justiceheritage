<?= $this->extend('layouts/portal') ?>

<?= $this->section('content') ?>

<div class="jh-portal-head">
    <h1><?= esc(lang('Portal.list_h1')) ?></h1>
    <p><?= esc(lang('Portal.list_lead')) ?></p>
    <div class="jh-portal-cta-row">
        <a class="btn btn-jh-primary" href="<?= site_url('portal/complaints/new') ?>"><?= esc(lang('Portal.nav_new')) ?></a>
    </div>
</div>

<div class="jh-panel">
    <div class="jh-table-wrap">
        <table class="table table-hover jh-table jh-datatable w-100" data-order-col="4" data-order-dir="desc">
            <thead>
                <tr>
                    <th><?= esc(lang('Portal.list_ref')) ?></th>
                    <th><?= esc(lang('Portal.list_subject')) ?></th>
                    <th><?= esc(lang('Portal.list_court')) ?></th>
                    <th><?= esc(lang('Portal.list_status')) ?></th>
                    <th><?= esc(lang('Portal.list_updated')) ?></th>
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
                        <td><?= esc($case['updated']) ?></td>
                        <td>
                            <a class="jh-table-link" href="<?= site_url('portal/complaints/' . $case['id']) ?>">
                                <?= esc(lang('Portal.list_open')) ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
