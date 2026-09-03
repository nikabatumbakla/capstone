<?php

namespace App\Controllers\Admin\Management;

use App\Controllers\BaseController;
use App\Models\Admin\Management\ChatbotModel;

class Chatbot extends BaseController
{
    protected $chatbotModel;

    public function __construct()
    {
        $this->chatbotModel = new ChatbotModel();
    }

    public function index()
{
    $data['count_queries'] = $this->chatbotModel->getCounts()['queries'];
    $data['count_escalations'] = $this->chatbotModel->getCounts()['escalations'];

    $data['title'] = "ChatBot Intelligence";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "chatbot";
    return view('pages/admin/management/chatbot', $data);
}

    public function save_intent()
    {
        $id = $this->request->getPost('intent_id') ?: null;
        $name = $this->request->getPost('name');

        if ($this->chatbotModel->intentNameExists($name, $id ? (int) $id : null)) {
            return redirect()->back()->withInput()->with('error', 'An intent with that name already exists.');
        }

        $payload = [
            'intent_name'        => $name,
            'keywords'           => $this->request->getPost('keywords'),
            'response_template'  => $this->request->getPost('response'),
            'is_active'          => $this->request->getPost('is_active') ? 1 : 0,
        ];

        $this->chatbotModel->saveIntent($payload, $id ? (int) $id : null);
        return redirect()->to('admin/management/chatbot')->with('success', $id ? 'Intent updated.' : 'New intent created.');
    }

    public function get_intent($id)
    {
        $row = $this->chatbotModel->getIntentById((int) $id);
        if (!$row) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        return $this->response->setJSON($row);
    }

    public function delete_intent($id)
    {
        $this->chatbotModel->removeIntent((int) $id);
        return redirect()->to('admin/management/chatbot')->with('success', 'Intent removed.');
    }

    public function get_escalation($id)
    {
        $row = $this->chatbotModel->getEscalationDetails((int) $id);
        if (!$row) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        return $this->response->setJSON($row);
    }

    public function reply_escalation()
    {
        $id = (int) $this->request->getPost('escalation_id');
        $message = trim((string) $this->request->getPost('message'));
        if ($message === '') {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Message cannot be empty.']);
        }

        $staffName = session()->get('full_name') ?? 'Staff';
        $this->chatbotModel->appendStaffReply($id, $staffName, $message);
        $updated = $this->chatbotModel->getEscalationDetails($id);

        return $this->response->setJSON(['status' => 'success', 'full_chat_history' => $updated->full_chat_history]);
    }

    public function resolve_escalation($id)
    {
        $this->chatbotModel->resolveEscalation((int) $id);
        return redirect()->to('admin/management/chatbot')->with('success', 'Escalation marked resolved.');
    }

    public function intents_data()
{
    $search = trim((string) ($this->request->getGet('search') ?? ''));
    $page = (int) ($this->request->getGet('page') ?? 1);
    $result = $this->chatbotModel->getIntents($search, $page, 10);
    return $this->response->setJSON($result);
}

public function escalations_data()
{
    $status = $this->request->getGet('esc_status') ?: 'open';
    $page = (int) ($this->request->getGet('page') ?? 1);
    $result = $this->chatbotModel->getOpenEscalations($status, $page, 10);
    $result['counts'] = $this->chatbotModel->getCounts();
    return $this->response->setJSON($result);
}

}