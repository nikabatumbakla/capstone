<?php
namespace App\Controllers\Client\Support;
use App\Controllers\BaseController;

class Profile extends BaseController {
    public function index() {
        $db = \Config\Database::connect();
        $data['client'] = $db->table('institutional_clients')->where('client_id', session()->get('client_id'))->get()->getRow();
        $data['title'] = "My Profile";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "profile";
        return view('pages/client/support/profile', $data);
    }
}