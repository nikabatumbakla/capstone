<?php

namespace App\Controllers\Supplier\Inventory;

use App\Controllers\BaseController;
use App\Models\Supplier\CatalogModel;

class Catalog extends BaseController
{
    protected $catalogModel;

    public function __construct()
    {
        $this->catalogModel = new CatalogModel();
    }

    public function index()
    {
        $supplierId = session()->get('supplier_id');
        $search = trim((string) ($this->request->getGet('search') ?? ''));
        $categoryFilter = $this->request->getGet('category');
        $page = (int) ($this->request->getGet('page') ?? 1);

        $result = $this->catalogModel->getCatalog($supplierId, $search, $categoryFilter, $page, 10);
        $kpis = $this->catalogModel->getKpis($supplierId);

        $data['catalog'] = $result['data'];
        $data['total_pages'] = $result['total_pages'];
        $data['current_page'] = $page;
        $data['search'] = $search;
        $data['category_filter'] = $categoryFilter;
        $data['total_items'] = $kpis['total_items'];
        $data['preferred_items'] = $kpis['preferred_items'];
        $data['avg_lead_time'] = $kpis['avg_lead_time'];

        $data['addable_products'] = $this->catalogModel->getAddableProducts($supplierId);
        $data['registered_categories'] = $this->catalogModel->getRegisteredCategoryNames($supplierId);

        $data['title'] = "My Product Catalog";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "catalog";
        return view('pages/supplier/inventory/catalog', $data);
    }

    public function add()
    {
        $supplierId = session()->get('supplier_id');
        $productId = (int) $this->request->getPost('product_id');

        $payload = [
            'unit_cost'         => (float) $this->request->getPost('unit_cost'),
            'minimum_order_qty' => (int) ($this->request->getPost('minimum_order_qty') ?: 1),
            'lead_time_days'    => (int) ($this->request->getPost('lead_time_days') ?: 7),
        ];

        if ($payload['unit_cost'] <= 0) {
            return redirect()->back()->withInput()->with('error', 'Please provide a valid unit cost.');
        }

        $result = $this->catalogModel->addToCatalog($supplierId, $productId, $payload);
        if (isset($result['error'])) {
            return redirect()->to('supplier/inventory/catalog')->with('error', $result['error']);
        }
        return redirect()->to('supplier/inventory/catalog')->with('success', 'Product added to your catalog.');
    }

    public function get_entry($catalogId)
    {
        $supplierId = session()->get('supplier_id');
        $entry = $this->catalogModel->getCatalogEntry((int) $catalogId, $supplierId);
        if (!$entry) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        return $this->response->setJSON($entry);
    }

    public function update()
    {
        $supplierId = session()->get('supplier_id');
        $catalogId = (int) $this->request->getPost('catalog_id');
        $unitCost = (float) $this->request->getPost('unit_cost');

        if ($unitCost <= 0) {
            return redirect()->to('supplier/inventory/catalog')->with('error', 'Please provide a valid unit cost.');
        }

        $payload = [
            'unit_cost'         => $unitCost,
            'minimum_order_qty' => (int) ($this->request->getPost('minimum_order_qty') ?: 1),
            'lead_time_days'    => (int) ($this->request->getPost('lead_time_days') ?: 7),
        ];

        $success = $this->catalogModel->updateCatalogEntry($catalogId, $supplierId, $payload);
        return redirect()->to('supplier/inventory/catalog')->with($success ? 'success' : 'error',
            $success ? 'Catalog entry updated.' : 'Unable to update — entry not found.');
    }

    public function delete($catalogId)
    {
        $supplierId = session()->get('supplier_id');
        $success = $this->catalogModel->removeCatalogEntry((int) $catalogId, $supplierId);
        return redirect()->to('supplier/inventory/catalog')->with($success ? 'success' : 'error',
            $success ? 'Product removed from your catalog.' : 'Unable to remove — entry not found.');
    }
}