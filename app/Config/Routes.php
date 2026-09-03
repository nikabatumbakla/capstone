<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */


// --- SECURED PARTNER AREA (Suppliers & Clients) ---
$routes->group('client', ['namespace' => 'App\Controllers\Client', 'filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'Main\Dashboard::index'); 
    $routes->post('chatbot/ask', 'ChatbotWidget::ask');
    $routes->get('chatbot/history', 'ChatbotWidget::history');

    $routes->group('orders', ['namespace' => 'App\Controllers\Client\Orders'], function($routes) {
        $routes->get('browse', 'Products::index');
        $routes->post('add-to-cart', 'Products::add_to_cart');

        $routes->get('my-orders', 'Orders::index');
        $routes->get('get-order-details/(:num)', 'Orders::get_order_details/$1');
        $routes->get('place-order', 'Orders::place_order_view');
        $routes->post('update-cart-qty', 'Orders::update_cart_qty');
        $routes->get('remove-from-cart/(:num)', 'Orders::remove_from_cart/$1');
        $routes->post('save-order', 'Orders::save_order');
        $routes->get('get-product-details/(:num)', 'Products::get_product_details/$1');
    });
    $routes->group('account', ['namespace' => 'App\Controllers\Client\Account'], function($routes) {
        $routes->get('invoices', 'Invoices::index');
        $routes->get('invoices', 'Invoices::index');
        $routes->get('invoices/get-details/(:num)', 'Invoices::get_invoice_details/$1');
        $routes->post('invoices/submit-payment', 'Invoices::submit_payment');
    });
    $routes->group('support', ['namespace' => 'App\Controllers\Client\Support'], function($routes) {
        $routes->get('chatbot', 'Chatbot::index');
        $routes->get('announcements', 'Announcements::index');
        $routes->get('profile', 'Profile::index');
        $routes->post('profile/update', 'Profile::update');
        $routes->post('profile/update', 'Profile::update');
    });
});

$routes->group('supplier', ['namespace' => 'App\Controllers\Supplier', 'filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'Main\Dashboard::index');

    $routes->group('orders', ['namespace' => 'App\Controllers\Supplier\Orders'], function($routes) {
        $routes->get('inbox', 'PurchaseOrders::index');
        $routes->get('get-po-details/(:num)', 'PurchaseOrders::get_po_details/$1');
        $routes->post('process-acknowledge', 'PurchaseOrders::process_acknowledge');
        $routes->post('update-delivery', 'PurchaseOrders::update_delivery');
        $routes->get('delivery', 'PurchaseOrders::delivery');
    });

    $routes->group('inventory', ['namespace' => 'App\Controllers\Supplier\Inventory'], function($routes) {
        $routes->get('catalog', 'Catalog::index');
        $routes->post('catalog/save', 'Catalog::save');
        $routes->post('catalog/add', 'Catalog::add');
        $routes->get('catalog/get-entry/(:num)', 'Catalog::get_entry/$1');
        $routes->post('catalog/update', 'Catalog::update');
        $routes->get('catalog/delete/(:num)', 'Catalog::delete/$1');
    });

    $routes->group('account', ['namespace' => 'App\Controllers\Supplier\Account'], function($routes) {
        $routes->get('scorecard', 'Account::scorecard');
        $routes->get('profile', 'Account::profile');
        $routes->post('profile/update', 'Account::update_profile');
    });
});

// POS for Admin and Staff runs as its own full-screen module, not nested in the shared admin layout
$routes->get('admin/pos-terminal', 'Admin\Operations\Sales::pos', ['filter' => 'auth']);
$routes->get('staff/operations/pos', 'Admin\Operations\Sales::pos', ['filter' => 'auth']);

// --- AUTHENTICATION ---
$routes->get('portal', 'Auth\Internal::index'); 
$routes->post('auth/login/internal', 'Auth\Internal::login');
$routes->get('logout', 'Auth\Internal::logout');

$routes->get('test-email', 'Auth\Internal::test_email');
$routes->post('auth/forgot-password/send-internal', 'Auth\Internal::forgot_password_send');
$routes->post('auth/forgot-password/verify-internal', 'Auth\Internal::forgot_password_verify');
$routes->post('auth/forgot-password/send-external', 'Auth\External::forgot_password_send');
$routes->post('auth/forgot-password/verify-external', 'Auth\External::forgot_password_verify');

$routes->post('auth/login/external', 'Auth\External::login');

$routes->get('partner-gateway/register/client', 'Partners\Register::client_index');
$routes->post('register/client/step1', 'Partners\Register::client_step2');
$routes->post('register/client/step2', 'Partners\Register::client_step3');
$routes->post('register/client/submit', 'Partners\Register::client_submit');

