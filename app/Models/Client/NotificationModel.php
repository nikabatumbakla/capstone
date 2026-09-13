<?php

namespace App\Models\Client;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getRecent(int $clientId, int $limit = 10): array
    {
        return $this->db->table('client_notifications')
            ->where('client_id', $clientId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    public function getUnreadCount(int $clientId): int
    {
        return $this->db->table('client_notifications')->where('client_id', $clientId)->where('is_read', 0)->countAllResults();
    }

    public function markAllRead(int $clientId): void
    {
        $this->db->table('client_notifications')->where('client_id', $clientId)->where('is_read', 0)->update(['is_read' => 1]);
    }

    public static function notify($db, int $clientId, string $message, ?string $link = null): void
    {
        $db->table('client_notifications')->insert([
            'client_id' => $clientId,
            'message'   => $message,
            'link'      => $link,
        ]);
    }
}