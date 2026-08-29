<?php
namespace App\Controllers\Client\Support;
use App\Controllers\BaseController;

class Chatbot extends BaseController {
    public function index() {
        $db = \Config\Database::connect();
        // Fetch recent chat history for this user
        $data['chat_history'] = $db->table('chatbot_logs')
            ->where('user_id', session()->get('user_id'))
            ->orderBy('created_at', 'ASC')
            ->get()->getResultArray();

        $data['title'] = "ChatBot Support";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "chatbot";
        return view('pages/client/support/chatbot', $data);
    }
}