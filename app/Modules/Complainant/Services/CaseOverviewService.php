<?php

namespace Modules\Complainant\Services;

class CaseOverviewService
{
    public function user(): array
    {
        $authUser = (new \App\Libraries\ComplainantAuth())->user();
        if ($authUser !== null) {
            $nationalId = (string) ($authUser['national_id'] ?? '');

            return [
                'name'        => (string) $authUser['name'],
                'first_name'  => (string) ($authUser['first_name'] ?? ''),
                'last_name'   => (string) ($authUser['last_name'] ?? ''),
                'email'       => (string) $authUser['email'],
                'phone'       => (string) ($authUser['phone'] ?? ''),
                'national_id' => $nationalId,
                'id'          => $nationalId,
            ];
        }

        $name = (string) (session()->get('portal_user_name') ?? 'Complainant');
        $parts = preg_split('/\s+/', trim($name), 2) ?: [$name];

        return [
            'name'        => $name,
            'first_name'  => (string) ($parts[0] ?? $name),
            'last_name'   => (string) ($parts[1] ?? ''),
            'email'       => (string) (session()->get('portal_user_email') ?? ''),
            'phone'       => '',
            'national_id' => '',
            'id'          => '',
        ];
    }

    public function sampleCases(): array
    {
        return [
            [
                'id'           => 'JH-2026-0142',
                'subject'      => lang('Portal.sample_subject'),
                'court'        => 'communal',
                'court_label'  => lang('Portal.court_communal'),
                'level_label'  => lang('Portal.level_communal'),
                'court_name'   => lang('Portal.court_opt_communal_gitega'),
                'status'       => 'hearing',
                'status_label' => lang('Portal.status_hearing'),
                'updated'      => '2026-07-20',
                'hearing'      => '2026-08-05 · 09:30',
                'hearing_date' => '2026-08-05',
                'hearing_place'=> lang('Portal.court_opt_communal_gitega'),
                'magistrate'   => 'Hon. Claire Habonimana',
                'filed'        => '2026-06-12',
                'location'     => 'Gitega · Giheta · Colline Nyabihanga',
                'respondents'  => 'Eric Ndayishimiye, Jeanne Barakamfitiye',
                'summary'      => 'Dispute over the division of ancestral land parcels among heirs following the death of the family patriarch.',
                'appeal_days'  => null,
                'timeline'     => [
                    ['label' => lang('Portal.status_submitted'), 'date' => '2026-06-12', 'note' => lang('Portal.case_note_submitted'), 'done' => true],
                    ['label' => lang('Portal.status_verified'), 'date' => '2026-06-18', 'note' => lang('Portal.case_note_verified'), 'done' => true],
                    ['label' => lang('Portal.sample_activity_2'), 'date' => '2026-06-22', 'note' => lang('Portal.case_note_assigned'), 'done' => true],
                    ['label' => lang('Portal.status_hearing'), 'date' => '2026-07-20 14:30', 'note' => lang('Portal.case_note_hearing'), 'done' => true, 'current' => true],
                    ['label' => lang('Portal.status_judgment'), 'date' => null, 'note' => lang('Portal.case_note_pending'), 'done' => false],
                ],
                'documents'    => [
                    ['name' => lang('Portal.sample_doc_1'), 'type' => 'PDF', 'size' => '240 KB'],
                    ['name' => lang('Portal.sample_doc_2'), 'type' => 'JPG', 'size' => '1.1 MB'],
                    ['name' => lang('Portal.sample_doc_3'), 'type' => 'PDF', 'size' => '520 KB'],
                ],
            ],
            [
                'id'           => 'JH-2026-0120',
                'subject'      => lang('Portal.sample_subject'),
                'court'        => 'communal',
                'court_label'  => lang('Portal.court_communal'),
                'level_label'  => lang('Portal.level_communal'),
                'court_name'   => lang('Portal.court_opt_communal_giheta'),
                'status'       => 'judgment',
                'status_label' => lang('Portal.status_judgment'),
                'updated'      => '2026-07-16',
                'hearing'      => null,
                'hearing_date' => null,
                'hearing_place'=> null,
                'magistrate'   => 'Hon. Claire Habonimana',
                'filed'        => '2026-05-08',
                'location'     => 'Gitega · Giheta · Colline Ruvyagira',
                'respondents'  => 'Pierre Nsabimana',
                'summary'      => 'Communal judgment on family land inheritance ready for provincial appeal within the legal deadline.',
                'appeal_days'  => 11,
                'timeline'     => [
                    ['label' => lang('Portal.status_submitted'), 'date' => '2026-05-08', 'note' => lang('Portal.case_note_submitted'), 'done' => true],
                    ['label' => lang('Portal.status_verified'), 'date' => '2026-05-14', 'note' => lang('Portal.case_note_verified'), 'done' => true],
                    ['label' => lang('Portal.status_hearing'), 'date' => '2026-06-20', 'note' => lang('Portal.case_note_hearing'), 'done' => true],
                    ['label' => lang('Portal.status_judgment'), 'date' => '2026-07-16', 'note' => lang('Portal.case_note_communal_judgment'), 'done' => true, 'current' => true],
                ],
                'documents'    => [
                    ['name' => lang('Portal.sample_doc_1'), 'type' => 'PDF', 'size' => '190 KB'],
                    ['name' => lang('Portal.sample_doc_2'), 'type' => 'PDF', 'size' => '640 KB'],
                ],
            ],
            [
                'id'           => 'JH-2026-0098',
                'subject'      => lang('Portal.sample_subject_appeal'),
                'court'        => 'provincial',
                'court_label'  => lang('Portal.court_provincial'),
                'level_label'  => lang('Portal.level_provincial'),
                'court_name'   => lang('Portal.court_opt_provincial_gitega'),
                'status'       => 'hearing',
                'status_label' => lang('Portal.status_hearing'),
                'updated'      => '2026-07-22',
                'hearing'      => '2026-08-12 · 10:00',
                'hearing_date' => '2026-08-12',
                'hearing_place'=> lang('Portal.court_opt_provincial_gitega'),
                'magistrate'   => 'Hon. Pacifique Nkurunziza',
                'filed'        => '2026-05-28',
                'location'     => 'Gitega · Makebuko · Colline Gasenyi',
                'respondents'  => 'Josephine Hakizimana',
                'summary'      => 'Provincial appeal concerning the allocation of two inheritance land parcels after communal judgment.',
                'appeal_days'  => null,
                'timeline'     => [
                    ['label' => lang('Portal.status_submitted'), 'date' => '2026-03-02', 'note' => lang('Portal.case_note_submitted'), 'done' => true],
                    ['label' => lang('Portal.status_judgment'), 'date' => '2026-05-14', 'note' => lang('Portal.case_note_communal_judgment'), 'done' => true],
                    ['label' => lang('Portal.activity_prov_submitted'), 'date' => '2026-05-28', 'note' => lang('Portal.case_note_appeal_filed'), 'done' => true],
                    ['label' => lang('Portal.status_hearing'), 'date' => '2026-07-22 10:00', 'note' => lang('Portal.case_note_hearing'), 'done' => true, 'current' => true],
                    ['label' => lang('Portal.status_judgment'), 'date' => null, 'note' => lang('Portal.case_note_pending'), 'done' => false],
                ],
                'documents'    => [
                    ['name' => lang('Portal.sample_doc_1'), 'type' => 'PDF', 'size' => '210 KB'],
                    ['name' => lang('Portal.sample_doc_2'), 'type' => 'PDF', 'size' => '880 KB'],
                ],
            ],
            [
                'id'           => 'JH-2026-0075',
                'subject'      => lang('Portal.sample_subject_regional'),
                'court'        => 'regional',
                'court_label'  => lang('Portal.court_regional'),
                'level_label'  => lang('Portal.level_regional'),
                'court_name'   => lang('Portal.court_opt_regional'),
                'status'       => 'verified',
                'status_label' => lang('Portal.status_verified'),
                'updated'      => '2026-07-18',
                'hearing'      => null,
                'hearing_date' => null,
                'hearing_place'=> null,
                'magistrate'   => 'Hon. Esperance Niyonkuru',
                'filed'        => '2026-07-18',
                'location'     => 'Gitega · Makebuko · Colline Gasenyi',
                'respondents'  => 'Josephine Hakizimana',
                'summary'      => 'Regional appeal seeking final review of the provincial decision on inheritance land allocation.',
                'appeal_days'  => null,
                'timeline'     => [
                    ['label' => lang('Portal.status_submitted'), 'date' => '2026-01-10', 'note' => lang('Portal.case_note_submitted'), 'done' => true],
                    ['label' => lang('Portal.activity_prov_submitted'), 'date' => '2026-04-02', 'note' => lang('Portal.case_note_appeal_filed'), 'done' => true],
                    ['label' => lang('Portal.status_judgment'), 'date' => '2026-07-01', 'note' => lang('Portal.case_note_provincial_judgment'), 'done' => true],
                    ['label' => lang('Portal.activity_reg_submitted'), 'date' => '2026-07-18 11:15', 'note' => lang('Portal.case_note_regional_filed'), 'done' => true, 'current' => true],
                    ['label' => lang('Portal.status_hearing'), 'date' => null, 'note' => lang('Portal.case_note_pending'), 'done' => false],
                ],
                'documents'    => [
                    ['name' => lang('Portal.sample_doc_1'), 'type' => 'PDF', 'size' => '260 KB'],
                    ['name' => lang('Portal.sample_doc_2'), 'type' => 'PDF', 'size' => '910 KB'],
                ],
            ],
        ];
    }

