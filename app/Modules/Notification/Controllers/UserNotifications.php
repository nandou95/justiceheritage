<?php

namespace Modules\Notification\Controllers;

use App\Libraries\BackofficeAccess;
use Modules\Administration\Models\JuridictionModel;
use Modules\Administration\Models\NiveauJuridictionModel;
use Modules\Administration\Models\ProfilModel;
use Modules\CourtJurisdiction\Models\CommuneModel;
use Modules\CourtJurisdiction\Models\ProvinceModel;
use Modules\Notification\Models\CanalNotificationModel;
use Modules\Notification\Models\StatutNotificationModel;
use Modules\Notification\Services\BackofficeNotificationService;

class UserNotifications extends \App\Controllers\BaseController
{
    private BackofficeNotificationService $notifications;

    public function __construct()
    {
        $this->notifications = new BackofficeNotificationService();
    }

    public function index()
    {
        if (! BackofficeAccess::can('backoffice/notifications/users')) {
            return BackofficeAccess::denyRedirect();
        }

        $filters = [
            'canal_notification_id'  => $this->request->getGet('canal_notification_id'),
            'statut_notification_id' => $this->request->getGet('statut_notification_id'),
            'province_id'            => $this->request->getGet('province_id'),
            'commune_id'             => $this->request->getGet('commune_id'),
            'niveau_juridiction_id'  => $this->request->getGet('niveau_juridiction_id'),
            'juridiction_id'         => $this->request->getGet('juridiction_id'),
            'utilisateur_id'         => $this->request->getGet('utilisateur_id'),
            'profil_id'              => $this->request->getGet('profil_id'),
            'date_from'              => $this->request->getGet('date_from'),
            'date_to'                => $this->request->getGet('date_to'),
        ];

        $provinceId = (int) ($filters['province_id'] ?? 0);
        $niveauId   = (int) ($filters['niveau_juridiction_id'] ?? 0);

        return view('Modules\Notification\Views\user\index', [
            'title'         => lang('Backoffice.ntf_user_title'),
            'active'        => 'ntf-users',
            'items'         => $this->notifications->listUserNotifications($filters),
            'filters'       => $filters,
            'channels'      => (new CanalNotificationModel())->options(),
            'statuses'      => (new StatutNotificationModel())->options(),
            'provinces'     => (new ProvinceModel())->options(),
            'communes'      => $provinceId ? (new CommuneModel())->optionsByProvince($provinceId) : [],
            'niveaux'       => (new NiveauJuridictionModel())->options(),
            'jurisdictions' => (new JuridictionModel())->options([
                'niveau_juridiction_id' => $niveauId ?: null,
                'province_id'           => $provinceId ?: null,
                'commune_id'            => ! empty($filters['commune_id']) ? (int) $filters['commune_id'] : null,
            ]),
            'users'         => $this->notifications->userOptions(),
            'profiles'      => (new ProfilModel())->options(),
            'user'          => $this->sampleUser(),
        ]);
    }

    public function show(int $id)
    {
        if (! BackofficeAccess::can('backoffice/notifications/users')) {
            return BackofficeAccess::denyRedirect();
        }

        $record = $this->notifications->findUserNotification($id);
        if (! $record) {
            return redirect()
                ->to(site_url('backoffice/notifications/users'))
                ->with('error', lang('Backoffice.ntf_err_not_found'));
        }

        return view('Modules\Notification\Views\user\show', [
            'title'  => lang('Backoffice.ntf_user_details_title'),
            'active' => 'ntf-users',
            'record' => $record,
            'user'   => $this->sampleUser(),
        ]);
    }

    public function resend(int $id)
    {
        if (! BackofficeAccess::can('backoffice/notifications/users')) {
            return BackofficeAccess::denyRedirect();
        }

        $result = $this->notifications->resendUserNotification($id);
        if (! ($result['ok'] ?? false)) {
            return redirect()
                ->to(site_url('backoffice/notifications/users/' . $id))
                ->with('errors', $result['errors'] ?? [lang('Backoffice.ntf_err_resend')]);
        }

        return redirect()
            ->to(site_url('backoffice/notifications/users/' . (int) ($result['id'] ?? $id)))
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
