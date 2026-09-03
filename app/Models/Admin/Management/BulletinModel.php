<?php

namespace App\Models\Admin\Management;

use CodeIgniter\Model;

class BulletinModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getFeed(string $audience = '', string $search = '', int $page = 1, int $perPage = 8): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = function ($builder) use ($audience, $search) {
            if ($audience !== '') $builder->where('bp.target_audience', $audience);
            if ($search !== '') $builder->groupStart()->like('bp.title', $search)->orLike('bp.content', $search)->groupEnd();
            return $builder;
        };

        $countBuilder = $this->db->table('bulletin_posts as bp');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('bulletin_posts as bp')
            ->select('bp.*, u.full_name as author')
            ->join('users as u', 'u.user_id = bp.created_by', 'left');
        $apply($builder);
        $builder->orderBy('bp.is_pinned', 'DESC')->orderBy('bp.created_at', 'DESC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    public function getById(int $id)
    {
        return $this->db->table('bulletin_posts')->where('post_id', $id)->get()->getRow();
    }

    public function savePost(array $payload, ?int $id = null): int
    {
        if ($id) {
            $this->db->table('bulletin_posts')->where('post_id', $id)->update($payload);
            return $id;
        }
        $this->db->table('bulletin_posts')->insert($payload);
        return $this->db->insertID();
    }

    public function removePost(int $id): void
    {
        $this->db->table('bulletin_posts')->where('post_id', $id)->delete();
    }

    public function getStatus(array $post): string
    {
        if (!$post['is_published']) return 'draft';
        $now = date('Y-m-d H:i:s');
        if ($post['starts_at'] && $post['starts_at'] > $now) return 'scheduled';
        if ($post['ends_at'] && $post['ends_at'] < $now) return 'expired';
        return 'live';
    }

    public function getCounts(): array
    {
        return [
            'total'     => $this->db->table('bulletin_posts')->countAllResults(),
            'pinned'    => $this->db->table('bulletin_posts')->where('is_pinned', 1)->countAllResults(),
            'published' => $this->db->table('bulletin_posts')->where('is_published', 1)->countAllResults(),
            'drafts'    => $this->db->table('bulletin_posts')->where('is_published', 0)->countAllResults(),
        ];
    }

    public function archiveExpiredPosts(): void
    {
        $now = date('Y-m-d H:i:s');
        $expired = $this->db->table('bulletin_posts')->where('ends_at IS NOT NULL', null, false)->where('ends_at <', $now)->get()->getResultArray();

        foreach ($expired as $post) {
            $this->db->table('bulletin_archive')->insert([
                'original_post_id'    => $post['post_id'],
                'title'               => $post['title'],
                'content'             => $post['content'],
                'image_path'          => $post['image_path'],
                'target_audience'     => $post['target_audience'],
                'is_pinned'           => $post['is_pinned'],
                'starts_at'           => $post['starts_at'],
                'ends_at'             => $post['ends_at'],
                'created_by'          => $post['created_by'],
                'original_created_at' => $post['created_at'],
            ]);
            $this->db->table('bulletin_posts')->where('post_id', $post['post_id'])->delete();
        }
    }

    public function getArchive(string $search = '', int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = fn($b) => $search !== '' ? $b->like('title', $search) : $b;

        $countBuilder = $this->db->table('bulletin_archive');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('bulletin_archive as ba')->select('ba.*, u.full_name as author')->join('users as u', 'u.user_id = ba.created_by', 'left');
        $apply($builder);
        $builder->orderBy('ba.archived_at', 'DESC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    public function getArchivedPost(int $archiveId)
    {
        return $this->db->table('bulletin_archive')->where('archive_id', $archiveId)->get()->getRow();
    }

    public function repost(int $archiveId, string $startsAt, string $endsAt, ?string $newTitle = null, ?string $newContent = null): int
    {
        $archived = $this->getArchivedPost($archiveId);
        if (!$archived) return 0;

        $this->db->table('bulletin_posts')->insert([
            'title'           => $newTitle ?: $archived->title,
            'content'         => $newContent ?: $archived->content,
            'image_path'      => $archived->image_path,
            'target_audience' => $archived->target_audience,
            'is_pinned'       => $archived->is_pinned,
            'is_published'    => 1,
            'starts_at'       => $startsAt,
            'ends_at'         => $endsAt,
            'created_by'      => $archived->created_by,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);
        return $this->db->insertID();
    }

    public function removeArchivedPost(int $archiveId): void
    {
        $this->db->table('bulletin_archive')->where('archive_id', $archiveId)->delete();
    }

    public function archivePost(int $postId): void
{
    $post = $this->db->table('bulletin_posts')->where('post_id', $postId)->get()->getRow();
    if (!$post) return;

    $this->db->table('bulletin_archive')->insert([
        'original_post_id'    => $post->post_id,
        'title'               => $post->title,
        'content'             => $post->content,
        'image_path'          => $post->image_path,
        'target_audience'     => $post->target_audience,
        'is_pinned'           => $post->is_pinned,
        'starts_at'           => $post->starts_at,
        'ends_at'             => $post->ends_at,
        'created_by'          => $post->created_by,
        'original_created_at' => $post->created_at,
    ]);

    $this->db->table('bulletin_posts')->where('post_id', $postId)->delete();
}

}