$routes->get('partner-gateway/register/supplier', 'Partners\Register::supplier_index');
$routes->post('register/supplier/step1', 'Partners\Register::supplier_save_step1');
$routes->get('register/supplier/step2', 'Partners\Register::supplier_step2_view');
$routes->post('register/supplier/step2', 'Partners\Register::supplier_save_step2');
$routes->get('register/supplier/step3', 'Partners\Register::supplier_step3_view');
$routes->post('register/supplier/submit', 'Partners\Register::supplier_submit');


// --- WEBSITE ---
$routes->get('/', 'PublicSite\Main::index');
$routes->get('about', 'PublicSite\Main::about');
$routes->get('products', 'PublicSite\Main::products');
$routes->get('services', 'PublicSite\Main::services');
$routes->get('announcements', 'PublicSite\Main::announcements');
$routes->get('contact', 'PublicSite\Main::contact');
$routes->get('partner-gateway', 'Auth\External::index');
$routes->get('customer/info', 'PublicSite\CustomerInfo::index');
$routes->post('customer/info/save', 'PublicSite\CustomerInfo::save');


// --- SECURED ADMIN SECTION ---
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
        $routes->get('get-supplier-products/(:num)', 'Procurement::get_supplier_products/$1');
        $routes->post('save-supplier', 'Procurement::save_supplier'); 

        $routes->get('purchase-orders', 'Procurement::purchase_orders');
        $routes->post('save-po', 'Procurement::save_po');

        $routes->get('approve-po/(:num)', 'Procurement::approve_po/$1');
        $routes->get('reject-po/(:num)', 'Procurement::reject_po/$1');
        $routes->get('get-po-details/(:num)', 'Procurement::get_po_details/$1');

        $routes->get('goods-receipt', 'Procurement::goods_receipt');
        $routes->post('save-grr', 'Procurement::save_grr');

    });

    // 2. OPERATIONS FOLDER - SALES
    $routes->group('sales', ['namespace' => 'App\Controllers\Admin\Operations'], function($routes) {
        $routes->get('institutional-clients', 'Sales::clients');
        $routes->get('get-client-details/(:num)', 'Sales::get_client_details/$1');
        
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
        $routes->post('confirm-payment', 'Sales::confirm_payment');

        $routes->get('supplier-returns', 'Sales::supplier_returns');
        $routes->get('get-po-items-for-return/(:num)', 'Sales::get_po_items_for_return/$1');
        $routes->post('save-supplier-return', 'Sales::save_supplier_return');
        $routes->get('approve-supplier-return/(:num)', 'Sales::approve_supplier_return/$1');
        $routes->get('reject-supplier-return/(:num)', 'Sales::reject_supplier_return/$1');
        $routes->get('get-supplier-return-details/(:num)', 'Sales::get_supplier_return_details/$1');

        $routes->get('get-product-pos/(:any)', 'Sales::get_product_pos/$1'); // Added missing POS search
        $routes->post('pos/process', 'Sales::process_pos');
    });

    // 3. STRATEGY FOLDER
    $routes->group('strategy', ['namespace' => 'App\Controllers\Admin\Strategy'], function($routes) {
        $routes->group('analytics', function($routes) {
            $routes->get('predictive-dss', 'Analytics::dss');
            $routes->get('get-forecast/(:num)', 'Analytics::get_forecast/$1');

            $routes->get('get-supplier-report', 'Analytics::get_supplier_report');
            $routes->get('get-low-performing-report', 'Analytics::get_low_performing_report');
            $routes->get('get-products-by-category/(:num)', 'Analytics::get_products_by_category/$1');
            $routes->get('get-overall-trend', 'Analytics::get_overall_trend');

            $routes->get('reports', 'Analytics::reports');
            $routes->get('export/(:any)/(:any)', 'Analytics::export/$1/$2');
            $routes->get('get-movement-details/(:num)', 'Analytics::get_movement_details/$1');
        });

        $routes->get('compliance', 'Compliance::bir');
        $routes->get('compliance/get-journal/(:any)', 'Compliance::get_journal/$1');
        $routes->get('compliance/export-2550m', 'Compliance::export_2550m');
        $routes->get('compliance/get-vat-sales-book', 'Strategy\Compliance::get_vat_sales_book');
        $routes->get('compliance/get-cash-receipts', 'Strategy\Compliance::get_cash_receipts_journal');

    });

    // 4. MANAGEMENT FOLDER
    $routes->group('management', ['namespace' => 'App\Controllers\Admin\Management'], function($routes) {
        // Alerts
        $routes->get('alerts-tasks', 'Alerts::index');
        $routes->post('alerts/save', 'Alerts::save');
        $routes->get('alerts/edit/(:num)', 'Alerts::get_details/$1');
        $routes->get('alerts/delete/(:num)', 'Alerts::delete/$1');
        $routes->get('alerts/resolve/(:num)', 'Alerts::resolve/$1');
        $routes->get('alerts/header-notifications', 'Alerts::header_notifications');

        // Bulletin
        $routes->get('bulletin-board', 'Bulletin::index');
        $routes->post('bulletin/save', 'Bulletin::save');
        $routes->get('bulletin/edit/(:num)', 'Bulletin::get_details/$1');
        $routes->get('bulletin/delete/(:num)', 'Bulletin::delete/$1');
        $routes->get('bulletin/get-archive', 'Bulletin::get_archive');
        $routes->post('bulletin/repost', 'Bulletin::repost');
        $routes->get('bulletin/delete-archived/(:num)', 'Bulletin::delete_archived/$1');

        // Users
        $routes->get('user-management', 'Users::index');
        $routes->get('users/edit/(:num)', 'Users::get_details/$1');
        $routes->get('users/delete/(:num)', 'Users::delete/$1');
        $routes->post('users/create', 'Users::create');
        $routes->post('users/update-access', 'Users::updateAccess');
        $routes->get('users/hard-delete/(:num)', 'Users::hard_delete/$1');
        $routes->get('my-profile/get', 'Users::get_my_profile');
        $routes->post('my-profile/update', 'Users::update_my_profile');
        $routes->get('users/pending-applications', 'Users::pending_applications');
        $routes->get('users/approve-application/(:num)', 'Users::approve_application/$1');
        $routes->get('users/reject-application/(:num)', 'Users::reject_application/$1');

        // ChatBot
        $routes->get('chatbot', 'Chatbot::index');
        $routes->post('chatbot/intent/save', 'Chatbot::save_intent');
        $routes->get('chatbot/intent/edit/(:num)', 'Chatbot::get_intent/$1');
        $routes->get('chatbot/intent/delete/(:num)', 'Chatbot::delete_intent/$1');
        $routes->get('chatbot/escalation/details/(:num)', 'Chatbot::get_escalation/$1');

        $routes->post('chatbot/escalation/reply', 'Chatbot::reply_escalation');
        $routes->get('chatbot/escalation/resolve/(:num)', 'Chatbot::resolve_escalation/$1');
        $routes->get('chatbot/intents-data', 'Chatbot::intents_data');
        $routes->get('chatbot/escalations-data', 'Chatbot::escalations_data');

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

    $routes->get('main/my-profile/get', 'Main\ProfileController::get_my_profile');
    $routes->post('main/my-profile/update', 'Main\ProfileController::update_my_profile');

    // OPERATIONS Folder
    $routes->group('inventory', ['namespace' => 'App\Controllers\Staff\Inventory'], function($routes) {
        $routes->get('stock', 'Stock::index');
        $routes->get('get-details/(:num)', 'Stock::get_details/$1');

        $routes->post('create-batch', 'Stock::create_batch');
        $routes->get('get-product-info/(:num)', 'Stock::get_product_info/$1');

        $routes->post('adjust_stock', 'Stock::adjust_stock');
        $routes->get('adjustment-logs', 'Logs::logs');
        $routes->get('logs', 'Logs::logs');
    });

    $routes->group('operations', ['namespace' => 'App\Controllers\Staff\Operations'], function($routes) {
        $routes->get('sales-orders', 'SalesOrders::index');
        $routes->get('get-order-details/(:num)', 'SalesOrders::get_details/$1');
        $routes->post('update-order-status', 'SalesOrders::update_status');

        $routes->get('sales-returns', 'Returns::index');
        $routes->get('get-return-order-items/(:num)', 'Returns::get_order_items/$1');
        $routes->post('process-return', 'Returns::process_return');
        $routes->post('confirm-payment', 'SalesOrders::confirm_payment');

        $routes->get('goods-receipt', 'GoodsReceipt::index');
        $routes->get('get-po-items/(:num)', 'GoodsReceipt::get_po_items/$1');
        $routes->post('save-grr', 'GoodsReceipt::save_grr');
    });

    $routes->group('info', ['namespace' => 'App\Controllers\Staff\Info'], function($routes) {
        $routes->get('alerts', 'Alerts::index');
        $routes->get('complete-task/(:num)', 'Alerts::complete_task/$1');
        $routes->get('alerts/header-notifications', 'Alerts::header_notifications');

        $routes->get('read-alert/(:num)', 'Alerts::mark_as_read/$1');
        $routes->get('bulletin', 'Bulletin::index');

        $routes->get('support-queue', 'ChatbotEscalations::index');
        $routes->get('support-queue/get-details/(:num)', 'ChatbotEscalations::get_details/$1');
        $routes->get('support-queue/claim/(:num)', 'ChatbotEscalations::claim/$1');
        $routes->post('support-queue/reply', 'ChatbotEscalations::reply');
        $routes->get('support-queue/resolve/(:num)', 'ChatbotEscalations::resolve/$1');

    });

});

