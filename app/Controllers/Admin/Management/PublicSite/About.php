<?php

namespace App\Controllers\Admin\Management\PublicSite;

use App\Controllers\BaseController;
use App\Models\Admin\Management\PublicSite\SiteContentModel;

class About extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new SiteContentModel();
    }

    public function index()
    {
        $data['about'] = $this->model->getAboutContent();
        $data['team'] = $this->model->getTeamMembers();
        $data['title'] = "Public Site — About Us";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "site-content";
        $data['active_section'] = 'about';
        return view('pages/admin/management/site_content/about', $data);
    }

    public function save()
    {
        $this->model->saveAboutContent([
            'story_paragraph1' => $this->request->getPost('story_paragraph1'),
            'story_paragraph2' => $this->request->getPost('story_paragraph2'),
            'mission_text' => $this->request->getPost('mission_text'),
            'vision_text' => $this->request->getPost('vision_text'),
        ]);
        return redirect()->to('admin/management/site-content/about')->with('success', 'About Us content updated.');
    }

    public function save_team()
    {
        $id = $this->request->getPost('member_id');
        $photoPath = null;
        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            $newName = $photo->getRandomName();
            $photo->move(FCPATH . 'images/team', $newName);
            $photoPath = 'images/team/' . $newName;
        }
        $this->model->saveTeamMember($this->request->getPost(), $id ?: null, $photoPath);
        return redirect()->to('admin/management/site-content/about')->with('success', $id ? 'Team member updated.' : 'Team member added.');
    }

    public function get_team($id)
    {
        $row = $this->model->getTeamMember((int) $id);
        if (!$row) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        return $this->response->setJSON($row);
    }

    public function delete_team($id)
    {
        $this->model->deleteTeamMember((int) $id);
        return redirect()->to('admin/management/site-content/about')->with('success', 'Team member removed.');
    }
}