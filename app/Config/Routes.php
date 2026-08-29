<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */


// --- SECURED PARTNER AREA (Suppliers & Clients) ---
$routes->group('client', ['namespace' => 'App\Controllers\Client', 'filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'Main\Dashboard::index'); 

    $routes->group('orders', ['namespace' => 'App\Controllers\Client\Orders'], function($routes) {
        $routes->get('browse', 'Products::index');
        $routes->get('place-order', 'Orders::place_order_view');
        $routes->post('save-order', 'Orders::save_order');
        $routes->get('my-orders', 'Orders::index');
        $routes->get('get-order-details/(:num)', 'Orders::get_order_details/$1');
    });

    $routes->group('account', ['namespace' => 'App\Controllers\Client\Account'], function($routes) {
        $routes->get('payment', 'Payment::index');
        $routes->post('process-payment', 'Payment::process');
        $routes->get('invoices', 'Invoices::index');
    });

    $routes->group('support', ['namespace' => 'App\Controllers\Client\Support'], function($routes) {
        $routes->get('chatbot', 'Chatbot::index');
        $routes->get('announcements', 'Announcements::index');
        $routes->get('profile', 'Profile::index');
        $routes->post('profile/update', 'Profile::update');
    });

});

$routes->group('supplier', ['namespace' => 'App\Controllers\Supplier', 'filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'Main\Dashboard::index');

    $routes->group('orders', ['namespace' => 'App\Controllers\Supplier\Orders'], function($routes) {
        $routes->get('inbox', 'PurchaseOrders::index');
        $routes->get('acknowledge/(:num)', 'PurchaseOrders::view_acknowledge/$1');
        $routes->post('process-acknowledge', 'PurchaseOrders::process_acknowledge'); // Fixed method name
        $routes->get('delivery', 'PurchaseOrders::delivery_updates');
        $routes->post('update-delivery', 'PurchaseOrders::update_delivery');
    });

    $routes->group('inventory', ['namespace' => 'App\Controllers\Supplier\Inventory'], function($routes) {
        $routes->get('catalog', 'Catalog::index');
        $routes->post('catalog/save', 'Catalog::save');
    });

    $routes->group('account', ['namespace' => 'App\Controllers\Supplier\Account'], function($routes) {
        $routes->get('scorecard', 'Account::scorecard');
        $routes->get('profile', 'Account::profile');
        $routes->post('profile/update', 'Account::update_profile');
    });
});



// --- AUTHENTICATION ---
// 1. Internal Portal (Admin and Staff)
$routes->get('portal', 'Auth\Internal::index'); 
$routes->post('auth/login/internal', 'Auth\Internal::login');
$routes->get('logout', 'Auth\Internal::logout');

// 2. Partner Gateway (Suppliers and Institutional Clients)
$routes->post('auth/login/external', 'Auth\External::login');

$routes->get('partner-gateway/register/client', 'Partners\Register::client_index');
$routes->post('register/client/step1', 'Partners\Register::client_step2');
$routes->post('register/client/step2', 'Partners\Register::client_step3');
$routes->post('register/client/submit', 'Partners\Register::client_submit');

$routes->get('partner-gateway/register/supplier', 'Partners\Register::supplier_index');
$routes->post('register/supplier/step1', 'Partners\Register::supplier_step2');
$routes->post('register/supplier/step2', 'Partners\Register::supplier_step3');
$routes->post('register/supplier/submit', 'Partners\Register::supplier_submit');


// --- WEBSITE ---
$routes->get('/', 'PublicSite\Main::index');
$routes->get('about', 'PublicSite\Main::about');
$routes->get('products', 'PublicSite\Main::products');
$routes->get('services', 'PublicSite\Main::services');
$routes->get('announcements', 'PublicSite\Main::announcements');
$routes->get('contact', 'PublicSite\Main::contact');
$routes->get('partner-gateway', 'Auth\External::index');


