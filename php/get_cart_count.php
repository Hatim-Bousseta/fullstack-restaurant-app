<?php
require_once 'db.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$response = ['success' => false, 'cart_count' => 0];

if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $cart_count = getCartCount($user_id);
    
    $response = [
        'success' => true,
        'cart_count' => $cart_count,
        'cart_total' => number_format(getCartTotal($user_id), 2)
    ];
} else {
    $response['message'] = 'Not logged in';
}

echo json_encode($response);
?>