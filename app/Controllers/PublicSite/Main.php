<?php

namespace App\Controllers\PublicSite;

use App\Controllers\BaseController;
use App\Models\PublicSite\PublicSiteModel;

class Main extends BaseController
{
    protected $siteModel;

    public function __construct()
    {
        $this->siteModel = new PublicSiteModel();
    }

    public function index()
    {
        $data['categories'] = $this->siteModel->getCategories();
        $data['featured'] = $this->siteModel->getFeaturedProducts(6);

        $data['title'] = 'Robin Rose Trading – Your Ultimate Healthcare Partner';
        $data['active_nav'] = 'home';
        return view('public_site/pages/home', $data);
    }

    public function about()
    {
        $data['title'] = 'About Us - Robin Rose Trading';
        $data['active_nav'] = 'about';
        return view('public_site/pages/about', $data);
    }

    public function products()
    {
        $catSlug = $this->request->getGet('cat') ?: '';
        $search = trim((string) ($this->request->getGet('search') ?? ''));
        $page = (int) ($this->request->getGet('page') ?? 1);

        $result = $this->siteModel->getProducts($catSlug, $search, $page, 12);

        $data['categories'] = $this->siteModel->getCategories();
        $data['products'] = $result['data'];
        $data['total_pages'] = $result['total_pages'];
        $data['current_page'] = $page;
        $data['search'] = $search;

        $data['title'] = 'Product Catalog - Robin Rose Trading';
        $data['active_nav'] = 'products';
        $data['active_cat'] = $catSlug ?: 'all';
        return view('public_site/pages/products', $data);
    }

    public function services()
    {
        $data['title'] = 'Services - Robin Rose Trading';
        $data['active_nav'] = 'services';
        return view('public_site/pages/services', $data);
    }

    public function announcements()
    {
        $page = (int) ($this->request->getGet('page') ?? 1);
        $result = $this->siteModel->getAnnouncements($page, 6);

        $data['announcements'] = $result['data'];
        $data['total_pages'] = $result['total_pages'];
        $data['current_page'] = $page;

        $data['title'] = 'Announcements - Robin Rose Trading';
        $data['active_nav'] = 'announcements';
        return view('public_site/pages/announcements', $data);
    }

    public function contact()
    {
        $data['title'] = 'Contact Us - Robin Rose Trading';
        $data['active_nav'] = 'contact';
        return view('public_site/pages/contact', $data);
    }
}