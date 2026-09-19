<?php

namespace App\Models\Admin\Management\PublicSite;

use CodeIgniter\Model;

class SiteContentModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getTestimonials(): array
    {
        return $this->db->table('client_testimonials')->orderBy('is_published', 'ASC')->orderBy('created_at', 'DESC')->get()->getResultArray();
    }

    public function togglePublish(int $id, bool $publish): void
    {
        $this->db->table('client_testimonials')->where('testimonial_id', $id)->update(['is_published' => $publish ? 1 : 0]);
    }

    public function updateSortOrder(int $id, int $sortOrder): void
    {
        $this->db->table('client_testimonials')->where('testimonial_id', $id)->update(['sort_order' => $sortOrder]);
    }

    public function deleteTestimonial(int $id): void
    {
        $this->db->table('client_testimonials')->where('testimonial_id', $id)->delete();
    }

    public function getHero()
    {
        return $this->db->table('site_hero_content')->get()->getRow();
    }

    public function saveHero(array $data): void
    {
        $existing = $this->getHero();
        if ($existing) $this->db->table('site_hero_content')->where('id', $existing->id)->update($data);
        else $this->db->table('site_hero_content')->insert($data);
    }

    public function getWhyUsCards(bool $activeOnly = false): array
    {
        $b = $this->db->table('site_why_us_cards');
        if ($activeOnly) $b->where('is_active', 1);
        return $b->orderBy('sort_order', 'ASC')->get()->getResultArray();
    }

    public function getWhyUsCard(int $id)
    {
        return $this->db->table('site_why_us_cards')->where('card_id', $id)->get()->getRow();
    }

    public function saveWhyUsCard(array $data, ?int $id = null): void
    {
        $payload = [
            'icon' => $data['icon'], 'title' => $data['title'], 'description' => $data['description'],
            'sort_order' => (int) ($data['sort_order'] ?? 0), 'is_active' => isset($data['is_active']) ? 1 : 0,
        ];
        if ($id) $this->db->table('site_why_us_cards')->where('card_id', $id)->update($payload);
        else $this->db->table('site_why_us_cards')->insert($payload);
    }

    public function deleteWhyUsCard(int $id): void
    {
        $this->db->table('site_why_us_cards')->where('card_id', $id)->delete();
    }

    public function getServiceCards(bool $activeOnly = false): array
    {
        $b = $this->db->table('site_service_cards');
        if ($activeOnly) $b->where('is_active', 1);
        return $b->orderBy('sort_order', 'ASC')->get()->getResultArray();
    }

    public function getServiceCard(int $id)
    {
        return $this->db->table('site_service_cards')->where('service_id', $id)->get()->getRow();
    }

    public function saveServiceCard(array $data, ?int $id = null): void
    {
        $payload = [
            'icon' => $data['icon'], 'title' => $data['title'], 'description' => $data['description'],
            'feature_list' => trim($data['feature_list'] ?? ''), 'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => isset($data['is_active']) ? 1 : 0,
        ];
        if ($id) $this->db->table('site_service_cards')->where('service_id', $id)->update($payload);
        else $this->db->table('site_service_cards')->insert($payload);
    }

    public function deleteServiceCard(int $id): void
    {
        $this->db->table('site_service_cards')->where('service_id', $id)->delete();
    }

    public function getAboutContent()
{
    return $this->db->table('site_about_content')->get()->getRow();
}

public function saveAboutContent(array $data): void
{
    $existing = $this->getAboutContent();
    if ($existing) $this->db->table('site_about_content')->where('id', $existing->id)->update($data);
    else $this->db->table('site_about_content')->insert($data);
}

public function getTeamMembers(bool $activeOnly = false): array
{
    $b = $this->db->table('site_team_members');
    if ($activeOnly) $b->where('is_active', 1);
    return $b->orderBy('sort_order', 'ASC')->get()->getResultArray();
}

public function getTeamMember(int $id)
{
    return $this->db->table('site_team_members')->where('member_id', $id)->get()->getRow();
}

public function saveTeamMember(array $data, ?int $id = null, ?string $photoPath = null): void
{
    $payload = [
        'name' => $data['name'], 'role' => $data['role'],
        'sort_order' => (int) ($data['sort_order'] ?? 0), 'is_active' => isset($data['is_active']) ? 1 : 0,
    ];
    if ($photoPath) $payload['photo_path'] = $photoPath;

    if ($id) $this->db->table('site_team_members')->where('member_id', $id)->update($payload);
    else $this->db->table('site_team_members')->insert($payload);
}

public function deleteTeamMember(int $id): void
{
    $this->db->table('site_team_members')->where('member_id', $id)->delete();
}

public function getContactInfo()
{
    return $this->db->table('site_contact_info')->get()->getRow();
}

public function saveContactInfo(array $data): void
{
    $existing = $this->getContactInfo();
    if ($existing) $this->db->table('site_contact_info')->where('id', $existing->id)->update($data);
    else $this->db->table('site_contact_info')->insert($data);
}

}