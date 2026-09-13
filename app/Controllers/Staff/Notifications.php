<?php

namespace App\Controllers\Staff;

use App\Controllers\BaseController;

class Notifications extends BaseController
{
    public function header_data()
    {
        $db = \Config\Database::connect();
        $staffId = session()->get('user_id');

        $typeMeta = [
            'low_stock'     => 'fa-exclamation-triangle',
            'near_expiry'   => 'fa-hourglass-half',
            'expired'       => 'fa-ban',
            'po_approval'   => 'fa-file-alt',
            'assigned_task' => 'fa-clipboard-check',
        ];

        $alerts = $db->table('alerts')
            ->where('is_resolved', 0)
            ->groupStart()
                ->where('assigned_to', $staffId)
                ->orWhere('assigned_to IS NULL', null, false)
            ->groupEnd()
            ->orderBy('priority', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->limit(8)
            ->get()->getResultArray();

        foreach ($alerts as &$a) {
            $a['icon'] = $typeMeta[$a['alert_type']] ?? 'fa-info-circle';
        }

        $unreadCount = $db->table('alerts')
            ->where('is_resolved', 0)
            ->groupStart()
                ->where('assigned_to', $staffId)
                ->orWhere('assigned_to IS NULL', null, false)
            ->groupEnd()
            ->countAllResults();

        return $this->response->setJSON([
            'unread_count' => $unreadCount,
            'items'        => $alerts,
        ]);
    }
}