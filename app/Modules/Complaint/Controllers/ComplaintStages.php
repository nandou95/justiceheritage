<?php

namespace Modules\Complaint\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Modules\Administration\Models\ProfilModel;
use Modules\Complaint\Services\ComplaintStageService;
use Modules\CourtJurisdiction\Models\NiveauJuridictionModel;

class ComplaintStages extends \App\Controllers\BaseController
{
    private ComplaintStageService $service;

    public function __construct()
    {
        $this->service = new ComplaintStageService();
    }

    public function index()
    {
        $status   = $this->request->getGet('status');
        $isActive = $this->parseStatus($status);

        return view('Modules\Complaint\Views\stages\index', [
            'title'  => lang('Backoffice.cs_title'),
            'active' => 'complaint-stages',
            'items'  => $this->service->list($isActive),
            'status' => $status,
            'user'   => $this->sampleUser(),
        ]);
    }

    public function create()
    {
        return view('Modules\Complaint\Views\stages\form', [
            'title'    => lang('Backoffice.cs_create_title'),
            'active'   => 'complaint-stages',
            'mode'     => 'create',
            'record'   => $this->emptyRecord(),
            'levels'   => (new NiveauJuridictionModel())->options(),
            'profiles' => (new ProfilModel())->options(),
            'user'     => $this->sampleUser(),
        ]);
    }

    public function store()
    {
        $result = $this->service->create($this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.cs_err_save')]);
        }

        return redirect()->to(site_url('backoffice/complaint-stages'))->with('success', lang('Backoffice.cs_created'));
    }

    public function edit(int $id)
    {
        $record = $this->service->find($id);
        if (! $record) {
            return redirect()->to(site_url('backoffice/complaint-stages'))->with('error', lang('Backoffice.cs_err_not_found'));
        }

        $assignedIds = array_map('intval', (array) ($record['profil_ids'] ?? []));

        return view('Modules\Complaint\Views\stages\form', [
            'title'    => lang('Backoffice.cs_edit_title'),
            'active'   => 'complaint-stages',
            'mode'     => 'edit',
            'record'   => $record,
            'levels'   => (new NiveauJuridictionModel())->options(),
            'profiles' => (new ProfilModel())->options($assignedIds),
            'user'     => $this->sampleUser(),
        ]);
    }

    public function update(int $id)
    {
        $result = $this->service->update($id, $this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.cs_err_save')]);
        }

        return redirect()->to(site_url('backoffice/complaint-stages'))->with('success', lang('Backoffice.cs_updated'));
    }

    public function toggleStatus(int $id)
    {
        $result = $this->service->toggleStatus($id);
        if (! ($result['ok'] ?? false)) {
            return redirect()->to(site_url('backoffice/complaint-stages'))->with('error', $result['errors'][0] ?? lang('Backoffice.cs_err_save'));
        }

        return redirect()->to(site_url('backoffice/complaint-stages'))->with(
            'success',
            ($result['activated'] ?? false) ? lang('Backoffice.cs_activated') : lang('Backoffice.cs_deactivated')
        );
    }

    public function profiles(int $id): ResponseInterface
    {
        return $this->response->setJSON([
            'ok'       => true,
            'profiles' => $this->service->profiles($id),
        ]);
    }

    public function actionsJson(int $id): ResponseInterface
    {
        return $this->response->setJSON([
            'ok'      => true,
            'actions' => $this->service->actions($id),
        ]);
    }

    public function actions(int $id)
    {
        $record = $this->service->find($id);
        if (! $record) {
            return redirect()->to(site_url('backoffice/complaint-stages'))->with('error', lang('Backoffice.cs_err_not_found'));
        }

        return view('Modules\Complaint\Views\stages\actions', [
            'title'   => lang('Backoffice.cs_actions_manage_title'),
            'active'  => 'complaint-stages',
            'record'  => $record,
            'actions' => $this->service->actions($id),
            'user'    => $this->sampleUser(),
        ]);
    }

    public function storeAction(int $id)
    {
        $result = $this->service->createAction($id, $this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()
                ->to(site_url('backoffice/complaint-stages/' . $id . '/actions'))
                ->withInput()
                ->with('errors', $result['errors'] ?? [lang('Backoffice.cs_action_err_save')]);
        }

        return redirect()
            ->to(site_url('backoffice/complaint-stages/' . $id . '/actions'))
            ->with('success', lang('Backoffice.cs_action_created'));
    }

    public function toggleAction(int $id, int $actionId)
    {
        $result = $this->service->toggleAction($id, $actionId);
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->with('error', $result['errors'][0] ?? lang('Backoffice.cs_action_err_save'));
        }

        return redirect()->back()->with(
            'success',
            ($result['activated'] ?? false)
                ? lang('Backoffice.cs_action_activated')
                : lang('Backoffice.cs_action_deactivated')
        );
    }

    private function parseStatus(?string $status): ?bool
    {
        if ($status === '1' || $status === 'true') {
            return true;
        }
        if ($status === '0' || $status === 'false') {
            return false;
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyRecord(): array
    {
        return [
            'etape_plainte_id'          => null,
            'description_etape_plainte' => old('description_etape_plainte'),
            'niveau_juridiction_id'     => old('niveau_juridiction_id'),
            'is_convocation'            => old('is_convocation'),
            'is_audience'               => old('is_audience'),
            'profil_ids'                => old('profil_ids') ?: [],
        ];
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
