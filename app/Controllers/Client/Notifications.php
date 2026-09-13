<?php

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Models\Client\NotificationModel;

class Notifications extends BaseController
{
    protected $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
    }

    public function header_data()
    {
        $clientId = session()->get('client_id');
        return $this->response->setJSON([
            'unread_count' => $this->notificationModel->getUnreadCount($clientId),
            'items'        => $this->notificationModel->getRecent($clientId),
        ]);
    }

    public function mark_read()
    {
        $clientId = session()->get('client_id');
        $this->notificationModel->markAllRead($clientId);
        return $this->response->setJSON(['success' => true]);
    }
}