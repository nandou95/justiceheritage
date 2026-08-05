<?php

namespace Modules\Notification\Controllers;

use App\Libraries\BackofficeAccess;
use Modules\CourtJurisdiction\Models\CommuneModel;
use Modules\CourtJurisdiction\Models\ProvinceModel;
use Modules\Notification\Models\CanalNotificationModel;
use Modules\Notification\Models\StatutNotificationModel;
use Modules\Notification\Services\BackofficeNotificationService;

class ComplainantNotifications extends \App\Controllers\BaseController
{
    private BackofficeNotificationService $notifications;

    public function __construct()
    {
        $this->notifications = new BackofficeNotificationService();
    }

    public function index()
    {
        if (! BackofficeAccess::can('backoffice/notifications/complainants')) {
            return BackofficeAccess::denyRedirect();
        }

        $filters = [
            'canal_notification_id'  => $this->request->getGet('canal_notification_id'),
            'statut_notification_id' => $this->request->getGet('statut_notification_id'),
            'province_id'            => $this->request->getGet('province_id'),
            'commune_id'             => $this->request->getGet('commune_id'),
            'personne_id'            => $this->request->getGet('personne_id'),
            'plainte_id'             => $this->request->getGet('plainte_id'),
            'date_from'              => $this->request->getGet('date_from'),
            'date_to'                => $this->request->getGet('date_to'),
        ];

        $provinceId = (int) ($filters['province_id'] ?? 0);

        return view('Modules\Notification\Views\complainant\index', [
            'title'        => lang('Backoffice.ntf_complainant_title'),
            'active'       => 'ntf-complainants',
            'items'        => $this->notifications->listComplainantNotifications($filters),
            'filters'      => $filters,
            'channels'     => (new CanalNotificationModel())->options(),
            'statuses'     => (new StatutNotificationModel())->options(),
            'provinces'    => (new ProvinceModel())->options(),
            'communes'     => $provinceId ? (new CommuneModel())->optionsByProvince($provinceId) : [],
            'complainants' => $this->notifications->complainantOptions(),
            'complaints'   => $this->notifications->complaintOptions(),
            'user'         => $this->sampleUser(),
        ]);
    }

    public function show(int $id)
    {
        if (! BackofficeAccess::can('backoffice/notifications/complainants')) {
            return BackofficeAccess::denyRedirect();
        }

        $record = $this->notifications->findComplainantNotification($id);
        if (! $record) {
            return redirect()
                ->to(site_url('backoffice/notifications/complainants'))
                ->with('error', lang('Backoffice.ntf_err_not_found'));
        }

        return view('Modules\Notification\Views\complainant\show', [
            'title'  => lang('Backoffice.ntf_complainant_details_title'),
            'active' => 'ntf-complainants',
            'record' => $record,
            'user'   => $this->sampleUser(),
        ]);
    }

    public function resend(int $id)
    {
        if (! BackofficeAccess::can('backoffice/notifications/complainants')) {
            return BackofficeAccess::denyRedirect();
        }

        $result = $this->notifications->resendComplainantNotification($id);
        if (! ($result['ok'] ?? false)) {
            return redirect()
                ->to(site_url('backoffice/notifications/complainants/' . $id))
                ->with('errors', $result['errors'] ?? [lang('Backoffice.ntf_err_resend')]);
        }

        return redirect()
            ->to(site_url('backoffice/notifications/complainants/' . (int) ($result['id'] ?? $id)))
            ->with('success', lang('Backoffice.ntf_resent'));
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
