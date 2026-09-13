<?php

namespace App\Controllers\Supplier;

use App\Controllers\BaseController;
use App\Models\Supplier\NotificationModel;

class Notifications extends BaseController
{
    protected $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
    }

    public function header_data()
    {
        $supplierId = session()->get('supplier_id');
        return $this->response->setJSON([
            'unread_count' => $this->notificationModel->getUnreadCount($supplierId),
            'items'        => $this->notificationModel->getRecent($supplierId),
        ]);
    }

    public function mark_read()
    {
        $supplierId = session()->get('supplier_id');
        $this->notificationModel->markAllRead($supplierId);
        return $this->response->setJSON(['success' => true]);
    }
}