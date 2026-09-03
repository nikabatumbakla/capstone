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
        $reviewStatus = $this->request->getGet('review_status') ?: 'all';
        $reviewSearch = trim((string) ($this->request->getGet('review_search') ?? ''));
        $reviewPage = (int) ($this->request->getGet('review_page') ?? 1);

        $sugStatus = $this->request->getGet('sug_status') ?: 'all';
        $sugSearch = trim((string) ($this->request->getGet('sug_search') ?? ''));
        $sugPage = (int) ($this->request->getGet('sug_page') ?? 1);

        $productRatings = $this->engagementModel->getProductRatingSummary();
        $storeFeedback = $this->engagementModel->getStoreFeedbackSummary();

        $data['avg_product_rating'] = $productRatings['avg'];
        $data['total_product_reviews'] = $productRatings['total'];
        $data['star_breakdown'] = $productRatings['breakdown'];

        $data['avg_store_rating'] = $storeFeedback['avg'];
        $data['total_store_feedback'] = $storeFeedback['total'];

        $data['pending_suggestions'] = $this->engagementModel->getPendingSuggestionsCount();
        $data['recent_reviews'] = $this->engagementModel->getRecentReviews(3);
        $data['recent_suggestions'] = $this->engagementModel->getRecentSuggestions(3);

        $reviewResult = $this->engagementModel->getReviews($reviewStatus, $reviewSearch, $reviewPage, 10);
        $data['all_reviews'] = $reviewResult['data'];
        $data['review_total_pages'] = $reviewResult['total_pages'];
        $data['review_current_page'] = $reviewPage;
        $data['review_status'] = $reviewStatus;
        $data['review_search'] = $reviewSearch;

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

    public function approve_review($id)
    {
        $this->engagementModel->approveReview((int) $id);
        return redirect()->back()->with('success', 'Review published.');
    }

    public function delete_review($id)
    {
        $this->engagementModel->removeReview((int) $id);
        return redirect()->back()->with('success', 'Review removed.');
    }

    public function update_suggestion($id, $status)
    {
        $this->engagementModel->setSuggestionStatus((int) $id, $status, session()->get('user_id') ?? 1);
        return redirect()->back()->with('success', 'Suggestion status updated.');
    }
}