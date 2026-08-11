<?php

namespace App\Controllers\Admin\Management;
use App\Controllers\BaseController;

class Bulletin extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        
        // Fetch all posts, ordered by Pinned first, then newest
        $data['posts'] = $db->table('bulletin_posts as bp')
            ->select('bp.*, u.full_name as author')
            ->join('users as u', 'u.user_id = bp.created_by', 'left')
            ->orderBy('bp.is_pinned', 'DESC')
            ->orderBy('bp.created_at', 'DESC')
            
            ->get()->getResultArray();

        $data['title'] = "Bulletin Board Management";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "bulletin";
        
        return view('pages/admin/management/bulletin_board', $data);
    }

    // THE EDIT & SAVE PROCESS (1 Form 1 Process)
    public function save()
    {
        $db = \Config\Database::connect();
        $id = $this->request->getPost('post_id'); // Hidden field from form

        $payload = [
            'title'           => $this->request->getPost('title'),
            'content'         => $this->request->getPost('content'),
            'target_audience' => $this->request->getPost('audience'),
            'is_pinned'       => $this->request->getPost('is_pinned') ? 1 : 0,
            'is_published'    => $this->request->getPost('is_published') ? 1 : 0,
            'starts_at'       => $this->request->getPost('starts_at') ?: null,
            'ends_at'         => $this->request->getPost('ends_at') ?: null,
            'updated_at'      => date('Y-m-d H:i:s')
        ];

        if ($id) {
            // EDIT FUNCTION: Update existing record
            $db->table('bulletin_posts')->where('post_id', $id)->update($payload);
            $msg = "Announcement intelligence updated.";
        } else {
            // CREATE FUNCTION: Insert new record
            $payload['created_by'] = session()->get('user_id') ?? 1;
            $payload['created_at'] = date('Y-m-d H:i:s');
            $db->table('bulletin_posts')->insert($payload);
            $msg = "New announcement published.";
        }

        return redirect()->to('admin/management/bulletin-board')->with('success', $msg);
    }

    // THE AJAX FETCH: Fetches data for the Edit drawer
    public function get_details($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('bulletin_posts')->where('post_id', $id)->get()->getRow();
        
        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Post not found']);
        }
        return $this->response->setJSON($row);
    }

    public function delete($id)
    {
        $db = \Config\Database::connect();
        $db->table('bulletin_posts')->where('post_id', $id)->delete();
        return redirect()->to('admin/management/bulletin-board')->with('success', 'Post removed.');
    }
}