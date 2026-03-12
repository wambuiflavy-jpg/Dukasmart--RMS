<?php
// config/session_storage.php
function addCustomerToSession($customer_data) {
    session_start();
    
    if (!isset($_SESSION['customers'])) {
        $_SESSION['customers'] = [];
    }
    
    // Generate ID
    $customer_id = "CUST-" . date('Ymd') . '-' . str_pad(count($_SESSION['customers']) + 1, 3, '0', STR_PAD_LEFT);
    $customer_data['id'] = $customer_id;
    $customer_data['created_at'] = date('Y-m-d H:i:s');
    $customer_data['total_spent'] = 0;
    $customer_data['loyalty_points'] = 0;
    
    $_SESSION['customers'][] = $customer_data;
    
    return $customer_id;
}

function getAllCustomers() {
    session_start();
    return $_SESSION['customers'] ?? [];
}
?>