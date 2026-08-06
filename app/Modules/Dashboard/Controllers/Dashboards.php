<?php

namespace Modules\Dashboard\Controllers;

use App\Libraries\BackofficeAccess;
use Modules\Dashboard\Services\DashboardAnalyticsService;

class Dashboards extends \App\Controllers\BaseController
{
    private DashboardAnalyticsService $analytics;

    public function __construct()
    {
        $this->analytics = new DashboardAnalyticsService();
    }

    public function executive()
    {
        return $this->render('backoffice/dashboards/executive', $this->analytics->executive());
    }

    public function complaints()
    {
        return $this->render('backoffice/dashboards/complaints', $this->analytics->complaints());
    }

    public function complainants()
    {
        return $this->render('backoffice/dashboards/complainants', $this->analytics->complainants());
    }

    public function appeals()
    {
        return $this->render('backoffice/dashboards/appeals', $this->analytics->appeals());
    }

    public function summons()
    {
        return $this->render('backoffice/dashboards/summons', $this->analytics->summons());
    }

    public function hearings()
    {
        return $this->render('backoffice/dashboards/hearings', $this->analytics->hearings());
    }

    public function notifications()
    {
        return $this->render('backoffice/dashboards/notifications', $this->analytics->notifications());
    }

    public function courts()
    {
        return $this->render('backoffice/dashboards/courts', $this->analytics->courts());
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function render(string $route, array $payload)
    {
        if (! BackofficeAccess::can($route)) {
            return BackofficeAccess::denyRedirect();
        }

        return view('Modules\Dashboard\Views\dashboard', [
            'title'  => $payload['title'] ?? lang('Backoffice.app_name'),
            'active' => $payload['active'] ?? 'dashboard',
            'lead'   => $payload['lead'] ?? '',
            'kpis'   => $payload['kpis'] ?? [],
            'charts' => $payload['charts'] ?? [],
            'tables' => $payload['tables'] ?? [],
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
