<?php

namespace App\Controllers\Admin\Management;

use App\Controllers\BaseController;
use App\Models\Admin\Management\EngagementModel;

class Engagement extends BaseController
{
    protected $engagementModel;

    public function __construct()
    {
        $this->engagementModel = new EngagementModel();
    }

    public function index()
    {
        $sugStatus = $this->request->getGet('sug_status') ?: 'all';
        $sugSearch = trim((string) ($this->request->getGet('sug_search') ?? ''));
        $sugPage = (int) ($this->request->getGet('sug_page') ?? 1);

        $storeRatings = $this->engagementModel->getStoreRatingSummary();
        $data['avg_store_rating'] = $storeRatings['avg'];
        $data['total_store_ratings'] = $storeRatings['total'];
        $data['star_breakdown'] = $storeRatings['breakdown'];

        $data['pending_suggestions'] = $this->engagementModel->getPendingSuggestionsCount();
        $data['recent_store_ratings'] = $this->engagementModel->getRecentStoreRatings(5);
        $data['recent_suggestions'] = $this->engagementModel->getRecentSuggestions(3);

        $sugResult = $this->engagementModel->getSuggestions($sugStatus, $sugSearch, $sugPage, 10);
        $data['all_suggestions'] = $sugResult['data'];
        $data['sug_total_pages'] = $sugResult['total_pages'];
        $data['sug_current_page'] = $sugPage;
        $data['sug_status'] = $sugStatus;
        $data['sug_search'] = $sugSearch;

        $data['title'] = "Customer Engagement";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "engagement";
        return view('pages/admin/management/customer_engagement', $data);
    }

    public function update_suggestion($id, $status)
    {
        $this->engagementModel->setSuggestionStatus((int) $id, $status, session()->get('user_id') ?? 1);
        return redirect()->back()->with('success', 'Suggestion status updated.');
    }
}