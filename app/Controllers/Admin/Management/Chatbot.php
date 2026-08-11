<?php

namespace App\Controllers\Admin\Management;
use App\Controllers\BaseController;

class Chatbot extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        
        // 1. Fetch Summary Data
        $data['count_queries'] = $db->table('chatbot_logs')->countAllResults();
        $data['count_escalations'] = $db->table('chatbot_escalations')->where('status', 'open')->countAllResults();

        // 2. Fetch Intents (The Bot Brain)
        $data['intents'] = $db->table('chatbot_intents')->orderBy('intent_name', 'ASC')->get()->getResultArray();

        // 3. Fetch Escalations (Staff Fallback)
        $data['escalations'] = $db->table('chatbot_escalations as ce')
            ->select('ce.*, cl.query_text, u.full_name as customer_name')
            ->join('chatbot_logs as cl', 'cl.chat_id = ce.chat_id')
            ->join('users as u', 'u.user_id = cl.user_id', 'left')
            ->where('ce.status', 'open')->get()->getResultArray();

        $data['title'] = "ChatBot Intelligence";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "chatbot";
        return view('pages/admin/management/chatbot', $data);
    }

    public function save_intent()
    {
        $db = \Config\Database::connect();
        $id = $this->request->getPost('intent_id');
        $data = [
            'intent_name' => $this->request->getPost('name'),
            'keywords'    => $this->request->getPost('keywords'),
            'response_template' => $this->request->getPost('response'),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0
        ];

        if ($id) {
            $db->table('chatbot_intents')->where('intent_id', $id)->update($data);
        } else {
            $db->table('chatbot_intents')->insert($data);
        }
        return redirect()->to('admin/management/chatbot')->with('success', 'Bot Intelligence Updated.');
    }

    // NEW: Fetch Intent for Editing
    public function get_intent($id)
    {
        $db = \Config\Database::connect();
        return $this->response->setJSON($db->table('chatbot_intents')->where('intent_id', $id)->get()->getRow());
    }

    // NEW: Delete Intent
    public function delete_intent($id)
    {
        $db = \Config\Database::connect();
        $db->table('chatbot_intents')->where('intent_id', $id)->delete();
        return redirect()->to('admin/management/chatbot')->with('success', 'Intent removed.');
    }

    // NEW: Fetch Live Chat Details
    public function get_escalation($id)
    {
        $db = \Config\Database::connect();
        $data = $db->table('chatbot_escalations as ce')
            ->select('ce.*, u.full_name as customer')
            ->join('chatbot_logs as cl', 'cl.chat_id = ce.chat_id')
            ->join('users as u', 'u.user_id = cl.user_id', 'left')
            ->where('ce.escalation_id', $id)->get()->getRow();
        return $this->response->setJSON($data);
    }
}