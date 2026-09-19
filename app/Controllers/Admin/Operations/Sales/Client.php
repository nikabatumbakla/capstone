<?php

namespace App\Controllers\Admin\Operations\Sales;

use App\Controllers\BaseController;

class Client extends BaseController
{
    public function clients()
    {
        $db = \Config\Database::connect();
        $request = \Config\Services::request();

        $search = trim((string) ($request->getGet('search') ?? ''));
        $type   = $request->getGet('type') ?? '';
        $status = $request->getGet('status') ?? '';

        $page    = (int) ($request->getGet('page') ?? 1);
        if ($page < 1) $page = 1;
        $perPage = 10;
        $offset  = ($page - 1) * $perPage;

        $baseQualified = function() use ($db) {
            return $db->table('institutional_clients as ic')
                ->join('users as u', 'u.user_id = ic.user_id')
                ->where('ic.is_active', 1)
                ->groupStart()
                    ->where('ic.contact_person IS NOT NULL', null, false)->where('ic.contact_person !=', '')
                    ->where('ic.phone IS NOT NULL', null, false)->where('ic.phone !=', '')
                    ->where('ic.address IS NOT NULL', null, false)->where('ic.address !=', '')
                ->groupEnd();
        };

        $applyFilters = function($builder) use ($search, $type, $status) {
            if ($search !== '') {
                $builder->groupStart()
                    ->like('ic.organization', $search)
                    ->orLike('ic.contact_person', $search)
                    ->groupEnd();
            }
            if ($type === 'hospital_clinic') {
                $builder->whereIn('ic.client_type', ['hospital', 'clinic']);
            } elseif ($type === 'lgu_sk') {
                $builder->whereIn('ic.client_type', ['lgu', 'sk']);
            } elseif ($type !== '') {
                $builder->where('ic.client_type', $type);
            }
            if ($status === 'unverified') {
                $builder->where('u.is_verified', 0);
            } elseif ($status === 'no_orders') {
                $builder->where("ic.client_id NOT IN (SELECT DISTINCT client_id FROM sales_orders)", null, false);
            }
            return $builder;
        };

        $data['count_total']      = $baseQualified()->countAllResults();
        $data['count_unverified'] = $baseQualified()->where('u.is_verified', 0)->countAllResults();
        $data['count_no_orders']  = $baseQualified()->where("ic.client_id NOT IN (SELECT DISTINCT client_id FROM sales_orders)", null, false)->countAllResults();
        $data['count_types']      = count(
            $db->table('institutional_clients')->select('client_type')->where('is_active', 1)->distinct()->get()->getResultArray()
        );

        $countBuilder = $baseQualified();
        $applyFilters($countBuilder);
        $totalRows = $countBuilder->countAllResults();

        $builder = $baseQualified();
        $builder->select('ic.*, u.email as login_email, u.is_verified');
        $applyFilters($builder);
        $builder->orderBy('ic.organization', 'ASC');
        $builder->limit($perPage, $offset);
        $data['clients'] = $builder->get()->getResultArray();

        $data['total_rows']   = $totalRows;
        $data['current_page'] = $page;
        $data['per_page']     = $perPage;
        $data['total_pages']  = max(1, (int) ceil($totalRows / $perPage));
        $data['search']       = $search;
        $data['type_filter']  = $type;
        $data['status_filter'] = $status;

        $data['categories'] = $db->table('categories')->orderBy('sort_order', 'ASC')->get()->getResultArray();
        $data['products'] = $db->table('products as p')
            ->select("p.product_id, p.name, p.unit, p.category_id, p.is_vat_exempt,
                (SELECT COALESCE(SUM(quantity_avail),0) FROM inventory_batches WHERE product_id = p.product_id) as total_stock,
                (SELECT ib.sell_price FROM inventory_batches ib WHERE ib.product_id = p.product_id AND ib.quantity_avail > 0 ORDER BY ib.expires_at ASC LIMIT 1) as latest_sell_price")
            ->where('p.is_active', 1)
            ->orderBy('p.name', 'ASC')
            ->get()->getResultArray();
        $rateRow = $db->table('store_settings')->where('setting_key', 'school_discount_rate')->get()->getRow();
        $data['school_discount_rate'] = $rateRow ? (float) $rateRow->setting_value : 10;

        $data['title'] = "Client Directory";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "sales";
        return view('pages/admin/operations/sales/institutional_clients', $data);
    }

    public function get_client_details($id)
    {
        $db = \Config\Database::connect();
        $client = $db->table('institutional_clients as ic')
            ->select('ic.*, u.email as login_email, u.is_verified')
            ->join('users as u', 'u.user_id = ic.user_id', 'left')
            ->where('ic.client_id', $id)
            ->get()->getRow();

        if (!$client) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not Found']);

        $orders = $db->table('sales_orders')
            ->where('client_id', $id)
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        return $this->response->setJSON(['client' => $client, 'orders' => $orders]);
    }

    public function get_client_history($id)
    {
        $db = \Config\Database::connect();
        $client = $db->table('institutional_clients')->where('client_id', $id)->get()->getRow();
        $orders = $db->table('sales_orders')->where('client_id', $id)->orderBy('created_at', 'DESC')->get()->getResultArray();

        return $this->response->setJSON(['client' => $client, 'orders' => $orders]);
    }
}