<?php

namespace Modules\Complaint\Controllers;

use Modules\Complaint\Services\DocumentTypeService;
use Modules\CourtJurisdiction\Models\NiveauJuridictionModel;

class DocumentTypes extends \App\Controllers\BaseController
{
    private DocumentTypeService $service;

    public function __construct()
    {
        $this->service = new DocumentTypeService();
    }

    public function index()
    {
        $niveauId = $this->request->getGet('niveau_juridiction_id');
        $status   = $this->request->getGet('status');
        $isActive = null;
        if ($status === '1' || $status === 'true') {
            $isActive = true;
        } elseif ($status === '0' || $status === 'false') {
            $isActive = false;
        }

        return view('Modules\Complaint\Views\document_types\index', [
            'title'   => lang('Backoffice.dt_title'),
            'active'  => 'document-types',
            'items'   => $this->service->list($niveauId ? (int) $niveauId : null, $isActive),
            'filters' => [
                'niveau_juridiction_id' => $niveauId,
                'status'                => $status,
            ],
            'levels' => (new NiveauJuridictionModel())->options(),
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
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.dt_err_save')]);
        }

        return redirect()->to(site_url('backoffice/document-types'))->with('success', lang('Backoffice.dt_created'));
    }

    public function update(int $id)
    {
        $result = $this->service->update($id, $this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.dt_err_save')]);
        }

        return redirect()->to(site_url('backoffice/document-types'))->with('success', lang('Backoffice.dt_updated'));
    }

    public function toggleStatus(int $id)
    {
        $result = $this->service->toggleStatus($id);
        if (! ($result['ok'] ?? false)) {
            return redirect()->to(site_url('backoffice/document-types'))->with('error', $result['errors'][0] ?? lang('Backoffice.dt_err_save'));
        }

        return redirect()->to(site_url('backoffice/document-types'))->with(
            'success',
            ($result['activated'] ?? false) ? lang('Backoffice.dt_activated') : lang('Backoffice.dt_deactivated')
        );
    }
}
