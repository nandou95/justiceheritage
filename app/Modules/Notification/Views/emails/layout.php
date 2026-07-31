<!DOCTYPE html>

<html lang="<?= esc(service('request')->getLocale()) ?>">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= esc($appName ?? 'JusticeHeritage') ?></title>

    <style>

        body { margin: 0; padding: 0; background: #f3f6f4; font-family: Arial, Helvetica, sans-serif; color: #122018; }

        .jh-mail-wrap { width: 100%; padding: 24px 12px; }

        .jh-mail-card { max-width: 560px; margin: 0 auto; background: #ffffff; border: 1px solid #e5ece8; border-radius: 12px; overflow: hidden; }

        .jh-mail-head { background: linear-gradient(145deg, #0a3227, #176b4f); color: #fff; padding: 20px 24px; }

        .jh-mail-head strong { font-size: 18px; letter-spacing: 0.02em; }

        .jh-mail-body { padding: 24px; line-height: 1.55; font-size: 15px; }

        .jh-mail-body p { margin: 0 0 14px; }

        .jh-mail-btn { display: inline-block; background: #176b4f; color: #fff !important; text-decoration: none; padding: 10px 16px; border-radius: 8px; font-weight: 700; }

        .jh-mail-creds { width: 100%; margin: 0 0 16px; border-collapse: collapse; }

        .jh-mail-creds td { padding: 8px 10px; border: 1px solid #e5ece8; font-size: 14px; }

        .jh-mail-creds td:first-child { width: 36%; background: #f7faf8; color: #52606d; }

        .jh-mail-foot { padding: 0 24px 22px; color: #7b8794; font-size: 12px; line-height: 1.45; }

    </style>

</head>

<body>

    <div class="jh-mail-wrap">

        <div class="jh-mail-card">

            <div class="jh-mail-head">

                <strong><?= esc($appName ?? 'JusticeHeritage') ?></strong>

            </div>

            <div class="jh-mail-body">

                <?= $this->renderSection('content') ?>

            </div>

            <div class="jh-mail-foot">

                <?= esc(lang('Mail.footer_note')) ?>

            </div>

        </div>

    </div>

</body>

</html>

