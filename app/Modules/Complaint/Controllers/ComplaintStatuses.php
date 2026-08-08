<?php

namespace Modules\Complaint\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Modules\Complaint\Models\StatutPlainteModel;
use Modules\Complaint\Services\ComplaintStatusService;
use Modules\CourtJurisdiction\Models\NiveauJuridictionModel;

class ComplaintStatuses extends \App\Controllers\BaseController
{
    private ComplaintStatusService $service;

    public function __construct()
    {
        $this->service = new ComplaintStatusService();
    }

    public function index()
    {
        $status   = $this->request->getGet('status');
        $niveauId = $this->request->getGet('niveau_juridiction_id');
        $isActive = null;
        if ($status === '1' || $status === 'true') {
            $isActive = true;
        } elseif ($status === '0' || $status === 'false') {
            $isActive = false;
        }
        $niveau = ($niveauId !== null && $niveauId !== '') ? (int) $niveauId : null;

        return view('Modules\Complaint\Views\statuses\index', [
            'title'   => lang('Backoffice.cst_title'),
            'active'  => 'complaint-statuses',
            'items'   => $this->service->list($niveau && $niveau > 0 ? $niveau : null, $isActive),
            'filters' => [
                'status'                => $status,
                'niveau_juridiction_id' => $niveauId,
            ],
            'levels' => (new NiveauJuridictionModel())->options(),
            'status' => $status,
            'user'   => [
                'name' => lang('Backoffice.user_sample'),
                'role' => lang('Backoffice.role_sample'),
            ],
        ]);
    }

    public function store()
    {
        $result = $this->service->create($this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.cst_err_save')]);
        }

        return redirect()->to(site_url('backoffice/complaint-statuses'))->with('success', lang('Backoffice.cst_created'));
    }

    public function update(int $id)
    {
        $result = $this->service->update($id, $this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.cst_err_save')]);
        }

        return redirect()->to(site_url('backoffice/complaint-statuses'))->with('success', lang('Backoffice.cst_updated'));
    }

    public function toggleStatus(int $id)
    {
        $result = $this->service->toggleStatus($id);
        if (! ($result['ok'] ?? false)) {
            return redirect()->to(site_url('backoffice/complaint-statuses'))->with('error', $result['errors'][0] ?? lang('Backoffice.cst_err_save'));
        }

        return redirect()->to(site_url('backoffice/complaint-statuses'))->with(
            'success',
            ($result['activated'] ?? false) ? lang('Backoffice.cst_activated') : lang('Backoffice.cst_deactivated')
        );
    }

    public function optionsJson(): ResponseInterface
    {
        $niveauId = (int) ($this->request->getGet('niveau_juridiction_id') ?? 0);
        $active   = $this->request->getGet('active');
        $activeOnly = ! ($active === '0' || $active === 'false');

        $options = (new StatutPlainteModel())->options($niveauId > 0 ? $niveauId : null, $activeOnly);

        return $this->response->setJSON([
            'ok'      => true,
            'options' => $options,
        ]);
    }
}
