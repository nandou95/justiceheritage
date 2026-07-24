<?= $this->extend('layouts/backoffice') ?>

<?= $this->section('content') ?>

<p class="bo-demo"><?= esc(lang('Backoffice.demo_notice')) ?></p>

<section class="bo-banner">
    <div class="bo-banner-ico" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M4 4h7v7H4V4Zm9 0h7v7h-7V4ZM4 13h7v7H4v-7Zm9 0h7v7h-7v-7Z" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
    </div>
    <div>
        <h1><?= esc(ucfirst(str_replace(['_', '-'], ' ', $key))) ?></h1>
        <p><?= esc(lang('Backoffice.portal_lead')) ?></p>
    </div>
</section>

<article class="bo-module">
    <h1><?= esc(ucfirst(str_replace(['_', '-'], ' ', $key))) ?></h1>
    <p><?= esc(lang('Backoffice.demo_notice')) ?></p>
    <a class="bo-access" href="<?= site_url('backoffice') ?>"><?= esc(lang('Backoffice.back_dashboard')) ?></a>
</article>

<?= $this->endSection() ?>
