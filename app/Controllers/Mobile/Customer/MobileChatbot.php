<?php

namespace App\Controllers\Mobile\Customer;

use App\Controllers\BaseController;
use App\Models\Mobile\CustomerMobileModel;

class MobileChatbot extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'customer') return redirect()->to('m/');
        $data['title'] = 'Chat Support';
        $data['page_name'] = 'chatbot';
        return view('mobile/customer/chatbot', $data);
    }

    public function history()
    {
        $model = new CustomerMobileModel();
        $history = $model->getChatHistory(session()->get('user_id'), 30);
        return $this->response->setJSON($history);
    }

    public function ask()
    {
        $query = trim((string) $this->request->getPost('query'));
        if ($query === '') return $this->response->setJSON(['response' => null]);

        $model = new \App\Models\Client\ChatbotModel();
        $response = $model->findResponse($query);
        $model->logQuery(session()->get('user_id'), $query, $response);

        return $this->response->setJSON([
            'response' => $response ?? "I couldn't find an answer for that — I've forwarded your question to our team.",
        ]);
    }
}