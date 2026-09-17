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

    $response = $this->model->ask((int) session()->get('user_id'), $query);
    return $this->response->setJSON(['response' => $response !== '' ? $response : null]);
}

    public function history()
    {
        return $this->response->setJSON($this->model->getHistory((int) session()->get('user_id')));
    }

    public function poll_count()
    {
        return $this->response->setJSON(['count' => $this->model->getMessageCount((int) session()->get('user_id'))]);
    }
}