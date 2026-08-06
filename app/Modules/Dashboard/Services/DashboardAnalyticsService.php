<?php

namespace Modules\Dashboard\Services;

/**
 * Analytics aggregations for Back Office dashboards.
 * All queries are empty-safe and return zeroed series when tables have no rows.
 */
class DashboardAnalyticsService
{
    private const PALETTE = [
        '#176b4f', '#c9a227', '#0ea5e9', '#7c3aed', '#dc2626',
        '#ea580c', '#0891b2', '#4f46e5', '#059669', '#be185d',
    ];

    /**
     * @return array<string, mixed>
     */
    public function complaints(): array
    {
        $byStatusRaw = $this->groupCount(
            "SELECT LOWER(COALESCE(s.description_statut_plainte, 'unknown')) AS label, COUNT(*)::int AS total
             FROM plainte.plainte p
             LEFT JOIN plainte.statut_plainte s ON s.statut_plainte_id = p.statut_plainte_id
             GROUP BY 1 ORDER BY total DESC"
        );

        $statusBuckets = [
            'pending'      => 0,
            'in_progress'  => 0,
            'under_review' => 0,
            'closed'       => 0,
            'appealed'     => 0,
        ];
        foreach ($byStatusRaw as $row) {
            $l = (string) $row['label'];
            $n = (int) $row['total'];
            if ($this->labelHas($l, ['annul'])) {
                continue;
            }
            if ($this->labelHas($l, ['recours', 'appeal'])) {
                $statusBuckets['appealed'] += $n;
            } elseif ($this->labelHas($l, ['clotur', 'clos', 'closed'])) {
                $statusBuckets['closed'] += $n;
            } elseif ($this->labelHas($l, ['en_cours', 'en cours', 'progress'])) {
                $statusBuckets['in_progress'] += $n;
            } elseif ($this->labelHas($l, ['review', 'examen'])) {
                $statusBuckets['under_review'] += $n;
            } else {
                $statusBuckets['pending'] += $n;
            }
        }

        $total = (int) $this->scalar('SELECT COUNT(*)::int FROM plainte.plainte');
        $today = (int) $this->scalar("SELECT COUNT(*)::int FROM plainte.plainte WHERE date_depot = CURRENT_DATE");
        $awaitingSummons = (int) $this->scalar(
            "SELECT COUNT(*)::int FROM plainte.plainte p
             INNER JOIN plainte.etape_plainte e ON e.etape_plainte_id = p.etape_plainte_id
             WHERE e.is_convocation = TRUE"
        );
        $awaitingHearing = (int) $this->scalar(
            "SELECT COUNT(*)::int FROM plainte.plainte p
             INNER JOIN plainte.etape_plainte e ON e.etape_plainte_id = p.etape_plainte_id
             WHERE e.is_audience = TRUE"
        );
        $appealed = (int) $this->scalar('SELECT COUNT(*)::int FROM plainte.plainte WHERE COALESCE(is_recours, false) = TRUE');
        $avgDays = (float) $this->scalar(
            "SELECT COALESCE(AVG(days), 0) FROM (
                SELECT MIN(EXTRACT(EPOCH FROM (v.date_verdict::timestamp - p.date_depot::timestamp)) / 86400.0) AS days
                FROM plainte.plainte p
                INNER JOIN audience.audience_plainte ap ON ap.plainte_id = p.plainte_id
                INNER JOIN verdict.verdict v ON v.audience_plainte_id = ap.audience_plainte_id
                WHERE p.date_depot IS NOT NULL
                GROUP BY p.plainte_id
             ) t"
        );

        $byLevel = $this->groupCount(
            "SELECT COALESCE(n.desc_niveau_juridiction, '—') AS label, COUNT(*)::int AS total
             FROM plainte.plainte p
             LEFT JOIN juridiction.niveau_juridiction n ON n.niveau_juridiction_id = p.niveau_juridiction_id
             GROUP BY 1 ORDER BY total DESC"
        );
        $byCourt = $this->groupCount(
            "SELECT COALESCE(j.nom_juridiction, '—') AS label, COUNT(*)::int AS total
             FROM plainte.plainte p
             LEFT JOIN juridiction.juridiction j ON j.juridiction_id = p.juridiction_id
             GROUP BY 1 ORDER BY total DESC LIMIT 12"
        );
        $byProvince = $this->provinceSeries(
            "SELECT j.province_id AS id, COUNT(*)::int AS total
             FROM plainte.plainte p
             INNER JOIN juridiction.juridiction j ON j.juridiction_id = p.juridiction_id
             WHERE j.province_id IS NOT NULL
             GROUP BY j.province_id"
        );
        $monthly = $this->monthlySeries(
            "SELECT to_char(date_trunc('month', date_depot), 'YYYY-MM') AS label, COUNT(*)::int AS total
             FROM plainte.plainte
             WHERE date_depot IS NOT NULL
             GROUP BY 1 ORDER BY 1"
        );
        $funnel = [
            ['label' => lang('Backoffice.dash_funnel_registered'), 'value' => $total],
            ['label' => lang('Backoffice.dash_funnel_review'), 'value' => $statusBuckets['under_review'] + $statusBuckets['in_progress']],
            ['label' => lang('Backoffice.dash_funnel_summons'), 'value' => (int) $this->scalar('SELECT COUNT(*)::int FROM convocation.convocation')],
            ['label' => lang('Backoffice.dash_funnel_hearing_sched'), 'value' => (int) $this->scalar('SELECT COUNT(*)::int FROM audience.audience')],
            ['label' => lang('Backoffice.dash_funnel_hearing_held'), 'value' => (int) $this->scalar(
                "SELECT COUNT(*)::int FROM audience.audience a
                 INNER JOIN audience.statut_audience s ON s.statut_audience_id = a.statut_audience_id
                 WHERE LOWER(s.description_statut_audience) LIKE '%tenu%'
                    OR LOWER(s.description_statut_audience) LIKE '%held%'"
            )],
            ['label' => lang('Backoffice.dash_funnel_verdict'), 'value' => (int) $this->scalar('SELECT COUNT(*)::int FROM verdict.verdict')],
            ['label' => lang('Backoffice.dash_funnel_appeal'), 'value' => (int) $this->scalar('SELECT COUNT(*)::int FROM recours.recours')],
            ['label' => lang('Backoffice.dash_funnel_closed'), 'value' => $statusBuckets['closed']],
        ];
        $bySource = [
            ['label' => lang('Backoffice.dash_source_portal'), 'value' => (int) $this->scalar('SELECT COUNT(*)::int FROM plainte.plainte WHERE COALESCE(est_cree_par_plaigant, false) = TRUE')],
            ['label' => lang('Backoffice.dash_source_staff'), 'value' => (int) $this->scalar('SELECT COUNT(*)::int FROM plainte.plainte WHERE COALESCE(est_cree_par_plaigant, false) = FALSE')],
        ];
        $byJudge = $this->groupCount(
            "SELECT TRIM(CONCAT(COALESCE(u.prenom_utilisateur,''),' ',COALESCE(u.nom_utilisateur,''))) AS label, COUNT(*)::int AS total
             FROM audience.audience a
             INNER JOIN administration.utilisateur u ON u.utilisateur_id = a.juge_id
             GROUP BY 1 ORDER BY total DESC LIMIT 10"
        );
        $byType = $this->groupCount(
            "SELECT COALESCE(NULLIF(TRIM(objet), ''), '—') AS label, COUNT(*)::int AS total
             FROM plainte.plainte
             GROUP BY 1 ORDER BY total DESC LIMIT 10"
        );

