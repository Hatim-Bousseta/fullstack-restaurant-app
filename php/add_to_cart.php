<?php
require_once 'db.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false, 
        'message' => 'Please login to add items to cart',
        'login_required' => true
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = (int)($_POST['item_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    // Validate
    if ($item_id <= 0) {
        $response['message'] = 'Invalid item';
    } elseif ($quantity <= 0 || $quantity > 20) {
        $response['message'] = 'Quantity must be between 1 and 20';
    } else {
        // Check if item exists
        $item = getMenuItem($item_id);
        
        if (!$item) {
            $response['message'] = 'Item not found';
        } else {
            // Add to cart
            if (addToCart($item_id, $quantity, $user_id)) {
                $cart_count = getCartCount($user_id);
                $cart_total = getCartTotal($user_id);
                
                $response = [
                    'success' => true,
                    'message' => 'Added ' . htmlspecialchars($item['name']) . ' to cart!',
                    'cart_count' => $cart_count,
                    'cart_total' => number_format($cart_total, 2),
                    'item_name' => $item['name']
                ];
            } else {
                $response['message'] = 'Failed to add item to cart';
            }
        }
    }
}

echo json_encode($response);
?>