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
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    
    // MAIN Folder
    $routes->get('dashboard', 'Main\Dashboard::index');

    // OPERATIONS Folder - Inventory
    $routes->group('inventory', ['namespace' => 'App\Controllers\Admin\Operations'], function($routes) {
        $routes->get('stock-management', 'Inventory::stock_management');
        $routes->get('adjustment-logs', 'Inventory::adjustment_logs');
        $routes->get('get-details/(:num)', 'Inventory::get_details/$1');
        $routes->get('get-log-details/(:num)', 'Inventory::get_log_details/$1');
        $routes->get('get-product/(:num)', 'Inventory::get_product/$1');
        $routes->get('delete-product/(:num)', 'Inventory::delete_product/$1');
        $routes->post('save-product', 'Inventory::save_product');
        $routes->post('adjust-stock', 'Inventory::adjust_stock');
        $routes->post('update-product', 'Inventory::update_product');
        $routes->post('update-info', 'Inventory::update_product_info');
        $routes->post('create-batch', 'Inventory::create_batch');
    });

    // OPERATIONS Folder - Procurement
    $routes->group('procurement', ['namespace' => 'App\Controllers\Admin\Operations'], function($routes) {
        $routes->get('suppliers', 'Procurement::suppliers');
        $routes->get('get-supplier-details/(:num)', 'Procurement::get_supplier_details/$1');
        $routes->post('save-supplier', 'Procurement::save_supplier'); 

        $routes->get('purchase-orders', 'Procurement::purchase_orders');
        $routes->get('run-predictive', 'Procurement::run_predictive_analysis');
        $routes->post('save-po', 'Procurement::save_po');
        $routes->get('approve-po/(:num)', 'Procurement::approve_po/$1');
        $routes->get('get-po-details/(:num)', 'Procurement::get_po_details/$1');

        $routes->get('goods-receipt', 'Procurement::goods_receipt');
        $routes->post('save-grr', 'Procurement::save_grr');
    });

    // OPERATIONS Folder - Sales
    $routes->group('sales', ['namespace' => 'App\Controllers\Admin\Operations'], function($routes) {
        $routes->get('institutional-clients', 'Sales::clients');
        $routes->get('get-client-details/(:num)', 'Sales::get_client_details/$1');
        $routes->post('save-client', 'Sales::save_client');
        $routes->post('save-order', 'Sales::save_order');
        
        $routes->get('sales-orders', 'Sales::orders');
        $routes->get('get-order-details/(:num)', 'Sales::get_order_details/$1');
        $routes->post('update-order-status', 'Sales::update_order_status');
        $routes->post('admin/sales/save-order', 'Sales::save_order');

        $routes->get('sales-returns', 'Sales::returns');
        $routes->get('get-return-order-items/(:num)', 'Sales::get_return_order_items/$1');
        $routes->get('get-return-details/(:num)', 'Sales::get_return_details/$1');
        $routes->get('approve-return/(:num)', 'Sales::approve_return/$1');
        $routes->get('reject-return/(:num)', 'Sales::reject_return/$1');
        $routes->post('process-return', 'Sales::process_return');

        $routes->get('pos', 'Sales::pos');
        $routes->post('pos/process', 'Sales::process_pos');
    });

    // STRATEGY Folder
    $routes->group('strategy', ['namespace' => 'App\Controllers\Admin\Strategy'], function($routes) {
        $routes->group('analytics', function($routes) {
            $routes->get('predictive-dss', 'Analytics::dss');
            $routes->get('get-forecast/(:num)', 'Analytics::get_forecast/$1');
            $routes->get('reports', 'Analytics::reports');
            $routes->get('export/(:any)/(:any)', 'Analytics::export/$1/$2');
        });

        $routes->get('compliance', 'Compliance::bir');
    });

    // MANAGEMENT Folder
    $routes->group('management', ['namespace' => 'App\Controllers\Admin\Management'], function($routes) {
        
        // Alerts & Tasks
        $routes->get('alerts-tasks', 'Alerts::index');
        $routes->post('alerts/save', 'Alerts::save'); // Handles both Create and Update
        $routes->get('alerts/edit/(:num)', 'Alerts::get_details/$1');
        $routes->get('alerts/delete/(:num)', 'Alerts::delete/$1');

        // Bulletin Board 
        $routes->get('bulletin-board', 'Bulletin::index');
        $routes->post('bulletin/save', 'Bulletin::save');
        $routes->get('bulletin/edit/(:num)', 'Bulletin::get_details/$1');
        $routes->get('bulletin/delete/(:num)', 'Bulletin::delete/$1');

        // User Management 
        $routes->get('user-management', 'Users::index');
        $routes->post('users/save', 'Users::save');
        $routes->get('users/edit/(:num)', 'Users::get_details/$1');
        $routes->get('users/delete/(:num)', 'Users::delete/$1');
        
        // ChatBot
        $routes->get('chatbot', 'Chatbot::index');
        $routes->post('chatbot/intent/save', 'Chatbot::save_intent');
        $routes->get('chatbot/intent/edit/(:num)', 'Chatbot::get_intent/$1');
        $routes->get('chatbot/intent/delete/(:num)', 'Chatbot::delete_intent/$1');
        $routes->get('chatbot/escalation/details/(:num)', 'Chatbot::get_escalation/$1');
        $routes->post('chatbot/escalation/resolve', 'Chatbot::resolve_escalation');

        // Customer Engagement CRUD
        $routes->get('customer-engagement', 'Engagement::index');
        $routes->get('reviews/approve/(:num)', 'Engagement::approve_review/$1');
        $routes->get('reviews/delete/(:num)', 'Engagement::delete_review/$1');
        $routes->get('suggestions/status/(:num)/(:any)', 'Engagement::update_suggestion/$1/$2');
    });

    


    
});
