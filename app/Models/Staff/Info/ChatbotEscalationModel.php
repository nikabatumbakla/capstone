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

    public function getConversations(string $status = 'escalated', int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = fn($b) => $status !== 'all' ? $b->where('cc.status', $status) : $b;

        $countBuilder = $this->db->table('chatbot_conversations as cc');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('chatbot_conversations as cc')
            ->select("cc.*, u.full_name as customer_name, u.role as customer_role,
                (SELECT message FROM chatbot_messages WHERE conversation_id = cc.conversation_id AND sender = 'user' ORDER BY created_at ASC LIMIT 1) as first_query")
            ->join('users as u', 'u.user_id = cc.user_id', 'left');
        $apply($builder);
        $builder->orderBy('cc.updated_at', 'DESC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    public function getCounts(): array
    {
        return [
            'open'        => $this->db->table('chatbot_conversations')->where('status', 'escalated')->countAllResults(),
            'in_progress' => $this->db->table('chatbot_conversations')->where('status', 'in_progress')->countAllResults(),
            'resolved'    => $this->db->table('chatbot_conversations')->where('status', 'resolved')->countAllResults(),
        ];
    }

    public function getConversationDetails(int $id)
    {
        $conversation = $this->db->table('chatbot_conversations as cc')
            ->select('cc.*, u.full_name as customer, u.role as customer_role')
            ->join('users as u', 'u.user_id = cc.user_id', 'left')
            ->where('cc.conversation_id', $id)->get()->getRow();
        if (!$conversation) return null;

        $conversation->messages = $this->db->table('chatbot_messages')
            ->where('conversation_id', $id)->orderBy('created_at', 'ASC')->get()->getResultArray();
        return $conversation;
    }

    public function appendStaffReply(int $conversationId, string $staffName, string $message): array
{
    $conv = $this->db->table('chatbot_conversations')->where('conversation_id', $conversationId)->get()->getRow();
    if (!$conv || $conv->status === 'resolved') {
        return ['success' => false, 'message' => 'This conversation is already resolved and can no longer be replied to.'];
    }

    $this->db->table('chatbot_messages')->insert([
        'conversation_id' => $conversationId, 'sender' => 'staff', 'staff_name' => $staffName, 'message' => $message,
    ]);
    $this->db->table('chatbot_conversations')->where('conversation_id', $conversationId)
        ->update(['status' => 'in_progress', 'updated_at' => date('Y-m-d H:i:s')]);

    return ['success' => true];
}

    public function claimEscalation(int $conversationId, int $staffUserId): void
    {
        $this->db->table('chatbot_conversations')->where('conversation_id', $conversationId)
            ->update(['assigned_to' => $staffUserId, 'status' => 'in_progress']);
    }

    public function resolveConversation(int $conversationId): void
    {
        $this->db->table('chatbot_conversations')->where('conversation_id', $conversationId)
            ->update(['status' => 'resolved', 'resolved_at' => date('Y-m-d H:i:s')]);
    }
}