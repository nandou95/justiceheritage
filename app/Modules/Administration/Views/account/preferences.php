<?= $this->extend('layouts/backoffice') ?>
<?= $this->section('content') ?>
<?= view('Modules\Administration\Views\partials\flash') ?>

<section class="bo-crud-head">
    <div>
        <p class="bo-crud-kicker"><?= esc(lang('Backoffice.account_kicker')) ?></p>
        <h1><?= esc(lang('Backoffice.account_preferences_title')) ?></h1>
        <p><?= esc(lang('Backoffice.account_preferences_lead')) ?></p>
    </div>
    <a class="btn btn-bo-secondary" href="<?= site_url('backoffice/my/profile') ?>">
        <i class="bi bi-arrow-left"></i> <?= esc(lang('Backoffice.account_back_profile')) ?>
    </a>
</section>

<section class="bo-panel bo-crud-panel">
    <p class="mb-0"><?= esc(lang('Backoffice.account_preferences_info')) ?></p>
</section>
<?= $this->endSection() ?>
