<?php

namespace App\Controllers\Staff\Operations;

use App\Controllers\BaseController;
use App\Models\Staff\Operations\QuickLookupModel;

class QuickLookup extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new QuickLookupModel();
    }

    public function search()
    {
        $term = trim((string) $this->request->getPost('term'));
        if ($term === '') return $this->response->setJSON([]);
        return $this->response->setJSON($this->model->search($term));
    }
}