    /**
     * @param list<array<string, mixed>> $cases
     * @return array{total:int,communal:int,provincial:int,regional:int,resolved:int,pending:int}
     */
    public function dashboardStats(array $cases): array
    {
        $stats = [
            'total'      => count($cases),
            'communal'   => 0,
            'provincial' => 0,
            'regional'   => 0,
            'resolved'   => 0,
            'pending'    => 0,
        ];

        foreach ($cases as $case) {
            $court = (string) ($case['court'] ?? 'communal');
            if (isset($stats[$court])) {
                $stats[$court]++;
            }

            if (in_array($case['status'], ['judgment', 'closed'], true)) {
                $stats['resolved']++;
            } else {
                $stats['pending']++;
            }
        }

        return $stats;
    }

    /**
     * @param list<array<string, mixed>> $cases
     * @return list<array<string, mixed>>
     */
    public function recentActivities(array $cases): array
    {
        $items = [];
        foreach ($cases as $case) {
            foreach ($case['timeline'] ?? [] as $step) {
                if (empty($step['date'])) {
                    continue;
                }

                $isCurrent = ! empty($step['current']);
                $isDone    = ! empty($step['done']);

                // Recent Activities shows actions that already happened or are in progress.
                if (! $isCurrent && ! $isDone) {
                    continue;
                }

                $parsed = $this->parseActivityDateTime(
                    (string) $step['date'],
                    (string) ($step['time'] ?? '')
                );
                $icon = $this->activityIcon(
                    (string) ($step['label'] ?? ''),
                    (string) ($case['court'] ?? 'communal')
                );

                if ($isCurrent) {
                    $statusKey   = 'current';
                    $statusClass = 'is-review';
                } else {
                    $statusKey   = 'done';
                    $statusClass = 'is-resolved';
                }

                $items[] = [
                    'title'         => $step['label'],
                    'description'   => $step['note'] ?? '',
                    'date'          => $parsed['date'],
                    'time'          => $parsed['time'],
                    'datetime'      => $parsed['datetime'],
                    'sort'          => $parsed['sort'],
                    'ref'           => $case['id'],
                    'court'         => $case['court'],
                    'icon'          => $icon,
                    'status'        => $statusKey,
                    'status_class'  => $statusClass,
                    'status_label'  => match ($statusKey) {
                        'current' => lang('Portal.activity_status_current'),
                        'done'    => lang('Portal.activity_status_done'),
                        default   => lang('Portal.activity_status_upcoming'),
                    },
                    'text' => $step['label'],
                    'note' => $step['note'] ?? '',
                ];
            }
        }

        usort($items, static function (array $a, array $b): int {
            $cmp = strcmp((string) $b['sort'], (string) $a['sort']);

            return $cmp !== 0 ? $cmp : strcmp((string) $b['ref'], (string) $a['ref']);
        });

        return array_slice($items, 0, 10);
    }