        return $this->payload(
            lang('Backoffice.dash_cmp_title'),
            lang('Backoffice.dash_cmp_lead'),
            'dash-complaints',
            [
                $this->kpi('total', lang('Backoffice.dash_kpi_total_complaints'), $total, 'inbox'),
                $this->kpi('today', lang('Backoffice.dash_kpi_new_today'), $today, 'plus'),
                $this->kpi('pending', lang('Backoffice.dash_kpi_pending'), $statusBuckets['pending'], 'clock'),
                $this->kpi('investigation', lang('Backoffice.dash_kpi_investigation'), $statusBuckets['in_progress'], 'search'),
                $this->kpi('closed', lang('Backoffice.dash_kpi_closed'), $statusBuckets['closed'], 'check'),
                $this->kpi('appealed', lang('Backoffice.dash_kpi_appealed'), $appealed, 'appeal'),
                $this->kpi('await_hearing', lang('Backoffice.dash_kpi_await_hearing'), $awaitingHearing, 'calendar'),
                $this->kpi('await_summons', lang('Backoffice.dash_kpi_await_summons'), $awaitingSummons, 'mail'),
            ],
            [
                $this->chart('statusPie', lang('Backoffice.dash_chart_status_dist'), 'pie', array_keys($statusBuckets), array_values($statusBuckets), [
                    'labelMap' => [
                        'pending' => lang('Backoffice.dash_status_pending'),
                        'in_progress' => lang('Backoffice.dash_status_in_progress'),
                        'under_review' => lang('Backoffice.dash_status_under_review'),
                        'closed' => lang('Backoffice.dash_status_closed'),
                        'appealed' => lang('Backoffice.dash_status_appealed'),
                    ],
                ]),
                $this->chart('levelBar', lang('Backoffice.dash_chart_by_level'), 'bar', array_column($byLevel, 'label'), array_column($byLevel, 'total')),
                $this->chart('courtHBar', lang('Backoffice.dash_chart_by_court'), 'bar-horizontal', array_column($byCourt, 'label'), array_column($byCourt, 'total')),
                $this->chart('provinceMap', lang('Backoffice.dash_chart_by_province'), 'province-map', [], [], ['points' => $byProvince]),
                $this->chart('monthlyLine', lang('Backoffice.dash_chart_monthly'), 'line', array_column($monthly, 'label'), array_column($monthly, 'total')),
                $this->chart('avgGauge', lang('Backoffice.dash_chart_avg_processing'), 'gauge', [], [], ['value' => round($avgDays, 1), 'max' => max(30, (int) ceil($avgDays * 1.5) ?: 30), 'unit' => lang('Backoffice.dash_unit_days')]),
                $this->chart('workflowFunnel', lang('Backoffice.dash_chart_workflow'), 'funnel', array_column($funnel, 'label'), array_column($funnel, 'value')),
                $this->chart('typesPie', lang('Backoffice.dash_chart_complaint_types'), 'pie', array_column($byType, 'label'), array_column($byType, 'total')),
                $this->chart('sourcePie', lang('Backoffice.dash_chart_filing_source'), 'pie', array_column($bySource, 'label'), array_column($bySource, 'value')),
                $this->chart('judgeBar', lang('Backoffice.dash_chart_per_judge'), 'bar', array_column($byJudge, 'label'), array_column($byJudge, 'total')),
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function complainants(): array
    {
        $total = (int) $this->scalar('SELECT COUNT(*)::int FROM plaignant.personne');
        $month = (int) $this->scalar("SELECT COUNT(*)::int FROM plaignant.personne WHERE date_trunc('month', create_at) = date_trunc('month', CURRENT_DATE)");
        $bySex = $this->groupCount(
            "SELECT COALESCE(s.description_sexe, '—') AS label, COUNT(*)::int AS total
             FROM plaignant.personne p
             LEFT JOIN plaignant.sexe s ON s.sexe_id = p.sexe_id
             GROUP BY 1 ORDER BY total DESC"
        );
        $male = 0;
        $female = 0;
        foreach ($bySex as $row) {
            $l = mb_strtolower((string) $row['label']);
            if (str_contains($l, 'fem') || str_contains($l, 'woman')) {
                $female += (int) $row['total'];
            } elseif (str_contains($l, 'hom') || str_contains($l, 'male') || str_contains($l, 'man')) {
                $male += (int) $row['total'];
            }
        }

        $minors = (int) $this->scalar(
            "SELECT COUNT(*)::int FROM plaignant.personne
             WHERE date_naissance IS NOT NULL
               AND EXTRACT(YEAR FROM age(date_naissance)) BETWEEN 16 AND 17"
        );
        $adults = (int) $this->scalar(
            "SELECT COUNT(*)::int FROM plaignant.personne
             WHERE date_naissance IS NOT NULL
               AND EXTRACT(YEAR FROM age(date_naissance)) >= 18"
        );
        $active = (int) $this->scalar(
            "SELECT COUNT(*)::int FROM plaignant.personne WHERE user_name IS NOT NULL AND user_name <> ''"
        );
        $avgComplaints = (float) $this->scalar(
            "SELECT COALESCE(AVG(cnt), 0) FROM (
                SELECT COUNT(pr.plainte_id)::float AS cnt
                FROM plaignant.personne p
                LEFT JOIN plaignant.plainte_role_personne pr ON pr.personne_id = p.personne_id
                GROUP BY p.personne_id
             ) t"
        );
        $withCreds = (int) $this->scalar("SELECT COUNT(*)::int FROM plaignant.personne WHERE mot_de_passe_hash IS NOT NULL AND mot_de_passe_hash <> ''");
        $completion = $total > 0 ? round(($withCreds / $total) * 100, 1) : 0.0;

        $byProvince = $this->provinceSeries(
            "SELECT province_naissance_id AS id, COUNT(*)::int AS total
             FROM plaignant.personne WHERE province_naissance_id IS NOT NULL GROUP BY 1"
        );
        $ageBands = [
            '16-20' => (int) $this->scalar("SELECT COUNT(*)::int FROM plaignant.personne WHERE date_naissance IS NOT NULL AND EXTRACT(YEAR FROM age(date_naissance)) BETWEEN 16 AND 20"),
            '21-30' => (int) $this->scalar("SELECT COUNT(*)::int FROM plaignant.personne WHERE date_naissance IS NOT NULL AND EXTRACT(YEAR FROM age(date_naissance)) BETWEEN 21 AND 30"),
            '31-40' => (int) $this->scalar("SELECT COUNT(*)::int FROM plaignant.personne WHERE date_naissance IS NOT NULL AND EXTRACT(YEAR FROM age(date_naissance)) BETWEEN 31 AND 40"),
            '41-50' => (int) $this->scalar("SELECT COUNT(*)::int FROM plaignant.personne WHERE date_naissance IS NOT NULL AND EXTRACT(YEAR FROM age(date_naissance)) BETWEEN 41 AND 50"),
            '50+'   => (int) $this->scalar("SELECT COUNT(*)::int FROM plaignant.personne WHERE date_naissance IS NOT NULL AND EXTRACT(YEAR FROM age(date_naissance)) >= 51"),
        ];
        $monthly = $this->monthlySeries(
            "SELECT to_char(date_trunc('month', create_at), 'YYYY-MM') AS label, COUNT(*)::int AS total
             FROM plaignant.personne WHERE create_at IS NOT NULL GROUP BY 1 ORDER BY 1"
        );
        $topActive = $this->groupCount(
            "SELECT TRIM(CONCAT(COALESCE(p.prenom_personne,''),' ',COALESCE(p.nom_personne,''))) AS label, COUNT(pr.plainte_id)::int AS total
             FROM plaignant.personne p
             INNER JOIN plaignant.plainte_role_personne pr ON pr.personne_id = p.personne_id
             GROUP BY 1 ORDER BY total DESC LIMIT 10"
        );

        return $this->payload(
            lang('Backoffice.dash_plaignant_title'),
            lang('Backoffice.dash_plaignant_lead'),
            'dash-complainants',
            [
                $this->kpi('total', lang('Backoffice.dash_kpi_total_complainants'), $total, 'people'),
                $this->kpi('month', lang('Backoffice.dash_kpi_new_month'), $month, 'plus'),
                $this->kpi('male', lang('Backoffice.dash_kpi_male'), $male, 'users'),
                $this->kpi('female', lang('Backoffice.dash_kpi_female'), $female, 'users'),
                $this->kpi('minors', lang('Backoffice.dash_kpi_minors'), $minors, 'people'),
                $this->kpi('adults', lang('Backoffice.dash_kpi_adults'), $adults, 'people'),
                $this->kpi('active', lang('Backoffice.dash_kpi_active_accounts'), $active, 'check'),
                $this->kpi('avg', lang('Backoffice.dash_kpi_avg_complaints'), round($avgComplaints, 2), 'inbox'),
            ],
            [
                $this->chart('provinceMap', lang('Backoffice.dash_chart_complainants_province'), 'province-map', [], [], ['points' => $byProvince]),
                $this->chart('genderPie', lang('Backoffice.dash_chart_gender'), 'pie', array_column($bySex, 'label'), array_column($bySex, 'total')),
                $this->chart('ageHist', lang('Backoffice.dash_chart_age'), 'bar', array_keys($ageBands), array_values($ageBands)),
                $this->chart('regLine', lang('Backoffice.dash_chart_registration_trend'), 'line', array_column($monthly, 'label'), array_column($monthly, 'total')),
                $this->chart('topActive', lang('Backoffice.dash_chart_most_active'), 'bar-horizontal', array_column($topActive, 'label'), array_column($topActive, 'total')),
                $this->chart('completionGauge', lang('Backoffice.dash_chart_completion_rate'), 'gauge', [], [], ['value' => $completion, 'max' => 100, 'unit' => '%']),
                $this->chart('geoHeat', lang('Backoffice.dash_chart_geo_heat'), 'province-map', [], [], ['points' => $byProvince]),
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function appeals(): array
    {
        $total = (int) $this->scalar('SELECT COUNT(*)::int FROM recours.recours');
        $byLevel = $this->groupCount(
            "SELECT COALESCE(n.desc_niveau_juridiction, '—') AS label, COUNT(*)::int AS total
             FROM recours.recours r
             LEFT JOIN juridiction.niveau_juridiction n ON n.niveau_juridiction_id = r.niveau_juridiction_id
             GROUP BY 1 ORDER BY total DESC"
        );
        $provincial = 0;
        $regional = 0;
        $ministry = 0;
        foreach ($byLevel as $row) {
            $l = mb_strtolower((string) $row['label']);
            $n = (int) $row['total'];
            if (str_contains($l, 'prov')) {
                $provincial += $n;
            } elseif (str_contains($l, 'regio')) {
                $regional += $n;
            } elseif (str_contains($l, 'minist')) {
                $ministry += $n;
            }
        }

        // Outcomes approximated from linked complaint status / deadline flag
        $pending = (int) $this->scalar(
            "SELECT COUNT(*)::int FROM recours.recours r
             LEFT JOIN plainte.plainte p ON p.plainte_id = r.nouvelle_plainte_id
             LEFT JOIN plainte.statut_plainte s ON s.statut_plainte_id = p.statut_plainte_id
             WHERE s.description_statut_plainte IS NULL
                OR (LOWER(s.description_statut_plainte) NOT LIKE '%clotur%'
                AND LOWER(s.description_statut_plainte) NOT LIKE '%annul%')"
        );
        $accepted = (int) $this->scalar(
            "SELECT COUNT(*)::int FROM recours.recours WHERE COALESCE(dans_les_delais, false) = TRUE"
        );
        $rejected = max(0, $total - $pending);
        $successRate = $total > 0 ? round(($accepted / $total) * 100, 1) : 0.0;
        $avgDays = (float) $this->scalar(
            "SELECT COALESCE(AVG(EXTRACT(EPOCH FROM (CURRENT_DATE::timestamp - r.date_recours::timestamp)) / 86400.0), 0)
             FROM recours.recours r
             LEFT JOIN plainte.plainte p ON p.plainte_id = r.nouvelle_plainte_id
             LEFT JOIN plainte.statut_plainte s ON s.statut_plainte_id = p.statut_plainte_id
             WHERE s.description_statut_plainte IS NULL
                OR LOWER(s.description_statut_plainte) NOT LIKE '%clotur%'"
        );

        $byProvince = $this->provinceSeries(
            "SELECT j.province_id AS id, COUNT(*)::int AS total
             FROM recours.recours r
             INNER JOIN juridiction.juridiction j ON j.juridiction_id = r.juridiction_id
             WHERE j.province_id IS NOT NULL GROUP BY 1"
        );
        $byCourt = $this->groupCount(
            "SELECT COALESCE(j.nom_juridiction, '—') AS label, COUNT(*)::int AS total
             FROM recours.recours r
             LEFT JOIN juridiction.juridiction j ON j.juridiction_id = r.juridiction_id
             GROUP BY 1 ORDER BY total DESC LIMIT 12"
        );
        $monthly = $this->monthlySeries(
            "SELECT to_char(date_trunc('month', date_recours), 'YYYY-MM') AS label, COUNT(*)::int AS total
             FROM recours.recours WHERE date_recours IS NOT NULL GROUP BY 1 ORDER BY 1"
        );
        $byVerdictType = $this->groupCount(
            "SELECT COALESCE(tv.description_type_verdict, '—') AS label, COUNT(*)::int AS total
             FROM recours.recours r
             LEFT JOIN verdict.verdict v ON v.verdict_id = r.verdict_conteste_id
             LEFT JOIN verdict.type_verdict tv ON tv.type_verdict_id = v.type_verdict_id
             GROUP BY 1 ORDER BY total DESC"
        );

        // Stacked-ish by level: reuse multi dataset via chart meta
        return $this->payload(
            lang('Backoffice.dash_apl_title'),
            lang('Backoffice.dash_apl_lead'),
            'dash-appeals',
            [
                $this->kpi('total', lang('Backoffice.dash_kpi_total_appeals'), $total, 'appeal'),
                $this->kpi('provincial', lang('Backoffice.dash_kpi_provincial_appeals'), $provincial, 'layers'),
                $this->kpi('regional', lang('Backoffice.dash_kpi_regional_appeals'), $regional, 'layers'),
                $this->kpi('ministry', lang('Backoffice.dash_kpi_ministry_appeals'), $ministry, 'building'),
                $this->kpi('accepted', lang('Backoffice.dash_kpi_appeals_accepted'), $accepted, 'check'),
                $this->kpi('rejected', lang('Backoffice.dash_kpi_appeals_rejected'), $rejected, 'x'),
                $this->kpi('pending', lang('Backoffice.dash_kpi_appeals_pending'), $pending, 'clock'),
                $this->kpi('avg', lang('Backoffice.dash_kpi_avg_appeal_days'), round($avgDays, 1), 'calendar'),
            ],
            [
                $this->chart('levelBar', lang('Backoffice.dash_chart_appeals_by_level'), 'bar', array_column($byLevel, 'label'), array_column($byLevel, 'total')),
                $this->chart('provinceMap', lang('Backoffice.dash_chart_appeals_by_province'), 'province-map', [], [], ['points' => $byProvince]),
                $this->chart('courtBar', lang('Backoffice.dash_chart_appeals_by_court'), 'bar-horizontal', array_column($byCourt, 'label'), array_column($byCourt, 'total')),
                $this->chart('outcomePie', lang('Backoffice.dash_chart_appeal_outcomes'), 'pie', [
                    lang('Backoffice.dash_outcome_accepted'),
                    lang('Backoffice.dash_outcome_rejected'),
                    lang('Backoffice.dash_outcome_pending'),
                ], [$accepted, $rejected, $pending]),
                $this->chart('monthlyLine', lang('Backoffice.dash_chart_monthly_appeals'), 'line', array_column($monthly, 'label'), array_column($monthly, 'total')),
                $this->chart('successGauge', lang('Backoffice.dash_chart_appeal_success'), 'gauge', [], [], ['value' => $successRate, 'max' => 100, 'unit' => '%']),
                $this->chart('verdictTypes', lang('Backoffice.dash_chart_appeals_verdict_types'), 'bar', array_column($byVerdictType, 'label'), array_column($byVerdictType, 'total')),
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function summons(): array
    {
        $total = (int) $this->scalar('SELECT COUNT(*)::int FROM convocation.convocation');
        $byStatus = $this->groupCount(
            "SELECT COALESCE(s.description_statut_convocation, '—') AS label, COUNT(*)::int AS total
             FROM convocation.convocation c
             LEFT JOIN convocation.statut_convocation s ON s.statut_convocation_id = c.statut_convocation_id
             GROUP BY 1 ORDER BY total DESC"
        );
        $delivered = 0;
        $pending = 0;
        $failed = 0;
        foreach ($byStatus as $row) {
            $l = mb_strtolower((string) $row['label']);
            $n = (int) $row['total'];
            if ($this->labelHas($l, ['remis', 'deliver', 'envoy', 'sent'])) {
                $delivered += $n;
            } elseif ($this->labelHas($l, ['echec', 'échec', 'fail'])) {
                $failed += $n;
            } else {
                $pending += $n;
            }
        }
        $hearingsScheduled = (int) $this->scalar('SELECT COUNT(DISTINCT date_audience)::int FROM convocation.convocation WHERE date_audience IS NOT NULL');
        $deliveryRate = $total > 0 ? round(($delivered / $total) * 100, 1) : 0.0;

        $byProvince = $this->provinceSeries(
            "SELECT province_lieu_audience_id AS id, COUNT(*)::int AS total
             FROM convocation.convocation WHERE province_lieu_audience_id IS NOT NULL GROUP BY 1"
        );
        $byCourt = $this->groupCount(
            "SELECT COALESCE(j.nom_juridiction, '—') AS label, COUNT(*)::int AS total
             FROM convocation.convocation c
             LEFT JOIN juridiction.juridiction j ON j.juridiction_id = c.juridiction_lieu_audience_id
             GROUP BY 1 ORDER BY total DESC LIMIT 12"
        );
        $monthly = $this->monthlySeries(
            "SELECT to_char(date_trunc('month', COALESCE(emise_le, created_at)), 'YYYY-MM') AS label, COUNT(*)::int AS total
             FROM convocation.convocation GROUP BY 1 ORDER BY 1"
        );
        $perHearing = $this->groupCount(
            "SELECT to_char(date_audience, 'YYYY-MM-DD') AS label, COUNT(*)::int AS total
             FROM convocation.convocation WHERE date_audience IS NOT NULL
             GROUP BY 1 ORDER BY 1 DESC LIMIT 15"
        );
        $deliveryTimes = $this->groupCount(
            "SELECT width_bucket(
                EXTRACT(EPOCH FROM (d.date_remise::timestamp - c.emise_le::timestamp)) / 86400.0,
                0, 30, 6
             )::text AS label, COUNT(*)::int AS total
             FROM convocation.convocation c
             INNER JOIN convocation.convocation_destinataire d ON d.convocation_id = c.convocation_id
             WHERE d.date_remise IS NOT NULL AND c.emise_le IS NOT NULL
             GROUP BY 1 ORDER BY 1"
        );

        return $this->payload(
            lang('Backoffice.dash_sum_title'),
            lang('Backoffice.dash_sum_lead'),
            'dash-summons',
            [
                $this->kpi('total', lang('Backoffice.dash_kpi_total_summons'), $total, 'mail'),
                $this->kpi('delivered', lang('Backoffice.dash_kpi_delivered'), $delivered, 'check'),
                $this->kpi('pending', lang('Backoffice.dash_kpi_pending_delivery'), $pending, 'clock'),
                $this->kpi('failed', lang('Backoffice.dash_kpi_failed_delivery'), $failed, 'x'),
                $this->kpi('hearings', lang('Backoffice.dash_kpi_hearings_scheduled'), $hearingsScheduled, 'calendar'),
            ],
            [
                $this->chart('statusPie', lang('Backoffice.dash_chart_summons_status'), 'pie', array_column($byStatus, 'label'), array_column($byStatus, 'total')),
                $this->chart('provinceMap', lang('Backoffice.dash_chart_summons_province'), 'province-map', [], [], ['points' => $byProvince]),
                $this->chart('courtBar', lang('Backoffice.dash_chart_summons_court'), 'bar-horizontal', array_column($byCourt, 'label'), array_column($byCourt, 'total')),
                $this->chart('deliveryHist', lang('Backoffice.dash_chart_delivery_time'), 'bar', array_column($deliveryTimes, 'label'), array_column($deliveryTimes, 'total')),
                $this->chart('monthlyLine', lang('Backoffice.dash_chart_monthly_summons'), 'line', array_column($monthly, 'label'), array_column($monthly, 'total')),
                $this->chart('perHearing', lang('Backoffice.dash_chart_summons_per_hearing'), 'bar', array_column($perHearing, 'label'), array_column($perHearing, 'total')),
                $this->chart('deliveryGauge', lang('Backoffice.dash_chart_delivery_rate'), 'gauge', [], [], ['value' => $deliveryRate, 'max' => 100, 'unit' => '%']),
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function hearings(): array
    {
        $total = (int) $this->scalar('SELECT COUNT(*)::int FROM audience.audience');
        $byStatus = $this->groupCount(
            "SELECT COALESCE(s.description_statut_audience, '—') AS label, COUNT(*)::int AS total
             FROM audience.audience a
             LEFT JOIN audience.statut_audience s ON s.statut_audience_id = a.statut_audience_id
             GROUP BY 1 ORDER BY total DESC"
        );
        $held = $postponed = $cancelled = $upcoming = $today = 0;
        foreach ($byStatus as $row) {
            $l = mb_strtolower((string) $row['label']);
            $n = (int) $row['total'];
            if ($this->labelHas($l, ['tenu', 'held', 'done', 'termine'])) {
                $held += $n;
            } elseif ($this->labelHas($l, ['report', 'postpon'])) {
                $postponed += $n;
            } elseif ($this->labelHas($l, ['annul', 'cancel'])) {
                $cancelled += $n;
            }
        }
        $upcoming = (int) $this->scalar("SELECT COUNT(*)::int FROM audience.audience WHERE date_audience > CURRENT_DATE");
        $today = (int) $this->scalar("SELECT COUNT(*)::int FROM audience.audience WHERE date_audience = CURRENT_DATE");
        $attendance = (float) $this->scalar(
            "SELECT CASE WHEN COUNT(*) = 0 THEN 0 ELSE
                100.0 * SUM(CASE WHEN COALESCE(present, false) THEN 1 ELSE 0 END)::float / COUNT(*)::float END
             FROM audience.presence_audience"
        );
        $avgDuration = (float) $this->scalar(
            "SELECT COALESCE(AVG(EXTRACT(EPOCH FROM (heure_fin - heure_debut)) / 60.0), 0)
             FROM audience.audience WHERE heure_debut IS NOT NULL AND heure_fin IS NOT NULL"
        );

        $byLevel = $this->groupCount(
            "SELECT COALESCE(n.desc_niveau_juridiction, '—') AS label, COUNT(*)::int AS total
             FROM audience.audience a
             LEFT JOIN juridiction.niveau_juridiction n ON n.niveau_juridiction_id = a.niveau_juridiction_id
             GROUP BY 1 ORDER BY total DESC"
        );
        $byProvince = $this->provinceSeries(
            "SELECT province_audience_id AS id, COUNT(*)::int AS total
             FROM audience.audience WHERE province_audience_id IS NOT NULL GROUP BY 1"
        );
        $monthly = $this->monthlySeries(
            "SELECT to_char(date_trunc('month', date_audience), 'YYYY-MM') AS label, COUNT(*)::int AS total
             FROM audience.audience WHERE date_audience IS NOT NULL GROUP BY 1 ORDER BY 1"
        );
        $perJudge = $this->groupCount(
            "SELECT TRIM(CONCAT(COALESCE(u.prenom_utilisateur,''),' ',COALESCE(u.nom_utilisateur,''))) AS label, COUNT(*)::int AS total
             FROM audience.audience a
             INNER JOIN administration.utilisateur u ON u.utilisateur_id = a.juge_id
             GROUP BY 1 ORDER BY total DESC LIMIT 10"
        );
        $perClerk = $this->groupCount(
            "SELECT TRIM(CONCAT(COALESCE(u.prenom_utilisateur,''),' ',COALESCE(u.nom_utilisateur,''))) AS label, COUNT(*)::int AS total
             FROM audience.audience a
             INNER JOIN administration.utilisateur u ON u.utilisateur_id = a.greffier_id
             GROUP BY 1 ORDER BY total DESC LIMIT 10"
        );
        $calendar = $this->queryAll(
            "SELECT date_audience::text AS date, COUNT(*)::int AS total
             FROM audience.audience
             WHERE date_audience >= date_trunc('month', CURRENT_DATE)
               AND date_audience < date_trunc('month', CURRENT_DATE) + INTERVAL '1 month'
             GROUP BY 1 ORDER BY 1"
        );

        return $this->payload(
            lang('Backoffice.dash_hrg_title'),
            lang('Backoffice.dash_hrg_lead'),
            'dash-hearings',
            [
                $this->kpi('total', lang('Backoffice.dash_kpi_total_hearings'), $total, 'calendar'),
                $this->kpi('held', lang('Backoffice.dash_kpi_hearings_held'), $held, 'check'),
                $this->kpi('postponed', lang('Backoffice.dash_kpi_hearings_postponed'), $postponed, 'clock'),
                $this->kpi('cancelled', lang('Backoffice.dash_kpi_hearings_cancelled'), $cancelled, 'x'),
                $this->kpi('upcoming', lang('Backoffice.dash_kpi_hearings_upcoming'), $upcoming, 'calendar'),
                $this->kpi('today', lang('Backoffice.dash_kpi_hearings_today'), $today, 'star'),
                $this->kpi('duration', lang('Backoffice.dash_kpi_avg_hearing_duration'), round($avgDuration, 0), 'clock'),
            ],
            [
                $this->chart('levelBar', lang('Backoffice.dash_chart_hearings_by_level'), 'bar', array_column($byLevel, 'label'), array_column($byLevel, 'total')),
                $this->chart('provinceMap', lang('Backoffice.dash_chart_hearings_province'), 'province-map', [], [], ['points' => $byProvince]),
                $this->chart('monthlyLine', lang('Backoffice.dash_chart_monthly_hearings'), 'line', array_column($monthly, 'label'), array_column($monthly, 'total')),
                $this->chart('attendanceGauge', lang('Backoffice.dash_chart_attendance_rate'), 'gauge', [], [], ['value' => round($attendance, 1), 'max' => 100, 'unit' => '%']),
                $this->chart('judgeBar', lang('Backoffice.dash_chart_hearings_per_judge'), 'bar', array_column($perJudge, 'label'), array_column($perJudge, 'total')),
                $this->chart('clerkBar', lang('Backoffice.dash_chart_hearings_per_clerk'), 'bar', array_column($perClerk, 'label'), array_column($perClerk, 'total')),
                $this->chart('outcomePie', lang('Backoffice.dash_chart_hearing_outcomes'), 'pie', array_column($byStatus, 'label'), array_column($byStatus, 'total')),
                $this->chart('calendar', lang('Backoffice.dash_chart_hearing_calendar'), 'calendar', [], [], ['events' => $calendar]),
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function notifications(): array
    {
        $person = (int) $this->scalar('SELECT COUNT(*)::int FROM notification.notification_personne');
        $user = (int) $this->scalar('SELECT COUNT(*)::int FROM notification.notification_utilisateur');
        $total = $person + $user;

        $byChannel = $this->groupCount(
            "SELECT COALESCE(c.description_canal_notification, '—') AS label, COUNT(*)::int AS total FROM (
                SELECT canal_notification_id FROM notification.notification_personne
                UNION ALL
                SELECT canal_notification_id FROM notification.notification_utilisateur
             ) n
             LEFT JOIN notification.canal_notification c ON c.canal_notification_id = n.canal_notification_id
             GROUP BY 1 ORDER BY total DESC"
        );
        $email = $sms = 0;
        foreach ($byChannel as $row) {
            $l = mb_strtolower((string) $row['label']);
            if (str_contains($l, 'mail') || str_contains($l, 'email')) {
                $email += (int) $row['total'];
            } elseif (str_contains($l, 'sms')) {
                $sms += (int) $row['total'];
            }
        }

        $byStatus = $this->groupCount(
            "SELECT COALESCE(s.description_statut_notification, '—') AS label, COUNT(*)::int AS total FROM (
                SELECT statut_notification_id FROM notification.notification_personne
                UNION ALL
                SELECT statut_notification_id FROM notification.notification_utilisateur
             ) n
             LEFT JOIN notification.statut_notification s ON s.statut_notification_id = n.statut_notification_id
             GROUP BY 1 ORDER BY total DESC"
        );
        $delivered = $failed = $read = $unread = 0;
        foreach ($byStatus as $row) {
            $l = mb_strtolower((string) $row['label']);
            $n = (int) $row['total'];
            if ($this->labelHas($l, ['lue', 'read'])) {
                $read += $n;
            } elseif ($this->labelHas($l, ['echec', 'échec', 'fail'])) {
                $failed += $n;
            } elseif ($this->labelHas($l, ['envoy', 'sent', 'deliver'])) {
                $delivered += $n;
            } else {
                $unread += $n;
            }
        }
        $readCount = (int) $this->scalar(
            "SELECT COUNT(*)::int FROM (
                SELECT 1 FROM notification.notification_personne WHERE lu_le IS NOT NULL
                UNION ALL
                SELECT 1 FROM notification.notification_utilisateur WHERE lu_le IS NOT NULL
             ) t"
        );
        $deliveryRate = $total > 0 ? round((($delivered + $read) / $total) * 100, 1) : 0.0;
        $readRate = $total > 0 ? round(($readCount / $total) * 100, 1) : 0.0;
        $avgDeliveryMin = (float) $this->scalar(
            "SELECT COALESCE(AVG(mins), 0) FROM (
                SELECT EXTRACT(EPOCH FROM (envoye_le - created_at)) / 60.0 AS mins
                FROM notification.notification_personne
                WHERE envoye_le IS NOT NULL AND created_at IS NOT NULL
                UNION ALL
                SELECT EXTRACT(EPOCH FROM (envoye_le - created_at)) / 60.0 AS mins
                FROM notification.notification_utilisateur
                WHERE envoye_le IS NOT NULL AND created_at IS NOT NULL
             ) t"
        );

        $monthly = $this->monthlySeries(
            "SELECT label, SUM(total)::int AS total FROM (
                SELECT to_char(date_trunc('month', COALESCE(envoye_le, created_at)), 'YYYY-MM') AS label, COUNT(*)::int AS total
                FROM notification.notification_personne GROUP BY 1
                UNION ALL
                SELECT to_char(date_trunc('month', COALESCE(envoye_le, created_at)), 'YYYY-MM') AS label, COUNT(*)::int AS total
                FROM notification.notification_utilisateur GROUP BY 1
             ) t GROUP BY label ORDER BY label"
        );

        // Module heuristic from subject keywords
        $modules = [
            lang('Backoffice.nav_complaints') => (int) $this->scalar("SELECT COUNT(*)::int FROM notification.notification_personne WHERE LOWER(sujet) LIKE '%plainte%' OR LOWER(sujet) LIKE '%complaint%'"),
            lang('Backoffice.nav_appeals') => (int) $this->scalar("SELECT COUNT(*)::int FROM (
                SELECT 1 FROM notification.notification_personne WHERE LOWER(sujet) LIKE '%recours%' OR LOWER(sujet) LIKE '%appeal%'
                UNION ALL SELECT 1 FROM notification.notification_utilisateur WHERE LOWER(sujet) LIKE '%recours%' OR LOWER(sujet) LIKE '%appeal%'
            ) t"),
            lang('Backoffice.nav_hearings') => (int) $this->scalar("SELECT COUNT(*)::int FROM (
                SELECT 1 FROM notification.notification_personne WHERE LOWER(sujet) LIKE '%audience%' OR LOWER(sujet) LIKE '%hearing%'
                UNION ALL SELECT 1 FROM notification.notification_utilisateur WHERE LOWER(sujet) LIKE '%audience%' OR LOWER(sujet) LIKE '%hearing%'
            ) t"),
            lang('Backoffice.nav_case_transfers') => (int) $this->scalar("SELECT COUNT(*)::int FROM (
                SELECT 1 FROM notification.notification_utilisateur WHERE LOWER(sujet) LIKE '%transfert%' OR LOWER(sujet) LIKE '%transfer%'
            ) t"),
            lang('Backoffice.nav_verdicts') => (int) $this->scalar("SELECT COUNT(*)::int FROM (
                SELECT 1 FROM notification.notification_personne WHERE LOWER(sujet) LIKE '%verdict%'
                UNION ALL SELECT 1 FROM notification.notification_utilisateur WHERE LOWER(sujet) LIKE '%verdict%'
            ) t"),
            lang('Backoffice.dash_module_auth') => (int) $this->scalar("SELECT COUNT(*)::int FROM (
                SELECT 1 FROM notification.notification_personne WHERE LOWER(sujet) LIKE '%auth%' OR LOWER(sujet) LIKE '%password%' OR LOWER(sujet) LIKE '%2fa%' OR LOWER(sujet) LIKE '%verification%'
            ) t"),
        ];

        $byProvince = $this->provinceSeries(
            "SELECT p.province_naissance_id AS id, COUNT(*)::int AS total
             FROM notification.notification_personne n
             INNER JOIN plaignant.personne p ON p.personne_id = n.personne_id
             WHERE p.province_naissance_id IS NOT NULL GROUP BY 1"
        );

        return $this->payload(
            lang('Backoffice.dash_ntf_title'),
            lang('Backoffice.dash_ntf_lead'),
            'dash-notifications',
            [
                $this->kpi('total', lang('Backoffice.dash_kpi_total_notifications'), $total, 'bell'),
                $this->kpi('email', lang('Backoffice.dash_kpi_emails_sent'), $email, 'mail'),
                $this->kpi('sms', lang('Backoffice.dash_kpi_sms_sent'), $sms, 'chat'),
                $this->kpi('delivered', lang('Backoffice.dash_kpi_ntf_delivered'), $delivered + $read, 'check'),
                $this->kpi('failed', lang('Backoffice.dash_kpi_ntf_failed'), $failed, 'x'),
                $this->kpi('read', lang('Backoffice.dash_kpi_ntf_read'), $readCount, 'eye'),
                $this->kpi('unread', lang('Backoffice.dash_kpi_ntf_unread'), max(0, $total - $readCount), 'clock'),
                $this->kpi('avg_delivery', lang('Backoffice.dash_kpi_avg_ntf_delivery'), round($avgDeliveryMin, 1), 'clock'),
            ],
            [
                $this->chart('channelPie', lang('Backoffice.dash_chart_ntf_channel'), 'pie', array_column($byChannel, 'label'), array_column($byChannel, 'total')),
                $this->chart('statusDonut', lang('Backoffice.dash_chart_ntf_status'), 'doughnut', array_column($byStatus, 'label'), array_column($byStatus, 'total')),
                $this->chart('moduleBar', lang('Backoffice.dash_chart_ntf_module'), 'bar', array_keys($modules), array_values($modules)),
                $this->chart('monthlyLine', lang('Backoffice.dash_chart_monthly_ntf'), 'line', array_column($monthly, 'label'), array_column($monthly, 'total')),
                $this->chart('deliveryGauge', lang('Backoffice.dash_chart_ntf_delivery_rate'), 'gauge', [], [], ['value' => $deliveryRate, 'max' => 100, 'unit' => '%']),
                $this->chart('readGauge', lang('Backoffice.dash_chart_ntf_read_rate'), 'gauge', [], [], ['value' => $readRate, 'max' => 100, 'unit' => '%']),
                $this->chart('provinceMap', lang('Backoffice.dash_chart_ntf_province'), 'province-map', [], [], ['points' => $byProvince]),
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function courts(): array
    {
        $total = (int) $this->scalar('SELECT COUNT(*)::int FROM juridiction.juridiction');
        $active = (int) $this->scalar('SELECT COUNT(*)::int FROM juridiction.juridiction WHERE COALESCE(is_active, true) = TRUE');
        $byLevel = $this->groupCount(
            "SELECT COALESCE(n.desc_niveau_juridiction, '—') AS label, COUNT(*)::int AS total
             FROM juridiction.juridiction j
             LEFT JOIN juridiction.niveau_juridiction n ON n.niveau_juridiction_id = j.niveau_juridiction_id
             GROUP BY 1 ORDER BY total DESC"
        );
        $communal = $provincial = $regional = $ministry = 0;
        foreach ($byLevel as $row) {
            $l = mb_strtolower((string) $row['label']);
            $n = (int) $row['total'];
            if (str_contains($l, 'commun')) {
                $communal += $n;
            } elseif (str_contains($l, 'prov')) {
                $provincial += $n;
            } elseif (str_contains($l, 'regio')) {
                $regional += $n;
            } elseif (str_contains($l, 'minist')) {
                $ministry += $n;
            }
        }

        $byProvince = $this->provinceSeries(
            "SELECT province_id AS id, COUNT(*)::int AS total FROM juridiction.juridiction WHERE province_id IS NOT NULL GROUP BY 1"
        );
        $complaintsPerCourt = $this->groupCount(
            "SELECT COALESCE(j.nom_juridiction, '—') AS label, COUNT(p.plainte_id)::int AS total
             FROM juridiction.juridiction j
             LEFT JOIN plainte.plainte p ON p.juridiction_id = j.juridiction_id
             GROUP BY 1 ORDER BY total DESC LIMIT 12"
        );
        $appealsPerCourt = $this->groupCount(
            "SELECT COALESCE(j.nom_juridiction, '—') AS label, COUNT(r.recours_id)::int AS total
             FROM juridiction.juridiction j
             LEFT JOIN recours.recours r ON r.juridiction_id = j.juridiction_id
             GROUP BY 1 ORDER BY total DESC LIMIT 12"
        );
        $hearingsPerCourt = $this->groupCount(
            "SELECT COALESCE(j.nom_juridiction, '—') AS label, COUNT(a.audience_id)::int AS total
             FROM juridiction.juridiction j
             LEFT JOIN audience.audience a ON a.juridiction_audience_id = j.juridiction_id
             GROUP BY 1 ORDER BY total DESC LIMIT 12"
        );
        $avgProcessing = $this->groupCount(
            "SELECT COALESCE(j.nom_juridiction, '—') AS label,
                    COALESCE(ROUND(AVG(EXTRACT(EPOCH FROM (v.date_verdict::timestamp - p.date_depot::timestamp)) / 86400.0)::numeric, 1), 0)::float AS total
             FROM juridiction.juridiction j
             LEFT JOIN plainte.plainte p ON p.juridiction_id = j.juridiction_id
             LEFT JOIN audience.audience_plainte ap ON ap.plainte_id = p.plainte_id
             LEFT JOIN verdict.verdict v ON v.audience_plainte_id = ap.audience_plainte_id
             GROUP BY 1 ORDER BY total DESC NULLS LAST LIMIT 12"
        );
        $judgeWorkload = $this->groupCount(
            "SELECT TRIM(CONCAT(COALESCE(u.prenom_utilisateur,''),' ',COALESCE(u.nom_utilisateur,''))) AS label, COUNT(*)::int AS total
             FROM audience.audience a
             INNER JOIN administration.utilisateur u ON u.utilisateur_id = a.juge_id
             GROUP BY 1 ORDER BY total DESC LIMIT 10"
        );
        $timeline = $this->monthlySeries(
            "SELECT to_char(date_trunc('month', created_at), 'YYYY-MM') AS label, COUNT(*)::int AS total
             FROM juridiction.juridiction WHERE created_at IS NOT NULL GROUP BY 1 ORDER BY 1"
        );

        $ranking = $this->queryAll(
            "SELECT
                j.nom_juridiction AS court,
                COUNT(DISTINCT p.plainte_id) FILTER (
                    WHERE EXISTS (
                        SELECT 1 FROM plainte.statut_plainte s
                        WHERE s.statut_plainte_id = p.statut_plainte_id
                          AND LOWER(s.description_statut_plainte) LIKE '%clotur%'
                    )
                )::int AS closed_cases,
                COALESCE(ROUND(AVG(EXTRACT(EPOCH FROM (v.date_verdict::timestamp - p.date_depot::timestamp)) / 86400.0)::numeric, 1), 0) AS avg_days,
                COUNT(DISTINCT r.recours_id)::int AS appeals,
                COUNT(DISTINCT p.plainte_id) FILTER (
                    WHERE NOT EXISTS (
                        SELECT 1 FROM plainte.statut_plainte s
                        WHERE s.statut_plainte_id = p.statut_plainte_id
                          AND LOWER(s.description_statut_plainte) LIKE '%clotur%'
                    )
                )::int AS pending_cases
             FROM juridiction.juridiction j
             LEFT JOIN plainte.plainte p ON p.juridiction_id = j.juridiction_id
             LEFT JOIN audience.audience_plainte ap ON ap.plainte_id = p.plainte_id
             LEFT JOIN verdict.verdict v ON v.audience_plainte_id = ap.audience_plainte_id
             LEFT JOIN recours.recours r ON r.juridiction_id = j.juridiction_id
             GROUP BY j.juridiction_id, j.nom_juridiction
             ORDER BY closed_cases DESC, pending_cases ASC
             LIMIT 15"
        );

        $payload = $this->payload(
            lang('Backoffice.dash_court_title'),
            lang('Backoffice.dash_court_lead'),
            'dash-court-jurisdictions',
            [
                $this->kpi('total', lang('Backoffice.dash_kpi_total_courts'), $total, 'building'),
                $this->kpi('active', lang('Backoffice.dash_kpi_active_courts'), $active, 'check'),
                $this->kpi('communal', lang('Backoffice.dash_kpi_communal_courts'), $communal, 'map'),
                $this->kpi('provincial', lang('Backoffice.dash_kpi_provincial_courts'), $provincial, 'map'),
                $this->kpi('regional', lang('Backoffice.dash_kpi_regional_courts'), $regional, 'map'),
                $this->kpi('ministry', lang('Backoffice.dash_kpi_ministry_courts'), $ministry, 'shield'),
            ],
            [
                $this->chart('levelBar', lang('Backoffice.dash_chart_courts_by_level'), 'bar', array_column($byLevel, 'label'), array_column($byLevel, 'total')),
                $this->chart('provinceMap', lang('Backoffice.dash_chart_courts_by_province'), 'province-map', [], [], ['points' => $byProvince]),
                $this->chart('cmpBar', lang('Backoffice.dash_chart_complaints_per_court'), 'bar-horizontal', array_column($complaintsPerCourt, 'label'), array_column($complaintsPerCourt, 'total')),
                $this->chart('aplBar', lang('Backoffice.dash_chart_appeals_per_court'), 'bar-horizontal', array_column($appealsPerCourt, 'label'), array_column($appealsPerCourt, 'total')),
                $this->chart('hrgBar', lang('Backoffice.dash_chart_hearings_per_court'), 'bar-horizontal', array_column($hearingsPerCourt, 'label'), array_column($hearingsPerCourt, 'total')),
                $this->chart('avgBar', lang('Backoffice.dash_chart_avg_time_by_court'), 'bar', array_column($avgProcessing, 'label'), array_column($avgProcessing, 'total')),
                $this->chart('judgeBar', lang('Backoffice.dash_chart_judge_workload'), 'bar', array_column($judgeWorkload, 'label'), array_column($judgeWorkload, 'total')),
                $this->chart('timeline', lang('Backoffice.dash_chart_court_activity'), 'line', array_column($timeline, 'label'), array_column($timeline, 'total')),
            ]
        );
        $payload['tables'][] = [
            'title'   => lang('Backoffice.dash_table_court_ranking'),
            'headers' => [
                lang('Backoffice.dash_col_court'),
                lang('Backoffice.dash_col_closed'),
                lang('Backoffice.dash_col_avg_days'),
                lang('Backoffice.dash_col_appeals'),
                lang('Backoffice.dash_col_pending'),
            ],
            'rows' => array_map(static fn (array $r): array => [
                $r['court'] ?? '—',
                (string) ($r['closed_cases'] ?? 0),
                (string) ($r['avg_days'] ?? 0),
                (string) ($r['appeals'] ?? 0),
                (string) ($r['pending_cases'] ?? 0),
            ], $ranking),
        ];

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function executive(): array
    {
        $complaints = (int) $this->scalar('SELECT COUNT(*)::int FROM plainte.plainte');
        $appeals = (int) $this->scalar('SELECT COUNT(*)::int FROM recours.recours');
        $hearings = (int) $this->scalar('SELECT COUNT(*)::int FROM audience.audience');
        $verdicts = (int) $this->scalar('SELECT COUNT(*)::int FROM verdict.verdict');
        $transfers = (int) $this->scalar('SELECT COUNT(*)::int FROM transfert.transfert_dossier');
        $notifications = (int) $this->scalar(
            'SELECT (
                (SELECT COUNT(*)::int FROM notification.notification_personne) +
                (SELECT COUNT(*)::int FROM notification.notification_utilisateur)
             )'
        );
        $complainants = (int) $this->scalar('SELECT COUNT(*)::int FROM plaignant.personne');
        $courts = (int) $this->scalar('SELECT COUNT(*)::int FROM juridiction.juridiction WHERE COALESCE(is_active, true) = TRUE');

        $map = $this->provinceSeries(
            "SELECT j.province_id AS id, COUNT(*)::int AS total
             FROM plainte.plainte p
             INNER JOIN juridiction.juridiction j ON j.juridiction_id = p.juridiction_id
             WHERE j.province_id IS NOT NULL GROUP BY 1"
        );
        $funnel = [
            lang('Backoffice.dash_funnel_registered') => $complaints,
            lang('Backoffice.dash_funnel_hearing_held') => $hearings,
            lang('Backoffice.dash_funnel_verdict') => $verdicts,
            lang('Backoffice.dash_funnel_appeal') => $appeals,
            lang('Backoffice.nav_case_transfers') => $transfers,
        ];
        $trends = $this->queryAll(
            "SELECT m.label,
                    COALESCE(c.total, 0) AS complaints,
                    COALESCE(a.total, 0) AS appeals,
                    COALESCE(h.total, 0) AS hearings,
                    COALESCE(v.total, 0) AS verdicts,
                    COALESCE(t.total, 0) AS transfers
             FROM (
                SELECT to_char(d, 'YYYY-MM') AS label FROM generate_series(
                    date_trunc('month', CURRENT_DATE) - INTERVAL '11 months',
                    date_trunc('month', CURRENT_DATE),
                    INTERVAL '1 month'
                ) d
             ) m
             LEFT JOIN (
                SELECT to_char(date_trunc('month', date_depot), 'YYYY-MM') AS label, COUNT(*)::int AS total
                FROM plainte.plainte GROUP BY 1
             ) c ON c.label = m.label
             LEFT JOIN (
                SELECT to_char(date_trunc('month', date_recours), 'YYYY-MM') AS label, COUNT(*)::int AS total
                FROM recours.recours GROUP BY 1
             ) a ON a.label = m.label
             LEFT JOIN (
                SELECT to_char(date_trunc('month', date_audience), 'YYYY-MM') AS label, COUNT(*)::int AS total
                FROM audience.audience GROUP BY 1
             ) h ON h.label = m.label
             LEFT JOIN (
                SELECT to_char(date_trunc('month', date_verdict), 'YYYY-MM') AS label, COUNT(*)::int AS total
                FROM verdict.verdict GROUP BY 1
             ) v ON v.label = m.label
             LEFT JOIN (
                SELECT to_char(date_trunc('month', date_transfert), 'YYYY-MM') AS label, COUNT(*)::int AS total
                FROM transfert.transfert_dossier GROUP BY 1
             ) t ON t.label = m.label
             ORDER BY m.label"
        );
        $topCourts = $this->groupCount(
            "SELECT COALESCE(j.nom_juridiction, '—') AS label, COUNT(p.plainte_id)::int AS total
             FROM juridiction.juridiction j
             LEFT JOIN plainte.plainte p ON p.juridiction_id = j.juridiction_id
             GROUP BY 1 ORDER BY total DESC LIMIT 10"
        );
        $topJudges = $this->groupCount(
            "SELECT TRIM(CONCAT(COALESCE(u.prenom_utilisateur,''),' ',COALESCE(u.nom_utilisateur,''))) AS label, COUNT(*)::int AS total
             FROM audience.audience a
             INNER JOIN administration.utilisateur u ON u.utilisateur_id = a.juge_id
             GROUP BY 1 ORDER BY total DESC LIMIT 10"
        );
        $avgResolution = (float) $this->scalar(
            "SELECT COALESCE(AVG(days), 0) FROM (
                SELECT MIN(EXTRACT(EPOCH FROM (v.date_verdict::timestamp - p.date_depot::timestamp)) / 86400.0) AS days
                FROM plainte.plainte p
                INNER JOIN audience.audience_plainte ap ON ap.plainte_id = p.plainte_id
                INNER JOIN verdict.verdict v ON v.audience_plainte_id = ap.audience_plainte_id
                WHERE p.date_depot IS NOT NULL
                GROUP BY p.plainte_id
             ) t"
        );
        $appealRateByLevel = $this->groupCount(
            "SELECT COALESCE(n.desc_niveau_juridiction, '—') AS label,
                    CASE WHEN COUNT(p.plainte_id) = 0 THEN 0
                         ELSE ROUND(100.0 * COUNT(r.recours_id)::numeric / COUNT(DISTINCT p.plainte_id), 1)
                    END::float AS total
             FROM juridiction.niveau_juridiction n
             LEFT JOIN plainte.plainte p ON p.niveau_juridiction_id = n.niveau_juridiction_id
             LEFT JOIN recours.recours r ON r.niveau_juridiction_id = n.niveau_juridiction_id
             GROUP BY 1 ORDER BY 1"
        );
        $hearingCompletion = (float) $this->scalar(
            "SELECT CASE WHEN COUNT(*) = 0 THEN 0 ELSE
                100.0 * SUM(CASE WHEN LOWER(COALESCE(s.description_statut_audience,'')) LIKE '%tenu%'
                                  OR LOWER(COALESCE(s.description_statut_audience,'')) LIKE '%held%' THEN 1 ELSE 0 END)::float
                / COUNT(*)::float END
             FROM audience.audience a
             LEFT JOIN audience.statut_audience s ON s.statut_audience_id = a.statut_audience_id"
        );
        $ntfSuccess = (float) $this->scalar(
            "SELECT CASE WHEN COUNT(*) = 0 THEN 0 ELSE
                100.0 * SUM(CASE WHEN LOWER(COALESCE(s.description_statut_notification,'')) LIKE '%envoy%'
                                  OR LOWER(COALESCE(s.description_statut_notification,'')) LIKE '%lue%'
                                  OR LOWER(COALESCE(s.description_statut_notification,'')) LIKE '%sent%' THEN 1 ELSE 0 END)::float
                / COUNT(*)::float END
             FROM (
                SELECT statut_notification_id FROM notification.notification_personne
                UNION ALL
                SELECT statut_notification_id FROM notification.notification_utilisateur
             ) n
             LEFT JOIN notification.statut_notification s ON s.statut_notification_id = n.statut_notification_id"
        );

        $labels = array_column($trends, 'label');

        return $this->payload(
            lang('Backoffice.dash_exec_title'),
            lang('Backoffice.dash_exec_lead'),
            'dash-executive',
            [
                $this->kpi('cmp', lang('Backoffice.dash_kpi_total_complaints'), $complaints, 'inbox'),
                $this->kpi('apl', lang('Backoffice.dash_kpi_total_appeals'), $appeals, 'appeal'),
                $this->kpi('hrg', lang('Backoffice.dash_kpi_total_hearings'), $hearings, 'calendar'),
                $this->kpi('vrd', lang('Backoffice.dash_kpi_total_verdicts'), $verdicts, 'scale'),
                $this->kpi('trf', lang('Backoffice.dash_kpi_total_transfers'), $transfers, 'transfer'),
                $this->kpi('ntf', lang('Backoffice.dash_kpi_total_notifications'), $notifications, 'bell'),
                $this->kpi('people', lang('Backoffice.dash_kpi_total_complainants'), $complainants, 'people'),
                $this->kpi('courts', lang('Backoffice.dash_kpi_active_courts'), $courts, 'building'),
            ],
            [
                $this->chart('provinceMap', lang('Backoffice.dash_chart_complaint_density'), 'province-map', [], [], ['points' => $map]),
                $this->chart('pipeline', lang('Backoffice.dash_chart_pipeline'), 'funnel', array_keys($funnel), array_values($funnel)),
                $this->chart('multiTrend', lang('Backoffice.dash_chart_national_trends'), 'multi-line', $labels, [], [
                    'datasets' => [
                        ['label' => lang('Backoffice.nav_complaints'), 'data' => array_map('intval', array_column($trends, 'complaints'))],
                        ['label' => lang('Backoffice.nav_appeals'), 'data' => array_map('intval', array_column($trends, 'appeals'))],
                        ['label' => lang('Backoffice.nav_hearings'), 'data' => array_map('intval', array_column($trends, 'hearings'))],
                        ['label' => lang('Backoffice.nav_verdicts'), 'data' => array_map('intval', array_column($trends, 'verdicts'))],
                        ['label' => lang('Backoffice.nav_case_transfers'), 'data' => array_map('intval', array_column($trends, 'transfers'))],
                    ],
                ]),
                $this->chart('topCourts', lang('Backoffice.dash_chart_top_courts'), 'bar-horizontal', array_column($topCourts, 'label'), array_column($topCourts, 'total')),
                $this->chart('topJudges', lang('Backoffice.dash_chart_top_judges'), 'bar', array_column($topJudges, 'label'), array_column($topJudges, 'total')),
                $this->chart('avgGauge', lang('Backoffice.dash_chart_avg_resolution'), 'gauge', [], [], ['value' => round($avgResolution, 1), 'max' => max(30, (int) ceil($avgResolution * 1.5) ?: 30), 'unit' => lang('Backoffice.dash_unit_days')]),
                $this->chart('appealRate', lang('Backoffice.dash_chart_appeal_rate_level'), 'bar', array_column($appealRateByLevel, 'label'), array_column($appealRateByLevel, 'total')),
                $this->chart('hearingGauge', lang('Backoffice.dash_chart_hearing_completion'), 'gauge', [], [], ['value' => round($hearingCompletion, 1), 'max' => 100, 'unit' => '%']),
                $this->chart('ntfGauge', lang('Backoffice.dash_chart_ntf_delivery_rate'), 'gauge', [], [], ['value' => round($ntfSuccess, 1), 'max' => 100, 'unit' => '%']),
                $this->chart('activityHeat', lang('Backoffice.dash_chart_judicial_heat'), 'province-map', [], [], ['points' => $map]),
            ]
        );
    }

    /**
     * @param list<array<string,mixed>> $kpis
     * @param list<array<string,mixed>> $charts
     * @return array<string, mixed>
     */
    private function payload(string $title, string $lead, string $active, array $kpis, array $charts): array
    {
        return [
            'title'  => $title,
            'lead'   => $lead,
            'active' => $active,
            'kpis'   => $kpis,
            'charts' => $charts,
            'tables' => [],
        ];
    }

    /**
     * @return array{key:string,label:string,value:int|float|string,icon:string,tone:string}
     */
    private function kpi(string $key, string $label, int|float|string $value, string $icon): array
    {
        return [
            'key'   => $key,
            'label' => $label,
            'value' => $value,
            'icon'  => $icon,
            'tone'  => 'default',
        ];
    }

    /**
     * @param list<string> $labels
     * @param list<int|float> $data
     * @param array<string,mixed> $meta
     * @return array<string, mixed>
     */
    private function chart(string $id, string $title, string $type, array $labels, array $data, array $meta = []): array
    {
        if (! empty($meta['labelMap']) && is_array($meta['labelMap'])) {
            $labels = array_map(static fn ($l) => $meta['labelMap'][$l] ?? $l, $labels);
        }

        return [
            'id'     => $id,
            'title'  => $title,
            'type'   => $type,
            'labels' => array_values($labels),
            'data'   => array_map(static fn ($v) => is_numeric($v) ? 0 + $v : 0, array_values($data)),
            'colors' => array_slice(self::PALETTE, 0, max(count($labels), 1)),
            'meta'   => $meta,
        ];
    }

    /**
     * @return list<array{label:string,total:int|float}>
     */
    private function groupCount(string $sql): array
    {
        try {
            $rows = db_connect()->query($sql)->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Dashboard groupCount failed: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static fn (array $r): array => [
            'label' => (string) ($r['label'] ?? '—'),
            'total' => is_numeric($r['total'] ?? null) ? 0 + $r['total'] : 0,
        ], $rows);
    }

    /**
     * @return list<array{label:string,total:int}>
     */
    private function monthlySeries(string $sql): array
    {
        return $this->groupCount($sql);
    }

    /**
     * @return list<array{id:int,name:string,lat:float,lng:float,value:int}>
     */
    private function provinceSeries(string $countsSql): array
    {
        $counts = [];
        try {
            foreach (db_connect()->query($countsSql)->getResultArray() as $row) {
                $counts[(int) $row['id']] = (int) $row['total'];
            }
        } catch (\Throwable $e) {
            $counts = [];
        }

        try {
            $provinces = db_connect()->query(
                "SELECT province_id, province_name, province_latitude, province_longitude
                 FROM localite.localite_province
                 WHERE COALESCE(is_active, true) = TRUE
                 ORDER BY province_name"
            )->getResultArray();
        } catch (\Throwable $e) {
            return [];
        }

        return array_map(static function (array $p) use ($counts): array {
            return [
                'id'    => (int) $p['province_id'],
                'name'  => (string) $p['province_name'],
                'lat'   => (float) ($p['province_latitude'] ?? 0),
                'lng'   => (float) ($p['province_longitude'] ?? 0),
                'value' => (int) ($counts[(int) $p['province_id']] ?? 0),
            ];
        }, $provinces);
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function queryAll(string $sql): array
    {
        try {
            return db_connect()->query($sql)->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Dashboard queryAll failed: {message}', ['message' => $e->getMessage()]);

            return [];
        }
    }

    private function scalar(string $sql): int|float|string
    {
        try {
            $row = db_connect()->query($sql)->getRowArray();
            if (! $row) {
                return 0;
            }
            $val = reset($row);

            return is_numeric($val) ? 0 + $val : (string) $val;
        } catch (\Throwable $e) {
            log_message('error', 'Dashboard scalar failed: {message}', ['message' => $e->getMessage()]);

            return 0;
        }
    }

    /**
     * @param list<string> $needles
     */
    private function labelHas(string $label, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($label, mb_strtolower($needle))) {
                return true;
            }
        }

        return false;
    }
}
