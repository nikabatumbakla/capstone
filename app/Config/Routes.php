<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Authentication
$routes->get('/', 'AuthController::index');
$routes->post('auth/login', 'AuthController::login');
$routes->get('auth/logout', 'AuthController::logout');

// Admin Dashboard Section
$routes->group('admin', function($routes) {
    // Dashboard
    $routes->get('dashboard', '\App\Controllers\Admin\Dashboard::index');

    // Inventory Module
    $routes->group('inventory', function($routes) {
    $routes->get('stock-management', '\App\Controllers\Admin\Inventory::stock_management');
    $routes->get('adjustment-logs', '\App\Controllers\Admin\Inventory::adjustment_logs');

    // AJAX data endpoints (GET)
    $routes->get('get-details/(:num)', '\App\Controllers\Admin\Inventory::get_details/$1');
    $routes->get('get-log-details/(:num)', '\App\Controllers\Admin\Inventory::get_log_details/$1');

    // Form processing endpoints (POST)
    $routes->post('save-product', '\App\Controllers\Admin\Inventory::save_product');
    $routes->post('adjust-stock', '\App\Controllers\Admin\Inventory::adjust_stock');
    $routes->post('update-product', '\App\Controllers\Admin\Inventory::update_product');
    $routes->post('update-info', '\App\Controllers\Admin\Inventory::update_product_info');

    $routes->get('get-product/(:num)', '\App\Controllers\Admin\Inventory::get_product/$1');

    // Delete (GET link, simple confirm-and-go)
    $routes->get('delete-product/(:num)', '\App\Controllers\Admin\Inventory::delete_product/$1');

    $routes->post('create-batch', '\App\Controllers\Admin\Inventory::create_batch');
});

    // Procurement Module
    $routes->group('procurement', function($routes) {
        $routes->get('suppliers', '\App\Controllers\Admin\Procurement::suppliers');
        $routes->get('get-supplier-details/(:num)', '\App\Controllers\Admin\Procurement::get_supplier_details/$1');
        $routes->post('save-supplier', '\App\Controllers\Admin\Procurement::save_supplier'); 

        $routes->get('purchase-orders', '\App\Controllers\Admin\Procurement::purchase_orders');
        $routes->get('run-predictive', '\App\Controllers\Admin\Procurement::run_predictive_analysis'); // Missing Route Fixed
        $routes->post('save-po', '\App\Controllers\Admin\Procurement::save_po');
        $routes->get('approve-po/(:num)', '\App\Controllers\Admin\Procurement::approve_po/$1');
        $routes->get('get-po-details/(:num)', '\App\Controllers\Admin\Procurement::get_po_details/$1');

        $routes->get('goods-receipt', '\App\Controllers\Admin\Procurement::goods_receipt');
        $routes->post('save-grr', '\App\Controllers\Admin\Procurement::save_grr');
    });

    // Sales Module
    $routes->group('sales', function($routes) {
        $routes->get('institutional-clients', '\App\Controllers\Admin\Sales::clients');
        $routes->post('save-client', '\App\Controllers\Admin\Sales::save_client');
        $routes->get('get-client-history/(:num)', '\App\Controllers\Admin\Sales::get_client_history/$1');
        $routes->post('save-order', '\App\Controllers\Admin\Sales::save_order');

        $routes->get('sales-orders', '\App\Controllers\Admin\Sales::orders');
        $routes->get('get-order-details/(:num)', '\App\Controllers\Admin\Sales::get_order_details/$1');
        $routes->post('update-order-status', '\App\Controllers\Admin\Sales::update_order_status');

        $routes->get('sales-returns', '\App\Controllers\Admin\Sales::returns');
        $routes->post('sales-returns/process', '\App\Controllers\Admin\Sales::process_return');
        $routes->get('get-transaction-items/(:any)', '\App\Controllers\Admin\Sales::get_transaction_items/$1');

        $routes->get('pos', '\App\Controllers\Admin\Sales::pos');
        $routes->post('pos/process', '\App\Controllers\Admin\Sales::process_pos');

    });

    // Strategy & System
    $routes->get('analytics/predictive-dss', '\App\Controllers\Admin\Analytics::dss');
    $routes->get('compliance/bir', '\App\Controllers\Admin\Compliance::bir');
    $routes->get('system/user-management', '\App\Controllers\Admin\System::users');
});
