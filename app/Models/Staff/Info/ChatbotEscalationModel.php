<?php

namespace App\Models\Staff\Info;

use CodeIgniter\Model;

class ChatbotEscalationModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getEscalations(string $status = 'open', int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = fn($b) => $status !== 'all' ? $b->where('ce.status', $status) : $b;

        $countBuilder = $this->db->table('chatbot_escalations as ce');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('chatbot_escalations as ce')
            ->select('ce.*, cl.query_text, u.full_name as customer_name, u.role as customer_role')
            ->join('chatbot_logs as cl', 'cl.chat_id = ce.chat_id')
            ->join('users as u', 'u.user_id = cl.user_id', 'left');
        $apply($builder);
        $builder->orderBy('ce.created_at', 'DESC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    public function getCounts(): array
{
    return [
        'open'        => $this->db->table('chatbot_escalations')->where('status', 'open')->countAllResults(),
        'in_progress' => $this->db->table('chatbot_escalations')->where('status', 'in_progress')->countAllResults(),
        'resolved'    => $this->db->table('chatbot_escalations')->where('status', 'resolved')->countAllResults(),
    ];
}

    public function getEscalationDetails(int $id)
    {
        return $this->db->table('chatbot_escalations as ce')
            ->select('ce.*, u.full_name as customer, u.role as customer_role')
            ->join('chatbot_logs as cl', 'cl.chat_id = ce.chat_id')
            ->join('users as u', 'u.user_id = cl.user_id', 'left')
            ->where('ce.escalation_id', $id)->get()->getRow();
    }

    public function appendStaffReply(int $escalationId, string $staffName, string $message): void
    {
        $escalation = $this->db->table('chatbot_escalations')->where('escalation_id', $escalationId)->get()->getRow();
        if (!$escalation) return;

        $updatedHistory = rtrim($escalation->full_chat_history) . "\nStaff ({$staffName}): {$message}";

        $this->db->table('chatbot_escalations')->where('escalation_id', $escalationId)->update([
            'full_chat_history' => $updatedHistory,
            'status'            => 'in_progress',
            'assigned_to'       => $escalation->assigned_to,
        ]);
    }

    public function claimEscalation(int $escalationId, int $staffUserId): void
    {
        $this->db->table('chatbot_escalations')->where('escalation_id', $escalationId)->update([
            'assigned_to' => $staffUserId,
            'status'      => 'in_progress',
        ]);
    }

    public function resolveEscalation(int $escalationId): void
    {
        $this->db->table('chatbot_escalations')->where('escalation_id', $escalationId)->update([
            'status'      => 'resolved',
            'resolved_at' => date('Y-m-d H:i:s'),
        ]);
    }
}