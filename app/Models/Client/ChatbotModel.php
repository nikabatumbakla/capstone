<?php

namespace App\Models\Client;

use CodeIgniter\Model;

class ChatbotModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    // One active conversation per client — reuse it if open, create fresh only
    // if their last one was resolved (or they have none yet).
    private function getOrCreateConversation(int $userId): int
    {
        $existing = $this->db->table('chatbot_conversations')
            ->where('user_id', $userId)->where('status !=', 'resolved')
            ->orderBy('created_at', 'DESC')->get()->getRow();

        if ($existing) return (int) $existing->conversation_id;

        $this->db->table('chatbot_conversations')->insert(['user_id' => $userId, 'status' => 'bot']);
        return (int) $this->db->insertID();
    }

    private function addMessage(int $conversationId, string $sender, string $message, ?string $staffName = null): void
    {
        $this->db->table('chatbot_messages')->insert([
            'conversation_id' => $conversationId, 'sender' => $sender,
            'staff_name' => $staffName, 'message' => $message,
        ]);
        $this->db->table('chatbot_conversations')->where('conversation_id', $conversationId)->update(['updated_at' => date('Y-m-d H:i:s')]);
    }

    public function findResponse(string $query): ?string
    {
        $intents = $this->db->table('chatbot_intents')->where('is_active', 1)->orderBy('sort_order', 'ASC')->get()->getResultArray();
        $q = strtolower($query);
        foreach ($intents as $intent) {
            $keywords = array_map('trim', explode(',', strtolower($intent['keywords'])));
            foreach ($keywords as $kw) {
                if ($kw !== '' && str_contains($q, $kw)) return $intent['response_template'];
            }
        }
        return null;
    }

    // Once a human is engaged (escalated/in_progress), the bot stops answering —
    // every further message just gets logged and waits for staff, so a client
    // never gets a confusing mix of bot replies and staff replies to the same issue.
    public function ask(int $userId, string $query): string
{
    $conversationId = $this->getOrCreateConversation($userId);
    $this->addMessage($conversationId, 'user', $query);

    $conversation = $this->db->table('chatbot_conversations')->where('conversation_id', $conversationId)->get()->getRow();

    if ($conversation->status === 'bot') {
        $response = $this->findResponse($query);
        if ($response !== null) {
            $this->addMessage($conversationId, 'bot', $response);
            return $response;
        }
        $fallback = "Hmm, I'm not sure about that one — but no worries, I've already looped in our support team and they'll reply to you right here soon.";
        $this->addMessage($conversationId, 'bot', $fallback);
        $this->db->table('chatbot_conversations')->where('conversation_id', $conversationId)->update(['status' => 'escalated']);
        return $fallback;
    }
    return '';
}
    public function getHistory(int $userId): array
{
    $conversation = $this->db->table('chatbot_conversations')
        ->where('user_id', $userId)->orderBy('created_at', 'DESC')->get()->getRow();
    if (!$conversation) return [];

    $rows = $this->db->table('chatbot_messages')
        ->where('conversation_id', $conversation->conversation_id)
        ->orderBy('created_at', 'ASC')->get()->getResultArray();

    return array_map(fn($r) => [
        'sender' => $r['sender'],
        'text' => $r['message'],
        'staff_name' => $r['staff_name'],
        'created_at' => $r['created_at'],
    ], $rows);
}

    public function getMessageCount(int $userId): int
    {
        $conversation = $this->db->table('chatbot_conversations')
            ->where('user_id', $userId)->orderBy('created_at', 'DESC')->get()->getRow();
        if (!$conversation) return 0;

        return $this->db->table('chatbot_messages')->where('conversation_id', $conversation->conversation_id)->countAllResults();
    }
}