// --- SECURED ADMIN SECTION ---
// All routes inside this group are protected by the 'auth' filter
// The base namespace is set to App\Controllers\Admin
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'auth'], function($routes) {
    
    // 1. MAIN FOLDER
    $routes->get('dashboard', 'Main\Dashboard::index');

    // 2. OPERATIONS FOLDER - INVENTORY
    $routes->group('inventory', ['namespace' => 'App\Controllers\Admin\Operations'], function($routes) {
        $routes->get('stock-management', 'Inventory::stock_management');
        $routes->get('adjustment-logs', 'Inventory::adjustment_logs');

        $routes->get('get-stock-context/(:num)', 'Inventory::get_stock_context/$1');
        $routes->get('get-details/(:num)', 'Inventory::get_details/$1');
        $routes->get('get-log-details/(:num)', 'Inventory::get_log_details/$1');
        $routes->get('get-product/(:num)', 'Inventory::get_product/$1');

        $routes->get('get-education/(:num)', 'Inventory::get_education/$1');
        
        $routes->get('delete-product/(:num)', 'Inventory::delete_product/$1');
        $routes->post('save-product', 'Inventory::save_product');
        $routes->post('adjust-stock', 'Inventory::adjust_stock');
        $routes->post('update-product', 'Inventory::update_product');
        $routes->post('update-info', 'Inventory::update_product_info');
        $routes->post('create-batch', 'Inventory::create_batch');
    });

    // 2. OPERATIONS FOLDER - PROCUREMENT
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

    // 2. OPERATIONS FOLDER - SALES
    $routes->group('sales', ['namespace' => 'App\Controllers\Admin\Operations'], function($routes) {
        $routes->get('institutional-clients', 'Sales::clients');
        $routes->get('get-client-details/(:num)', 'Sales::get_client_details/$1');
        $routes->post('save-client', 'Sales::save_client');
        $routes->post('save-order', 'Sales::save_order'); // FIXED: Removed redundant URL segments
        $routes->get('sales-orders', 'Sales::orders');
        $routes->get('get-order-details/(:num)', 'Sales::get_order_details/$1');
        $routes->post('update-order-status', 'Sales::update_order_status');
        $routes->get('sales-returns', 'Sales::returns');
        $routes->get('get-return-order-items/(:num)', 'Sales::get_return_order_items/$1');
        $routes->get('get-return-details/(:num)', 'Sales::get_return_details/$1');
        $routes->get('approve-return/(:num)', 'Sales::approve_return/$1');
        $routes->get('reject-return/(:num)', 'Sales::reject_return/$1');
        $routes->post('process-return', 'Sales::process_return');
        $routes->get('pos', 'Sales::pos');
        $routes->get('get-product-pos/(:any)', 'Sales::get_product_pos/$1'); // Added missing POS search
        $routes->post('pos/process', 'Sales::process_pos');
    });

    // 3. STRATEGY FOLDER
    $routes->group('strategy', ['namespace' => 'App\Controllers\Admin\Strategy'], function($routes) {
        $routes->group('analytics', function($routes) {
            $routes->get('predictive-dss', 'Analytics::dss');
            $routes->get('get-forecast/(:num)', 'Analytics::get_forecast/$1');
            $routes->get('reports', 'Analytics::reports');
            $routes->get('export/(:any)/(:any)', 'Analytics::export/$1/$2');
        });
        $routes->get('compliance', 'Compliance::bir');
    });

    // 4. MANAGEMENT FOLDER
    $routes->group('management', ['namespace' => 'App\Controllers\Admin\Management'], function($routes) {
        // Alerts
        $routes->get('alerts-tasks', 'Alerts::index');
        $routes->post('alerts/save', 'Alerts::save');
        $routes->get('alerts/edit/(:num)', 'Alerts::get_details/$1');
        $routes->get('alerts/delete/(:num)', 'Alerts::delete/$1');
        $routes->get('alerts/resolve/(:num)', 'Alerts::resolve/$1');

        // Bulletin
        $routes->get('bulletin-board', 'Bulletin::index');
        $routes->post('bulletin/save', 'Bulletin::save');
        $routes->get('bulletin/edit/(:num)', 'Bulletin::get_details/$1');
        $routes->get('bulletin/delete/(:num)', 'Bulletin::delete/$1');

        // Users
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

        // Engagement
        $routes->get('customer-engagement', 'Engagement::index');
        $routes->get('reviews/approve/(:num)', 'Engagement::approve_review/$1');
        $routes->get('reviews/delete/(:num)', 'Engagement::delete_review/$1');
        $routes->get('suggestions/status/(:num)/(:any)', 'Engagement::update_suggestion/$1/$2');
    });
});

$routes->group('staff', ['namespace' => 'App\Controllers\Staff', 'filter' => 'auth'], function($routes) {
    
    // MAIN Folder
    $routes->get('dashboard', 'Main\Dashboard::index');

    // OPERATIONS Folder
    $routes->group('inventory', ['namespace' => 'App\Controllers\Staff\Inventory'], function($routes) {
        $routes->get('stock', 'Stock::index');
        $routes->get('logs', 'Logs::logs');
    });

    $routes->group('operations', ['namespace' => 'App\Controllers\Staff\Operations'], function($routes) {
        $routes->get('pos', 'Pos::index');
        $routes->get('sales-orders', 'SalesOrders::index');
        $routes->get('sales-returns', 'Returns::index');
        $routes->get('goods-receipt', 'GoodsReceipt::index');
    });

    $routes->group('info', ['namespace' => 'App\Controllers\Staff\Info'], function($routes) {
        $routes->get('alerts', 'Alerts::index');
        $routes->get('read-alert/(:num)', 'Alerts::mark_as_read/$1');
        $routes->get('dss', 'Dss::index');
        $routes->get('bulletin', 'Bulletin::index');
    });



});

