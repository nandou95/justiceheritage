<?php

namespace Modules\AuditLog\Controllers;

use App\Libraries\BackofficeAccess;
use Modules\AuditLog\Services\AuditLogListService;
use Modules\CourtJurisdiction\Models\CommuneModel;
use Modules\CourtJurisdiction\Models\ProvinceModel;

class ComplainantLogs extends \App\Controllers\BaseController
{
    private AuditLogListService $logs;

    public function __construct()
    {
        $this->logs = new AuditLogListService();
    }

    public function index()
    {
        if (! BackofficeAccess::can('backoffice/system-logs/complainants')) {
            return BackofficeAccess::denyRedirect();
        }

        $filters = [
            'province_id' => $this->request->getGet('province_id'),
            'commune_id'  => $this->request->getGet('commune_id'),
            'personne_id' => $this->request->getGet('personne_id'),
            'action'      => $this->request->getGet('action'),
            'table_cible' => $this->request->getGet('table_cible'),
            'date_from'   => $this->request->getGet('date_from'),
            'date_to'     => $this->request->getGet('date_to'),
        ];

        $provinceId = (int) ($filters['province_id'] ?? 0);

        return view('Modules\AuditLog\Views\complainant_logs\index', [
            'title'        => lang('Backoffice.logs_complainant_title'),
            'active'       => 'logs-complainants',
            'items'        => $this->logs->listComplainantLogs($filters),
            'filters'      => $filters,
            'provinces'    => (new ProvinceModel())->options(),
            'communes'     => $provinceId ? (new CommuneModel())->optionsByProvince($provinceId) : [],
            'complainants' => $this->logs->complainantOptions(),
            'actions'      => $this->logs->distinctActions('personne'),
            'tables'       => $this->logs->distinctTables('personne'),
            'user'         => $this->sampleUser(),
        ]);
    }

    public function show(int $id)
    {
        if (! BackofficeAccess::can('backoffice/system-logs/complainants')) {
            return BackofficeAccess::denyRedirect();
        }

        $record = $this->logs->findComplainantLog($id);
        if (! $record) {
            return redirect()
                ->to(site_url('backoffice/system-logs/complainants'))
                ->with('error', lang('Backoffice.logs_err_not_found'));
        }

        return view('Modules\AuditLog\Views\complainant_logs\show', [
            'title'  => lang('Backoffice.logs_complainant_details_title'),
            'active' => 'logs-complainants',
            'record' => $record,
            'user'   => $this->sampleUser(),
        ]);
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
