<?php
require_once 'db.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$response = ['success' => false, 'items' => []];

if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $cart_items = getCartItems($user_id);
    
    // Format items for JSON
    $items = [];
    foreach ($cart_items as $item) {
        $items[] = [
            'cart_id' => $item['cart_id'],
            'item_id' => $item['item_id'],
            'name' => $item['name'],
            'price' => (float)$item['price'],
            'quantity' => (int)$item['quantity'],
            'total' => (float)$item['price'] * (int)$item['quantity'],
            'image' => $item['image_path'] ?? 'default-food.jpg'
        ];
    }
    
    $response = [
        'success' => true,
        'items' => $items,
        'cart_count' => getCartCount($user_id),
        'cart_total' => number_format(getCartTotal($user_id), 2)
    ];
}

echo json_encode($response);
?>