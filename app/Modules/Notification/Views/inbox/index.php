<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.notifications')) ?></p>
        <h1><?= esc(lang('Backoffice.inbox_title')) ?></h1>
        <p><?= esc(lang('Backoffice.inbox_lead')) ?></p>
    </div>
</section>

<section class="bo-panel bo-crud-panel">
    <div class="bo-table-toolbar">
        <label class="bo-table-search"><i class="bi bi-search"></i>
            <input type="search" class="form-control" id="inbox-table-search" placeholder="<?= esc(lang('Backoffice.search_placeholder'), 'attr') ?>">
        </label>
    </div>
    <div class="table-responsive bo-table-wrap">
        <table class="table table-hover bo-table jh-datatable w-100" id="inbox-table" data-page-length="10" data-dom="lrtip">
            <thead>
                <tr>
                    <th><?= esc(lang('Backoffice.ntf_col_subject')) ?></th>
                    <th><?= esc(lang('Backoffice.ntf_col_channel')) ?></th>
                    <th><?= esc(lang('Backoffice.ntf_col_status')) ?></th>
                    <th><?= esc(lang('Backoffice.inbox_col_date')) ?></th>
                    <th data-orderable="false" data-searchable="false"><?= esc(lang('Backoffice.col_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($notifications)): ?>
                <tr><td colspan="5" class="text-muted"><?= esc(lang('Backoffice.inbox_empty')) ?></td></tr>
            <?php else: ?>
                <?php foreach ($notifications as $row): ?>
                    <tr class="<?= ! empty($row['is_unread']) ? 'fw-semibold' : '' ?>">
                        <td>
                            <?= esc($row['subject']) ?>
                            <div class="small text-muted"><?= esc($row['preview']) ?></div>
                        </td>
                        <td><?= esc($row['channel']) ?></td>
                        <td><?= esc($row['status'] ?: ($row['is_unread'] ? lang('Backoffice.inbox_status_unread') : lang('Backoffice.inbox_status_read'))) ?></td>
                        <td><?= esc($row['created_fmt']) ?></td>
                        <td>
                            <a class="btn btn-bo-icon" href="<?= esc($row['url']) ?>" data-bs-toggle="tooltip" title="<?= esc(lang('Backoffice.ntf_action_view'), 'attr') ?>">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?= $this->endSection() ?>