    /**
     * @return array{date:string,time:string,datetime:string,sort:string}
     */
    private function parseActivityDateTime(string $dateRaw, string $timeOverride = ''): array
    {
        $date = $dateRaw;
        $time = '09:00';

        if (preg_match('/^(\d{4}-\d{2}-\d{2})\s+(\d{1,2}:\d{2})/', $dateRaw, $matches)) {
            $date = $matches[1];
            $time = $matches[2];
        } elseif ($timeOverride !== '') {
            $time = $timeOverride;
        }

        if (preg_match('/^(\d{1,2}):(\d{2})/', $time, $timeParts)) {
            $time = sprintf('%02d:%02d', (int) $timeParts[1], (int) $timeParts[2]);
        }

        return [
            'date'     => $date,
            'time'     => $time,
            'datetime' => $date . 'T' . $time . ':00',
            'sort'     => $date . ' ' . $time,
        ];
    }

    private function activityIcon(string $label, string $court): string
    {
        $hay = mb_strtolower($label);

        if (str_contains($hay, 'hearing') || str_contains($hay, 'audience')) {
            return 'hearing';
        }
        if (str_contains($hay, 'judgment') || str_contains($hay, 'jugement') || str_contains($hay, 'decision')) {
            return 'judgment';
        }
        if (str_contains($hay, 'regional') || str_contains($hay, 'régional') || str_contains($hay, 'regional')) {
            return 'regional';
        }
        if (str_contains($hay, 'provincial') || str_contains($hay, 'appeal') || str_contains($hay, 'appel')) {
            return 'appeal';
        }
        if (str_contains($hay, 'verified') || str_contains($hay, 'approuv') || str_contains($hay, 'vérif')) {
            return 'verified';
        }
        if (str_contains($hay, 'assigned') || str_contains($hay, 'assign')) {
            return 'assigned';
        }
        if (str_contains($hay, 'submitted') || str_contains($hay, 'soumis') || str_contains($hay, 'dépos')) {
            return 'submitted';
        }

        return match ($court) {
            'provincial' => 'appeal',
            'regional'   => 'regional',
            default      => 'submitted',
        };
    }

