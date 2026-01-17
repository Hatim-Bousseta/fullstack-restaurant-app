<?php
require_once 'db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$user_id = $_SESSION['user_id'];
$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $item_id = $_POST['item_id'] ?? 0;
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    switch ($action) {
        case 'add':
            if ($item_id > 0 && $quantity > 0) {
                if (addToCart($item_id, $quantity, $user_id)) {
                    $response = [
                        'success' => true,
                        'message' => 'Item added to cart',
                        'cart_count' => getCartCount($user_id)
                    ];
                }
            }
            break;
            
        case 'get':
            $response = [
                'success' => true,
                'cart_items' => getCartItems($user_id),
                'cart_total' => getCartTotal($user_id),
                'cart_count' => getCartCount($user_id)
            ];
            break;
    }
}

echo json_encode($response);
?>