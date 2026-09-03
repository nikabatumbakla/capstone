<?php

namespace App\Models\Admin\Management;

use CodeIgniter\Model;

class ChatbotModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getCounts(): array
    {
        return [
            'queries'     => $this->db->table('chatbot_logs')->countAllResults(),
            'escalations' => $this->db->table('chatbot_escalations')->where('status', 'open')->countAllResults(),
        ];
    }

    public function getIntents(string $search = '', int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = fn($b) => $search !== '' ? $b->groupStart()->like('intent_name', $search)->orLike('keywords', $search)->groupEnd() : $b;

        $countBuilder = $this->db->table('chatbot_intents');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('chatbot_intents');
        $apply($builder);
        $builder->orderBy('sort_order', 'ASC')->orderBy('intent_name', 'ASC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    public function getIntentById(int $id)
    {
        return $this->db->table('chatbot_intents')->where('intent_id', $id)->get()->getRow();
    }

    public function intentNameExists(string $name, ?int $excludeId = null): bool
    {
        $builder = $this->db->table('chatbot_intents')->where('intent_name', $name);
        if ($excludeId) $builder->where('intent_id !=', $excludeId);
        return $builder->countAllResults() > 0;
    }

    public function saveIntent(array $payload, ?int $id = null): void
    {
        if ($id) {
            $this->db->table('chatbot_intents')->where('intent_id', $id)->update($payload);
        } else {
            $this->db->table('chatbot_intents')->insert($payload);
        }
    }

    public function removeIntent(int $id): void
    {
        $this->db->table('chatbot_intents')->where('intent_id', $id)->delete();
    }

    public function getOpenEscalations(string $status = 'open', int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;

        $countBuilder = $this->db->table('chatbot_escalations')->where('status', $status);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('chatbot_escalations as ce')
            ->select('ce.*, cl.query_text, u.full_name as customer_name')
            ->join('chatbot_logs as cl', 'cl.chat_id = ce.chat_id')
            ->join('users as u', 'u.user_id = cl.user_id', 'left')
            ->where('ce.status', $status)
            ->orderBy('ce.created_at', 'DESC')
            ->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    public function getEscalationDetails(int $id)
    {
        return $this->db->table('chatbot_escalations as ce')
            ->select('ce.*, u.full_name as customer')
            ->join('chatbot_logs as cl', 'cl.chat_id = ce.chat_id')
            ->join('users as u', 'u.user_id = cl.user_id', 'left')
            ->where('ce.escalation_id', $id)->get()->getRow();
    }

    // Appends a staff reply to the chat history, and marks the escalation in_progress
    // the moment a staff member actually responds — separate from 'open' (untouched) and 'resolved'.
    public function appendStaffReply(int $escalationId, string $staffName, string $message): void
    {
        $escalation = $this->db->table('chatbot_escalations')->where('escalation_id', $escalationId)->get()->getRow();
        if (!$escalation) return;

        $updatedHistory = rtrim($escalation->full_chat_history) . "\nStaff ({$staffName}): {$message}";

        $this->db->table('chatbot_escalations')->where('escalation_id', $escalationId)->update([
            'full_chat_history' => $updatedHistory,
            'status' => 'in_progress',
        ]);
    }

    public function resolveEscalation(int $escalationId): void
    {
        $this->db->table('chatbot_escalations')->where('escalation_id', $escalationId)->update([
            'status' => 'resolved',
            'resolved_at' => date('Y-m-d H:i:s'),
        ]);
    }
}