    public function findCase(string $id): ?array
    {
        foreach ($this->sampleCases() as $case) {
            if ($case['id'] === $id) {
                return $case;
            }
        }

        return null;
    }

    public function sampleLocations(): array
    {
        return [
            'Gitega' => [
                'Giheta' => [
                    'Zone Giheta' => ['Nyabihanga', 'Mubuga', 'Rwingoma'],
                    'Zone Mugera' => ['Mugera', 'Gasenyi', 'Kirundo'],
                ],
                'Makebuko' => [
                    'Zone Makebuko' => ['Gasenyi', 'Nyamugari', 'Rutoke'],
                    'Zone Bukirasazi' => ['Bukirasazi', 'Nyabikere'],
                ],
                'Gitega' => [
                    'Zone Urbaine' => ['Nyamugari', 'Musinzira', 'Bwoga'],
                    'Zone Rurengera' => ['Rurengera', 'Kibimba'],
                ],
            ],
            'Bujumbura Mairie' => [
                'Mukaza' => [
                    'Zone Rohero' => ['Rohero I', 'Rohero II', 'Kabondo'],
                    'Zone Buyenzi' => ['Buyenzi I', 'Buyenzi II'],
                ],
                'Ntahangwa' => [
                    'Zone Cibitoke' => ['Cibitoke', 'Kinama'],
                    'Zone Kamenge' => ['Kamenge Nord', 'Kamenge Sud'],
                ],
            ],
            'Ngozi' => [
                'Ngozi' => [
                    'Zone Urbaine' => ['Kiganda', 'Gatabo'],
                    'Zone Busiga' => ['Busiga', 'Mwumba'],
                ],
                'Kayanza' => [
                    'Zone Kayanza' => ['Gatabo', 'Rango'],
                ],
            ],
        ];
    }
}
