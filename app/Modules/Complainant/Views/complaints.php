<?= $this->extend('layouts/portal') ?>

<?= $this->section('content') ?>

<?php $complaints = $complaints ?? []; ?>

<?= view('Modules\Complainant\Views\partials\list_stats', [
    'listStats' => $listStats ?? [],
]) ?>

<section class="jh-dash-panel">
    <div class="jh-dash-panel-head">
        <div>
            <h2><?= esc(lang('Portal.list_h1')) ?></h2>
            <p><?= esc(lang('Portal.list_lead')) ?></p>
        </div>
    </div>

    <?= view('Modules\Complainant\Views\partials\complaint_list_table', [
        'complaints'   => $complaints,
        'emptyMessage' => lang('Portal.list_empty_message'),
        'pageLength'   => 10,
    ]) ?>
</section>

<?= $this->endSection() ?>
