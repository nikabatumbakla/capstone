<?php

namespace App\Controllers\Mobile\Customer;

use App\Controllers\BaseController;
use App\Models\Client\ChatbotModel;

class MobileChatbot extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new ChatbotModel();
    }

    public function index()
    {
        if (session()->get('role') !== 'customer') return redirect()->to('m/');
        $data['title'] = 'Chat Support';
        $data['page_name'] = 'chatbot';
        return view('mobile/customer/chatbot', $data);
    }

    public function history()
    {
        return $this->response->setJSON($this->model->getHistory((int) session()->get('user_id')));
    }

    public function ask()
    {
        $query = trim((string) $this->request->getPost('query'));
        if ($query === '') return $this->response->setJSON(['response' => null]);

        $response = $this->model->ask((int) session()->get('user_id'), $query);
        return $this->response->setJSON(['response' => $response]);
    }

    public function poll_count()
    {
        return $this->response->setJSON(['count' => $this->model->getMessageCount((int) session()->get('user_id'))]);
    }
}