<?= $this->extend('layouts/backoffice') ?>

<?= $this->section('content') ?>

<?php
$kpis   = $kpis ?? [];
$charts = $charts ?? [];
$tables = $tables ?? [];
$lead   = $lead ?? '';

$kpiIcon = static function (string $name): string {
    $map = [
        'inbox'    => 'bi-inbox',
        'plus'     => 'bi-plus-circle',
        'clock'    => 'bi-clock',
        'search'   => 'bi-search',
        'check'    => 'bi-check-circle',
        'appeal'   => 'bi-arrow-up-right',
        'calendar' => 'bi-calendar3',
        'mail'     => 'bi-envelope',
        'people'   => 'bi-people',
        'users'    => 'bi-person',
        'layers'   => 'bi-layers',
        'building' => 'bi-building',
        'x'        => 'bi-x-circle',
        'star'     => 'bi-star',
        'bell'     => 'bi-bell',
        'chat'     => 'bi-chat-dots',
        'eye'      => 'bi-eye',
        'map'      => 'bi-geo-alt',
        'shield'   => 'bi-shield-check',
        'scale'    => 'bi-bank',
        'transfer' => 'bi-arrow-left-right',
    ];

    return $map[$name] ?? 'bi-bar-chart';
};

$wideTypes = ['province-map', 'multi-line', 'funnel', 'calendar', 'line'];
?>

<section class="bo-banner bo-dash-banner">
    <div class="bo-banner-ico" aria-hidden="true">
        <i class="bi bi-speedometer2"></i>
    </div>
    <div>
        <h1><?= esc($title ?? '') ?></h1>
        <p><?= esc($lead) ?></p>
    </div>
</section>

<?php if ($kpis !== []): ?>
<section class="bo-kpi-grid" aria-label="<?= esc(lang('Backoffice.dash_kpi_section')) ?>">
    <?php foreach ($kpis as $kpi): ?>
        <article class="bo-kpi-card">
            <div class="bo-kpi-ico" aria-hidden="true">
                <i class="bi <?= esc($kpiIcon((string) ($kpi['icon'] ?? 'inbox'))) ?>"></i>
            </div>
            <div class="bo-kpi-body">
                <span class="bo-kpi-label"><?= esc((string) ($kpi['label'] ?? '')) ?></span>
                <strong class="bo-kpi-value"><?php
                    $raw = $kpi['value'] ?? 0;
                    if (is_float($raw) || (is_numeric($raw) && str_contains((string) $raw, '.'))) {
                        echo esc(rtrim(rtrim(number_format((float) $raw, 2, '.', ''), '0'), '.'));
                    } else {
                        echo esc((string) $raw);
                    }
                ?></strong>
            </div>
        </article>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<?php if ($charts !== []): ?>
<section class="bo-dash-charts" aria-label="<?= esc(lang('Backoffice.dash_reports_section')) ?>">
    <?php foreach ($charts as $chart): ?>
        <?php
        $type = (string) ($chart['type'] ?? 'bar');
        $isWide = in_array($type, $wideTypes, true);
        ?>
        <article class="bo-dash-chart-card<?= $isWide ? ' is-wide' : '' ?>" data-chart-id="<?= esc((string) ($chart['id'] ?? ''), 'attr') ?>">
            <header class="bo-dash-chart-head">
                <h2><?= esc((string) ($chart['title'] ?? '')) ?></h2>
            </header>
            <div class="bo-dash-chart-body" data-chart-type="<?= esc($type, 'attr') ?>">
                <?php if ($type === 'province-map'): ?>
                    <div class="bo-dash-map" data-role="map"></div>
                    <ul class="bo-dash-map-legend" data-role="map-legend"></ul>
                <?php elseif ($type === 'calendar'): ?>
                    <div class="bo-dash-calendar" data-role="calendar"></div>
                <?php elseif ($type === 'funnel'): ?>
                    <div class="bo-dash-funnel" data-role="funnel"></div>
                <?php elseif ($type === 'gauge'): ?>
                    <div class="bo-dash-gauge-wrap">
                        <canvas data-role="canvas"></canvas>
                        <div class="bo-dash-gauge-label" data-role="gauge-label"></div>
                    </div>
                <?php else: ?>
                    <canvas data-role="canvas"></canvas>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<?php if ($tables !== []): ?>
<section class="bo-dash-tables" aria-label="<?= esc(lang('Backoffice.dash_tables_section')) ?>">
    <?php foreach ($tables as $table): ?>
        <article class="bo-dash-table-card">
            <header class="bo-dash-chart-head">
                <h2><?= esc((string) ($table['title'] ?? '')) ?></h2>
            </header>
            <div class="bo-table-wrap">
                <table class="table table-hover jh-table w-100">
                    <thead>
                        <tr>
                            <?php foreach (($table['headers'] ?? []) as $header): ?>
                                <th><?= esc((string) $header) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($table['rows'])): ?>
                            <tr>
                                <td colspan="<?= max(1, count($table['headers'] ?? [])) ?>"><?= esc(lang('Backoffice.dash_empty')) ?></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($table['rows'] as $row): ?>
                                <tr>
                                    <?php foreach ($row as $cell): ?>
                                        <td><?= esc((string) $cell) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<script type="application/json" id="jh-dashboard-data"><?= json_encode([
    'charts' => $charts,
    'i18n'   => [
        'empty'   => lang('Backoffice.dash_empty'),
        'noData'  => lang('Backoffice.dash_chart_empty'),
        'value'   => lang('Backoffice.dash_map_value'),
        'weekdays'=> [
            lang('Backoffice.dash_cal_mon'),
            lang('Backoffice.dash_cal_tue'),
            lang('Backoffice.dash_cal_wed'),
            lang('Backoffice.dash_cal_thu'),
            lang('Backoffice.dash_cal_fri'),
            lang('Backoffice.dash_cal_sat'),
            lang('Backoffice.dash_cal_sun'),
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) ?></script>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
<script src="<?= public_asset('assets/js/dashboard.js') ?>"></script>
<?= $this->endSection() ?>
