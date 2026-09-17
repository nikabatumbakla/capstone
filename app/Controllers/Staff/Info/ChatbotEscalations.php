<?php

namespace App\Controllers\Staff\Info;

use App\Controllers\BaseController;
use App\Models\Staff\Info\ChatbotEscalationModel;

class ChatbotEscalations extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new ChatbotEscalationModel();
    }

    public function index()
    {
        $status = $this->request->getGet('status') ?: 'escalated';
        $page = (int) ($this->request->getGet('page') ?? 1);

        $result = $this->model->getConversations($status, $page, 10);
        $counts = $this->model->getCounts();

        $data['escalations'] = $result['data'];
        $data['total_pages'] = $result['total_pages'];
        $data['current_page'] = $page;
        $data['status_filter'] = $status;
        $data['count_open'] = $counts['open'];
        $data['count_in_progress'] = $counts['in_progress'];
        $data['count_resolved'] = $counts['resolved'];

        $data['title'] = "Customer Support Queue";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "chatbot";
        return view('pages/staff/info/chatbot_escalations', $data);
    }

    public function get_details($id)
    {
        $row = $this->model->getConversationDetails((int) $id);
        if (!$row) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        return $this->response->setJSON($row);
    }

    public function claim($id)
    {
        $this->model->claimEscalation((int) $id, session()->get('user_id'));
        return redirect()->back()->with('success', 'You are now handling this conversation.');
    }

    public function reply()
{
    $id = (int) $this->request->getPost('escalation_id');
    $message = trim((string) $this->request->getPost('message'));
    if ($message === '') return $this->response->setStatusCode(422)->setJSON(['error' => 'Message cannot be empty.']);

    $result = $this->model->appendStaffReply($id, session()->get('full_name') ?? 'Staff', $message);
    if (!$result['success']) return $this->response->setStatusCode(422)->setJSON(['error' => $result['message']]);

    $updated = $this->model->getConversationDetails($id);
    return $this->response->setJSON(['status' => 'success', 'messages' => $updated->messages]);
}

    public function resolve($id)
    {
        $this->model->resolveConversation((int) $id);
        return redirect()->to('staff/info/support-queue')->with('success', 'Marked resolved.');
    }
}