<?php

namespace Modules\Verdicts\Controllers;

use Modules\CourtJurisdiction\Models\CommuneModel;
use Modules\CourtJurisdiction\Models\JuridictionModel;
use Modules\CourtJurisdiction\Models\NiveauJuridictionModel;
use Modules\CourtJurisdiction\Models\ProvinceModel;
use Modules\Hearings\Models\StatutAudienceModel;
use Modules\Verdicts\Models\TypeVerdictModel;
use Modules\Verdicts\Services\BackofficeVerdictService;

class Verdicts extends \App\Controllers\BaseController
{
    private BackofficeVerdictService $service;

    public function __construct()
    {
        $this->service = new BackofficeVerdictService();
    }

    public function index()
    {
        $filters = [
            'niveau_juridiction_id' => $this->request->getGet('niveau_juridiction_id'),
            'province_id'           => $this->request->getGet('province_id'),
            'commune_id'            => $this->request->getGet('commune_id'),
            'juridiction_id'        => $this->request->getGet('juridiction_id'),
            'date_verdict'          => $this->request->getGet('date_verdict'),
            'type_verdict_id'       => $this->request->getGet('type_verdict_id'),
            'statut_audience_id'    => $this->request->getGet('statut_audience_id'),
        ];

        $provinceId = (int) ($filters['province_id'] ?? 0);
        $niveauId   = (int) ($filters['niveau_juridiction_id'] ?? 0);

        return view('Modules\Verdicts\Views\verdicts\index', [
            'title'         => lang('Backoffice.vrd_title'),
            'active'        => 'verdicts-list',
            'items'         => $this->service->list($filters),
            'filters'       => $filters,
            'levels'        => (new NiveauJuridictionModel())->options(),
            'provinces'     => (new ProvinceModel())->options(),
            'communes'      => $provinceId ? (new CommuneModel())->optionsByProvince($provinceId) : [],
            'jurisdictions' => (new JuridictionModel())->options([
                'niveau_juridiction_id' => $niveauId ?: null,
                'province_id'           => $provinceId ?: null,
                'commune_id'            => ! empty($filters['commune_id']) ? (int) $filters['commune_id'] : null,
            ]),
            'types'          => (new TypeVerdictModel())->options(),
            'hearingStatuses' => (new StatutAudienceModel())->options(),
            'user'           => $this->sampleUser(),
        ]);
    }

    public function create()
    {
        $provinceId    = (int) (old('province_id') ?: 0);
        $communeId     = (int) (old('commune_id') ?: 0);
        $niveauId      = (int) (old('niveau_juridiction_id') ?: 0);
        $juridictionId = (int) (old('juridiction_id') ?: 0);
        $apId          = (int) (old('audience_plainte_id') ?: 0);

        $eligible = $this->service->eligibleAudiencePlainteOptions($juridictionId ?: null, $niveauId ?: null);
        $audienceId = 0;
        $hearingDate = null;
        foreach ($eligible as $opt) {
            if ((int) $opt['id'] === $apId) {
                $audienceId  = (int) $opt['audience_id'];
                $hearingDate = $opt['hearing_date'];
                break;
            }
        }

        $verdictDate = old('date_verdict') ?: date('Y-m-d');
        $deadline    = old('date_limite_recours') ?: $this->service->defaultAppealDeadline((string) $verdictDate);

        return view('Modules\Verdicts\Views\verdicts\form', [
            'title'         => lang('Backoffice.vrd_create_title'),
            'active'        => 'verdicts-list',
            'record'        => [
                'niveau_juridiction_id' => old('niveau_juridiction_id'),
                'province_id'           => old('province_id'),
                'commune_id'            => old('commune_id'),
                'juridiction_id'        => old('juridiction_id'),
                'audience_plainte_id'   => old('audience_plainte_id'),
                'type_verdict_id'       => old('type_verdict_id'),
                'resume'                => old('resume'),
                'dispositif'            => old('dispositif'),
                'date_verdict'          => $verdictDate,
                'date_limite_recours'   => $deadline,
                'judge_ids'             => old('judge_ids') ?: [],
            ],
            'levels'        => (new NiveauJuridictionModel())->options(),
            'provinces'     => (new ProvinceModel())->options(),
            'communes'      => $provinceId ? (new CommuneModel())->optionsByProvince($provinceId) : [],
            'jurisdictions' => (new JuridictionModel())->options([
                'niveau_juridiction_id' => $niveauId ?: null,
                'province_id'           => $provinceId ?: null,
                'commune_id'            => $communeId ?: null,
            ]),
            'types'         => (new TypeVerdictModel())->options(),
            'hearings'      => $eligible,
            'judges'        => $this->service->hearingJudgeOptions($audienceId),
            'hearingDate'   => $hearingDate,
            'deadlineDays'  => \Modules\Verdicts\Models\VerdictModel::APPEAL_DEADLINE_DAYS,
            'user'          => $this->sampleUser(),
        ]);
    }

