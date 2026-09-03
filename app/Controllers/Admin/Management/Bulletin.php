<?php

namespace App\Controllers\Admin\Management;

use App\Controllers\BaseController;
use App\Models\Admin\Management\BulletinModel;

class Bulletin extends BaseController
{
    protected $bulletinModel;

    public function __construct()
    {
        $this->bulletinModel = new BulletinModel();
    }

    public function index()
    {
        $this->bulletinModel->archiveExpiredPosts();

        $audience = $this->request->getGet('audience') ?: '';
        $search = trim((string) ($this->request->getGet('search') ?? ''));
        $page = (int) ($this->request->getGet('page') ?? 1);

        $feed = $this->bulletinModel->getFeed($audience, $search, $page, 8);

        foreach ($feed['data'] as &$post) {
            $post['status'] = $this->bulletinModel->getStatus($post);
        }

        $data['posts'] = $feed['data'];
        $data['total_pages'] = $feed['total_pages'];
        $data['current_page'] = $page;
        $data['audience_filter'] = $audience;
        $data['search'] = $search;
        $data['counts'] = $this->bulletinModel->getCounts();

        $data['title'] = "Bulletin Board Management";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "bulletin";
        return view('pages/admin/management/bulletin_board', $data);
    }

    public function save()
{
    $id = $this->request->getPost('post_id');
    $startsAt = $this->request->getPost('starts_at');
    $endsAt = $this->request->getPost('ends_at');

    if (empty($startsAt) || empty($endsAt)) {
        return redirect()->back()->withInput()->with('error', 'Display start and end times are required.');
    }
    if (strtotime($endsAt) <= strtotime($startsAt)) {
        return redirect()->back()->withInput()->with('error', 'End time must be after the start time.');
    }
    if (strtotime($endsAt) <= time()) {
        return redirect()->back()->withInput()->with('error', 'End time must be in the future...');
    }


    $payload = [
        'title'           => $this->request->getPost('title'),
        'content'         => $this->request->getPost('content'),
        'target_audience' => $this->request->getPost('audience'),
        'is_pinned'       => $this->request->getPost('is_pinned') ? 1 : 0,
        'is_published'    => $this->request->getPost('is_published') ? 1 : 0,
        'starts_at'       => $startsAt,
        'ends_at'         => $endsAt,
        'updated_at'      => date('Y-m-d H:i:s'),
    ];

    $file = $this->request->getFile('image');
    if ($file && $file->isValid() && !$file->hasMoved()) {
        $newName = $file->getRandomName();
        $file->move(FCPATH . 'public/uploads/bulletin', $newName);
        $payload['image_path'] = 'public/uploads/bulletin/' . $newName;
    }

    if ($id) {
        $this->bulletinModel->savePost($payload, (int) $id);
        $msg = "Announcement updated.";
    } else {
        $payload['created_by'] = session()->get('user_id') ?? 1;
        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->bulletinModel->savePost($payload);
        $msg = "New announcement published.";
    }

    return redirect()->to('admin/management/bulletin-board')->with('success', $msg);
}

    public function get_details($id)
    {
        $row = $this->bulletinModel->getById($id);
        if (!$row) return $this->response->setStatusCode(404)->setJSON(['error' => 'Post not found']);
        return $this->response->setJSON($row);
    }

    public function delete($id)
{
    $this->bulletinModel->archivePost((int) $id);
    return redirect()->to('admin/management/bulletin-board')->with('success', 'Announcement archived.');
}

public function get_archive()
{
    $this->bulletinModel->archiveExpiredPosts();
    $search = trim((string) ($this->request->getGet('search') ?? ''));
    $page = (int) ($this->request->getGet('page') ?? 1);
    $result = $this->bulletinModel->getArchive($search, $page, 10);
    return $this->response->setJSON($result);
}

public function repost()
{
    $archiveId = (int) $this->request->getPost('archive_id');
    $startsAt = $this->request->getPost('starts_at');
    $endsAt = $this->request->getPost('ends_at');
    $newTitle = trim((string) $this->request->getPost('title'));
    $newContent = trim((string) $this->request->getPost('content'));

    if (empty($startsAt) || empty($endsAt)) {
        return redirect()->back()->with('error', 'Please provide both a start and end time.');
    }
    if (strtotime($endsAt) <= strtotime($startsAt)) {
        return redirect()->back()->with('error', 'End time must be after the start time.');
    }
    if (strtotime($endsAt) <= strtotime('-5 minutes')) {
        return redirect()->back()->with('error', 'End time must be in the future.');
    }

    $newId = $this->bulletinModel->repost($archiveId, $startsAt, $endsAt, $newTitle ?: null, $newContent ?: null);

    if ($newId) {
        $this->bulletinModel->removeArchivedPost($archiveId);
        return redirect()->to('admin/management/bulletin-board')->with('success', 'Announcement reposted and now live.');
    }

    return redirect()->to('admin/management/bulletin-board')->with('error', 'Failed to repost — original not found.');
}

public function delete_archived($archiveId)
{
    $this->bulletinModel->removeArchivedPost((int) $archiveId);
    return $this->response->setJSON(['status' => 'success']);
}

}