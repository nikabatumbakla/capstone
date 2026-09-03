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

    public function findResponse(string $query): ?string
    {
        $intents = $this->db->table('chatbot_intents')->where('is_active', 1)->get()->getResultArray();
        $queryLower = strtolower($query);

        foreach ($intents as $intent) {
            $keywords = array_map('trim', explode(',', strtolower($intent['keywords'])));
            foreach ($keywords as $keyword) {
                if ($keyword !== '' && strpos($queryLower, $keyword) !== false) {
                    return $intent['response_template'];
                }
            }
        }
        return null;
    }

    public function logQuery(?int $userId, string $query, ?string $response): int
    {
        $this->db->table('chatbot_logs')->insert([
            'user_id'       => $userId,
            'query_text'    => $query,
            'response_text' => $response,
            'was_escalated' => $response === null ? 1 : 0,
        ]);
        $chatId = $this->db->insertID();

        // No auto-answer found — create a real escalation so staff's Support Queue picks it up
        if ($response === null) {
            $this->db->table('chatbot_escalations')->insert([
                'chat_id' => $chatId,
                'status'  => 'open',
                'full_chat_history' => "User: {$query}\nBot: I couldn't find an answer for that. Connecting you with our team...",
            ]);
        }

        return $chatId;
    }

    public function getHistory(?int $userId, int $limit = 20): array
{
    if (!$userId) return [];
    return $this->db->table('chatbot_logs')
        ->where('user_id', $userId)
        ->orderBy('created_at', 'ASC')
        ->limit($limit)
        ->get()->getResultArray();
}
}