<?php

namespace App\Models\Supplier;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getRecent(int $supplierId, int $limit = 10): array
    {
        return $this->db->table('supplier_notifications')
            ->where('supplier_id', $supplierId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    public function getUnreadCount(int $supplierId): int
    {
        return $this->db->table('supplier_notifications')->where('supplier_id', $supplierId)->where('is_read', 0)->countAllResults();
    }

    public function markAllRead(int $supplierId): void
    {
        $this->db->table('supplier_notifications')->where('supplier_id', $supplierId)->where('is_read', 0)->update(['is_read' => 1]);
    }

    // Called from admin-side code whenever something happens that a supplier should know about
    public static function notify($db, int $supplierId, string $message, ?string $link = null): void
    {
        $db->table('supplier_notifications')->insert([
            'supplier_id' => $supplierId,
            'message'     => $message,
            'link'        => $link,
        ]);
    }
}