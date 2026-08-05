<?php

namespace Modules\AuditLog\Controllers;

use App\Libraries\BackofficeAccess;
use Modules\Administration\Models\JuridictionModel;
use Modules\Administration\Models\NiveauJuridictionModel;
use Modules\Administration\Models\ProfilModel;
use Modules\AuditLog\Services\AuditLogListService;
use Modules\CourtJurisdiction\Models\CommuneModel;
use Modules\CourtJurisdiction\Models\ProvinceModel;

class UserLogs extends \App\Controllers\BaseController
{
    private AuditLogListService $logs;

    public function __construct()
    {
        $this->logs = new AuditLogListService();
    }

    public function index()
    {
        if (! BackofficeAccess::can('backoffice/system-logs/users')) {
            return BackofficeAccess::denyRedirect();
        }

        $filters = [
            'province_id'           => $this->request->getGet('province_id'),
            'commune_id'            => $this->request->getGet('commune_id'),
            'niveau_juridiction_id' => $this->request->getGet('niveau_juridiction_id'),
            'juridiction_id'        => $this->request->getGet('juridiction_id'),
            'utilisateur_id'        => $this->request->getGet('utilisateur_id'),
            'profil_id'             => $this->request->getGet('profil_id'),
            'action'                => $this->request->getGet('action'),
            'table_cible'           => $this->request->getGet('table_cible'),
            'date_from'             => $this->request->getGet('date_from'),
            'date_to'               => $this->request->getGet('date_to'),
        ];

        $provinceId = (int) ($filters['province_id'] ?? 0);
        $niveauId   = (int) ($filters['niveau_juridiction_id'] ?? 0);

        return view('Modules\AuditLog\Views\user_logs\index', [
            'title'         => lang('Backoffice.logs_user_title'),
            'active'        => 'logs-users',
            'items'         => $this->logs->listUserLogs($filters),
            'filters'       => $filters,
            'provinces'     => (new ProvinceModel())->options(),
            'communes'      => $provinceId ? (new CommuneModel())->optionsByProvince($provinceId) : [],
            'niveaux'       => (new NiveauJuridictionModel())->options(),
            'jurisdictions' => (new JuridictionModel())->options([
                'niveau_juridiction_id' => $niveauId ?: null,
                'province_id'           => $provinceId ?: null,
                'commune_id'            => ! empty($filters['commune_id']) ? (int) $filters['commune_id'] : null,
            ]),
            'users'         => $this->logs->userOptions(),
            'profiles'      => (new ProfilModel())->options(),
            'actions'       => $this->logs->distinctActions('utilisateur'),
            'tables'        => $this->logs->distinctTables('utilisateur'),
            'user'          => $this->sampleUser(),
        ]);
    }

    public function show(int $id)
    {
        if (! BackofficeAccess::can('backoffice/system-logs/users')) {
            return BackofficeAccess::denyRedirect();
        }

        $record = $this->logs->findUserLog($id);
        if (! $record) {
            return redirect()
                ->to(site_url('backoffice/system-logs/users'))
                ->with('error', lang('Backoffice.logs_err_not_found'));
        }

        return view('Modules\AuditLog\Views\user_logs\show', [
            'title'  => lang('Backoffice.logs_user_details_title'),
            'active' => 'logs-users',
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
