<?php

namespace Modules\Notification\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Modules\Notification\Services\InboxNotificationService;

class InboxNotifications extends \App\Controllers\BaseController
{
    private InboxNotificationService $inbox;

    public function __construct()
    {
        $this->inbox = new InboxNotificationService();
    }

    public function index()
    {
        $userId = $this->currentUserId();
        if ($userId < 1) {
            return redirect()->to(site_url('backoffice/login'));
        }

        return view('Modules\Notification\Views\inbox\index', [
            'title'         => lang('Backoffice.inbox_title'),
            'active'        => 'my-notifications',
            'notifications' => $this->inbox->listForUser($userId),
            'unreadCount'   => $this->inbox->unreadCount($userId),
        ]);
    }

    public function show(int $id)
    {
        $userId = $this->currentUserId();
        if ($userId < 1) {
            return redirect()->to(site_url('backoffice/login'));
        }

        $marked = $this->inbox->markAsRead($userId, $id);
        $record = $marked['action'] ?? $this->inbox->findOwned($userId, $id);
        if ($record === null) {
            return redirect()
                ->to(site_url('backoffice/my/notifications'))
                ->with('error', lang('Backoffice.inbox_err_not_found'));
        }

        return view('Modules\Notification\Views\inbox\show', [
            'title'  => lang('Backoffice.inbox_details_title'),
            'active' => 'my-notifications',
            'record' => $record,
        ]);
    }

    public function unreadJson(): ResponseInterface
    {
        $userId = $this->currentUserId();
        if ($userId < 1) {
            return $this->response->setStatusCode(401)->setJSON([
                'ok'     => false,
                'errors' => [lang('Backoffice.login_err_session')],
            ]);
        }

        return $this->response->setJSON([
            'ok'            => true,
            'count'         => $this->inbox->unreadCount($userId),
            'notifications' => $this->inbox->unreadForUser($userId, 10),
        ]);
    }

    public function countJson(): ResponseInterface
    {
        $userId = $this->currentUserId();
        if ($userId < 1) {
            return $this->response->setStatusCode(401)->setJSON(['ok' => false, 'count' => 0]);
        }

        return $this->response->setJSON([
            'ok'    => true,
            'count' => $this->inbox->unreadCount($userId),
        ]);
    }

    public function markRead(int $id): ResponseInterface
    {
        $userId = $this->currentUserId();
        if ($userId < 1) {
            return $this->response->setStatusCode(401)->setJSON([
                'ok'     => false,
                'errors' => [lang('Backoffice.login_err_session')],
            ]);
        }

        $result = $this->inbox->markAsRead($userId, $id);
        if (! ($result['ok'] ?? false)) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok'     => false,
                'errors' => $result['errors'] ?? [lang('Backoffice.inbox_err_mark_read')],
            ]);
        }

        return $this->response->setJSON([
            'ok'       => true,
            'count'    => $this->inbox->unreadCount($userId),
            'action'   => $result['action'] ?? null,
            'csrfHash' => csrf_hash(),
        ]);
    }

    private function currentUserId(): int
    {
        return (int) (session('backoffice_user_id') ?? 0);
    }
}
