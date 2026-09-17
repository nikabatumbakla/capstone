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
            'queries'     => $this->db->table('chatbot_messages')->where('sender', 'user')->countAllResults(),
            'escalations' => $this->db->table('chatbot_conversations')->where('status', 'escalated')->countAllResults(),
        ];
    }

    // ===== INTENTS — unchanged from before =====
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
        if ($id) $this->db->table('chatbot_intents')->where('intent_id', $id)->update($payload);
        else $this->db->table('chatbot_intents')->insert($payload);
    }

    public function removeIntent(int $id): void
    {
        $this->db->table('chatbot_intents')->where('intent_id', $id)->delete();
    }

    // ===== CONVERSATIONS (formerly "escalations") =====
    public function getConversations(string $status = 'escalated', int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;

        $countBuilder = $this->db->table('chatbot_conversations')->where('status', $status);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('chatbot_conversations as cc')
            ->select("cc.*, u.full_name as customer_name, u.role as customer_role,
                (SELECT message FROM chatbot_messages WHERE conversation_id = cc.conversation_id AND sender = 'user' ORDER BY created_at ASC LIMIT 1) as first_query")
            ->join('users as u', 'u.user_id = cc.user_id', 'left')
            ->where('cc.status', $status)
            ->orderBy('cc.updated_at', 'DESC')
            ->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
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

    public function resolveConversation(int $conversationId): void
    {
        $this->db->table('chatbot_conversations')->where('conversation_id', $conversationId)
            ->update(['status' => 'resolved', 'resolved_at' => date('Y-m-d H:i:s')]);
    }
}