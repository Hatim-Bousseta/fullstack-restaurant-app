<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login']);
    exit;
}

$user_id = $_SESSION['user_id'];
$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = (int)($_POST['cart_id'] ?? 0);
    
    if ($cart_id > 0 && removeFromCart($cart_id, $user_id)) {
        $response = [
            'success' => true,
            'message' => 'Item removed',
            'cart_count' => getCartCount($user_id),
            'cart_total' => number_format(getCartTotal($user_id), 2)
        ];
    }
}

echo json_encode($response);
?>