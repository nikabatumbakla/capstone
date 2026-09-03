<?php

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Models\Client\ChatbotModel;

class ChatbotWidget extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new ChatbotModel();
    }

    public function ask()
    {
        $query = trim((string) $this->request->getPost('query'));
        if ($query === '') return $this->response->setJSON(['response' => null]);

        $response = $this->model->findResponse($query);
        $this->model->logQuery(session()->get('user_id'), $query, $response);

        return $this->response->setJSON([
            'response' => $response ?? "I couldn't find an answer for that right away — I've forwarded your question to our support team, and they'll follow up with you shortly.",
        ]);
    }

    public function history()
    {
        $history = $this->model->getHistory(session()->get('user_id'), 20);
        return $this->response->setJSON($history);
    }
}