    public function store()
    {
        $file = $this->request->getFile('upload_rapport_verdict');
        $result = $this->service->create($this->request->getPost(), $file);
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.vrd_err_save')]);
        }

        return redirect()->to(site_url('backoffice/verdicts'))->with('success', lang('Backoffice.vrd_created'));
    }

    public function show(int $id)
    {
        $details = $this->service->details($id);
        if (! $details) {
            return redirect()->to(site_url('backoffice/verdicts'))->with('error', lang('Backoffice.vrd_err_not_found'));
        }

        return view('Modules\Verdicts\Views\verdicts\show', array_merge($details, [
            'title'  => lang('Backoffice.vrd_details_title'),
            'active' => 'verdicts-list',
            'user'   => $this->sampleUser(),
        ]));
    }

    public function eligibleHearings()
    {
        $juridictionId = (int) ($this->request->getGet('juridiction_id') ?? 0);
        $niveauId      = (int) ($this->request->getGet('niveau_juridiction_id') ?? 0);

        return $this->response->setJSON([
            'ok'      => true,
            'options' => $this->service->eligibleAudiencePlainteOptions($juridictionId ?: null, $niveauId ?: null),
        ]);
    }

    public function hearingJudges()
    {
        $audiencePlainteId = (int) ($this->request->getGet('audience_plainte_id') ?? 0);
        $audienceId = 0;
        $hearingDate = null;
        foreach ($this->service->eligibleAudiencePlainteOptions() as $opt) {
            if ((int) $opt['id'] === $audiencePlainteId) {
                $audienceId  = (int) $opt['audience_id'];
                $hearingDate = $opt['hearing_date'];
                break;
            }
        }

        return $this->response->setJSON([
            'ok'           => true,
            'audience_id'  => $audienceId,
            'hearing_date' => $hearingDate,
            'options'      => $this->service->hearingJudgeOptions($audienceId),
            'deadline'     => $hearingDate ? null : null,
        ]);
    }

    public function defaultDeadline()
    {
        $date = (string) ($this->request->getGet('date_verdict') ?? date('Y-m-d'));

        return $this->response->setJSON([
            'ok'       => true,
            'deadline' => $this->service->defaultAppealDeadline($date),
            'days'     => \Modules\Verdicts\Models\VerdictModel::APPEAL_DEADLINE_DAYS,
        ]);
    }

    public function viewReport(int $id)
    {
        return $this->serveReport($id, false);
    }

    public function downloadReport(int $id)
    {
        return $this->serveReport($id, true);
    }

    private function serveReport(int $id, bool $download)
    {
        $details = $this->service->details($id);
        if (! $details) {
            return redirect()->back()->with('error', lang('Backoffice.vrd_err_not_found'));
        }

        $relative = ltrim(str_replace('\\', '/', (string) ($details['record']['upload_rapport_verdict'] ?? '')), '/');
        if ($relative === '' || str_contains($relative, '..') || ! str_starts_with($relative, 'uploads/verdicts/')) {
            return redirect()->back()->with('error', lang('Backoffice.vrd_err_report_missing'));
        }

        $absolute = WRITEPATH . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        if (! is_file($absolute)) {
            return redirect()->back()->with('error', lang('Backoffice.vrd_err_report_missing'));
        }

        $name = basename($absolute);
        if ($download) {
            return $this->response->download($absolute, null)->setFileName($name);
        }

        $mime = mime_content_type($absolute) ?: 'application/octet-stream';

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . $name . '"')
            ->setBody((string) file_get_contents($absolute));
    }

    /**
     * @return array{name:string,role:string}
     */
    private function sampleUser(): array
    {
        return [
            'name' => lang('Backoffice.user_sample'),
            'role' => lang('Backoffice.role_sample'),
        ];
    }
}
