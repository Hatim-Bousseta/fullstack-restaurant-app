 <?php
require_once '../php/db.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update':
            $cart_id = $_POST['cart_id'] ?? 0;
            $quantity = (int)($_POST['quantity'] ?? 1);
            
            if ($cart_id > 0 && $quantity > 0) {
                if (updateCartItem($cart_id, $quantity, $user_id)) {
                    $message = 'Cart updated successfully!';
                } else {
                    $error = 'Failed to update cart item';
                }
            }
            break;
            
        case 'remove':
            $cart_id = $_POST['cart_id'] ?? 0;
            
            if ($cart_id > 0 && removeFromCart($cart_id, $user_id)) {
                $message = 'Item removed from cart!';
            } else {
                $error = 'Failed to remove item';
            }
            break;
            
        case 'clear':
            if (clearCart($user_id)) {
                $message = 'Cart cleared successfully!';
            } else {
                $error = 'Failed to clear cart';
            }
            break;
            
        case 'checkout':
            // Get delivery address
            $delivery_address = $_POST['delivery_address'] ?? '';
            
            if (empty($delivery_address)) {
                // Try to get user's saved address
                $user = fetchOne("SELECT address FROM users WHERE id = :id", [':id' => $user_id]);
                $delivery_address = $user ? $user['address'] : '';
            }
            
            if (empty($delivery_address)) {
                $error = 'Please provide a delivery address';
            } else {
                // Simple order creation
                $cart_items = getCartItems($user_id);
                $cart_total = getCartTotal($user_id);
                
                if (empty($cart_items)) {
                    $error = 'Your cart is empty';
                } else {
                    // Create order
                    $sql = "INSERT INTO orders (user_id, total_price, delivery_address, status) 
                            VALUES (:user_id, :total_price, :delivery_address, 'preparing')";
                    
                    if (executeQuery($sql, [
                        ':user_id' => $user_id,
                        ':total_price' => $cart_total,
                        ':delivery_address' => $delivery_address
                    ])) {
                        // Get the order ID
                        $order = fetchOne("SELECT id FROM orders WHERE user_id = :user_id ORDER BY order_date DESC", 
                                         [':user_id' => $user_id]);
                        
                        if ($order) {
                            // Save order items
                            foreach ($cart_items as $item) {
                                $item_total = $item['price'] * $item['quantity'];
                                
                                $sql = "INSERT INTO order_items (order_id, item_id, item_name, quantity, price, total) 
                                        VALUES (:order_id, :item_id, :item_name, :quantity, :price, :total)";
                                
                                executeQuery($sql, [
                                    ':order_id' => $order['id'],
                                    ':item_id' => $item['item_id'],
                                    ':item_name' => $item['name'],
                                    ':quantity' => $item['quantity'],
                                    ':price' => $item['price'],
                                    ':total' => $item_total
                                ]);
                            }
                            
                            // Clear cart
                            clearCart($user_id);
                            
                            // Redirect to success page
                            header('Location: order_success.php');
                            exit;
                        }
                    } else {
                        $error = 'Failed to place order. Please try again.';
                    }
                }
            }
            break;
    }
}

// Get cart items
$cart_items = getCartItems($user_id);
$cart_total = getCartTotal($user_id);
$cart_count = getCartCount($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - FoodSHOP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <style>
        .cart-section {
            padding: 100px 20px 50px;
            background: var(--light-pink-color);
            min-height: 100vh;
        }

        .cart-container {
            max-width: var(--site-max-width);
            margin: 0 auto;
        }

        .cart-header {
            background: var(--white-color);
            border-radius: 20px;
            padding: 30px 40px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .cart-header h2 {
            color: var(--primary-color);
            font-size: var(--font-size-xl);
            margin-bottom: 10px;
        }

        .cart-stats {
            display: flex;
            gap: 30px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .stat {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 25px;
            background: var(--light-pink-color);
            border-radius: var(--border-radius-s);
        }

        .stat i {
            color: var(--primary-color);
            font-size: 1.5rem;
        }

        .cart-content {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
        }

        .cart-items {
            background: var(--white-color);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .cart-sidebar {
            background: var(--white-color);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 120px;
        }

        .alert {
            padding: 15px;
            border-radius: var(--border-radius-s);
            margin-bottom: 25px;
            font-size: var(--font-size-m);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .cart-item {
            display: flex;
            gap: 20px;
            padding: 25px;
            border-bottom: 2px solid var(--light-pink-color);
            align-items: center;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 120px;
            height: 120px;
            border-radius: 15px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-size: var(--font-size-l);
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .item-price {
            font-size: var(--font-size-m);
            color: var(--dark-color);
            font-weight: var(--font-weight-bold);
            margin-bottom: 15px;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-btn {
            width: 35px;
            height: 35px;
            background: var(--light-pink-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            background: var(--primary-color);
            color: white;
        }

        .quantity-input {
            width: 60px;
            padding: 8px;
            text-align: center;
            border: 2px solid var(--medium-gray-color);
            border-radius: var(--border-radius-s);
            font-size: var(--font-size-m);
        }

        .item-total {
            font-size: var(--font-size-l);
            color: var(--primary-color);
            font-weight: var(--font-weight-bold);
            margin: 0 30px;
            min-width: 100px;
            text-align: right;
        }

        .remove-btn {
            color: #dc3545;
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 10px;
            transition: all 0.3s ease;
        }

        .remove-btn:hover {
            transform: scale(1.2);
        }

        .cart-empty {
            text-align: center;
            padding: 60px 20px;
        }

        .cart-empty i {
            font-size: 4rem;
            color: var(--medium-gray-color);
            margin-bottom: 20px;
        }

        .cart-empty h3 {
            color: var(--dark-color);
            font-size: var(--font-size-l);
            margin-bottom: 15px;
        }

        .cart-summary h3 {
            color: var(--primary-color);
            font-size: var(--font-size-l);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-pink-color);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #eee;
        }

        .summary-row.total {
            font-size: var(--font-size-l);
            font-weight: var(--font-weight-bold);
            color: var(--primary-color);
            border-bottom: none;
            padding-top: 15px;
            margin-top: 15px;
            border-top: 2px solid var(--light-pink-color);
        }

        .btn-checkout {
            width: 100%;
            padding: 18px;
            background: var(--primary-color);
            color: var(--white-color);
            border: none;
            border-radius: var(--border-radius-s);
            font-size: var(--font-size-m);
            font-weight: var(--font-weight-bold);
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-checkout:hover {
            background: #9B0000;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 0, 0, 0.3);
        }

        .btn-secondary {
            width: 100%;
            padding: 15px;
            background: var(--secondary-color);
            color: var(--white-color);
            border: none;
            border-radius: var(--border-radius-s);
            font-size: var(--font-size-m);
            font-weight: var(--font-weight-bold);
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-secondary:hover {
            background: #e69500;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 165, 0, 0.3);
        }

        .delivery-address {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .delivery-address h4 {
            color: var(--dark-color);
            margin-bottom: 15px;
            font-size: var(--font-size-m);
        }

        .address-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--medium-gray-color);
            border-radius: var(--border-radius-s);
            font-size: var(--font-size-m);
            margin-bottom: 15px;
            resize: vertical;
            min-height: 80px;
        }

        .cart-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid var(--light-pink-color);
        }

        @media (max-width: 900px) {
            .cart-content {
                grid-template-columns: 1fr;
            }
            
            .cart-item {
                flex-direction: column;
                text-align: center;
            }
            
            .item-total {
                margin: 15px 0;
                text-align: center;
            }
            
            .quantity-controls {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="navbar">
            <a href="../index.php" class="nav-logo">
                <h2 class="logo-text">🍽️ FoodSHOP</h2>
            </a>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../index.php" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="../index.php#menu" class="nav-link">Menu</a>
                </li>
                <li class="nav-item cart-icon">
                    <a href="cart.php" class="nav-link active">
                        <i class="fas fa-shopping-cart"></i> 
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="profile.php" class="nav-link">
                        <i class="fas fa-user"></i> 
                        <?php 
                            $name = $_SESSION['username'];
                            echo (strpos($name, ' ') !== false) ? htmlspecialchars(explode(' ', $name)[0]) : htmlspecialchars($name);
                        ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../php/logout.php" class="nav-link">Logout</a>
                </li>
            </ul>
            <button id="menu-open-button" class="fas fa-bars"></button>
        </nav>
    </header>
     <?php if (isAdmin()): ?>
<li class="nav-item">
    <a href="pages/admin.php" class="nav-link">
        <i class="fas fa-crown"></i> Admin
    </a>
</li>
<?php endif; ?>
    <!-- Cart Content -->
    <section class="cart-section">
        <div class="cart-container">
            <div class="cart-header">
                <h2>Shopping Cart</h2>
                <p>Review your items and proceed to checkout</p>
                
                <div class="cart-stats">
                    <div class="stat">
                        <i class="fas fa-shopping-cart"></i>
                        <div>
                            <h4 style="color: grey;">
                            <?php echo $cart_count; ?> Items
                            </h4>
                            <p>In your cart</p>
                        </div>
                    </div>
                    <div class="stat">
                        <i class="fas fa-dollar-sign"></i>
                        <div>
                        <h4 style="color: grey;">
                         $<?php echo number_format($cart_total, 2); ?>
                        </h4>
                        <p>Total amount</p>
                        </div>

                    </div>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="cart-content">
                <div class="cart-items">
                    <?php if (empty($cart_items)): ?>
                        <div class="cart-empty">
                            <i class="fas fa-shopping-cart"></i>
                            <h3>Your cart is empty</h3>
                            <p>Add delicious items from our menu to get started!</p>
                            <a href="../index.php#menu" class="btn-primary" style="display: inline-block; margin-top: 20px; padding: 12px 30px;">
                                <i class="fas fa-utensils"></i> Browse Menu
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item">
                                <div class="item-image">
                                    <img src="../images/<?php echo htmlspecialchars($item['image_path'] ?? 'default-food.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                
                                <div class="item-details">
                                    <h3 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <div class="item-price">$<?php echo number_format($item['price'], 2); ?> each</div>
                                    
                                    <div class="quantity-controls">
                                        <form method="POST" action="" style="display: flex; align-items: center; gap: 10px;">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                            
                                            <button type="button" class="quantity-btn minus-btn" 
                                                    onclick="updateQuantity(<?php echo $item['cart_id']; ?>, -1)">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            
                                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                                   min="1" max="20" class="quantity-input"
                                                   onchange="submitForm(this.form)">
                                            
                                            <button type="button" class="quantity-btn plus-btn" 
                                                    onclick="updateQuantity(<?php echo $item['cart_id']; ?>, 1)">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                
                                <div class="item-total">
                                    $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                </div>
                                
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                    <button type="submit" class="remove-btn" title="Remove item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="cart-actions">
                            <form method="POST" action="" style="flex: 1;">
                                <input type="hidden" name="action" value="clear">
                                <button type="submit" class="btn-secondary" onclick="return confirm('Clear entire cart?')">
                                    <i class="fas fa-trash-alt"></i> Clear Cart
                                </button>
                            </form>
                            <a href="../index.php#menu" class="btn-secondary" style="flex: 1; text-align: center; text-decoration: none;">
                                <i class="fas fa-plus"></i> Add More Items
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($cart_items)): ?>
                    <div class="cart-sidebar">
                        <div class="cart-summary">
                            <h3>Order Summary</h3>
                            
                            <div class="summary-row">
                                <span>Subtotal (<?php echo $cart_count; ?> items)</span>
                                <span>$<?php echo number_format($cart_total, 2); ?></span>
                            </div>
                            
                            <div class="summary-row">
                                <span>Delivery Fee</span>
                                <span>$2.99</span>
                            </div>
                            
                            <div class="summary-row">
                                <span>Tax (8%)</span>
                                <span>$<?php echo number_format($cart_total * 0.08, 2); ?></span>
                            </div>
                            
                            <div class="summary-row total">
                                <span>Total</span>
                                <span>$<?php echo number_format($cart_total + 2.99 + ($cart_total * 0.08), 2); ?></span>
                            </div>
                            
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="checkout">
                                
                                <div class="delivery-address">
                                    <h4>Delivery Address</h4>
                                    <?php
                                    $user = fetchOne("SELECT address FROM users WHERE id = :id", [':id' => $user_id]);
                                    $saved_address = $user ? $user['address'] : '';
                                    ?>
                                    <textarea name="delivery_address" class="address-input" 
                                              placeholder="Enter your delivery address (street, city, zip code)" 
                                              required><?php echo htmlspecialchars($saved_address); ?></textarea>
                                    
                                    <?php if ($saved_address): ?>
                                        <small style="color: #666; display: block; margin-top: -10px; margin-bottom: 15px;">
                                            <i class="fas fa-info-circle"></i> Your saved address is shown above. You can edit it if needed.
                                        </small>
                                    <?php endif; ?>
                                </div>
                                
                                <button type="submit" class="btn-checkout" onclick="return confirmOrder()">
                                    <i class="fas fa-shopping-bag"></i> Place Order (Cash on Delivery)
                                </button>
                            </form>
                            
                            <a href="../index.php" class="btn-secondary">
                                <i class="fas fa-arrow-left"></i> Continue Shopping
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-section">
        <div class="section-content">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>🍽️ FoodSHOP</h3>
                    <p>Delicious meals delivered hot and fresh to your door.</p>
                </div>
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <a href="../index.php#menu">Menu</a>
                    <a href="../index.php#delivery">Delivery Info</a>
                    <a href="../index.php#contact">Contact</a>
                </div>
                <div class="footer-column">
                    <h3>My Account</h3>
                    <a href="profile.php">Profile</a>
                    <a href="cart.php">Cart</a>
                    <a href="order_success.php">My Orders</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p class="copyright-text">© 2024 FoodSHOP. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Update cart item quantity
        function updateQuantity(cartId, change) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'update';
            form.appendChild(actionInput);
            
            const cartIdInput = document.createElement('input');
            cartIdInput.type = 'hidden';
            cartIdInput.name = 'cart_id';
            cartIdInput.value = cartId;
            form.appendChild(cartIdInput);
            
            // Get current quantity
            const quantityInput = document.querySelector(`input[name="quantity"][value="${cartId}"]`);
            const currentQty = quantityInput ? parseInt(quantityInput.value) : 1;
            const newQty = Math.max(1, currentQty + change);
            
            const newQuantityInput = document.createElement('input');
            newQuantityInput.type = 'hidden';
            newQuantityInput.name = 'quantity';
            newQuantityInput.value = newQty;
            form.appendChild(newQuantityInput);
            
            document.body.appendChild(form);
            form.submit();
        }
        
        // Submit form when quantity input changes
        function submitForm(form) {
            const quantity = parseInt(form.querySelector('input[name="quantity"]').value);
            if (quantity > 0 && quantity <= 20) {
                form.submit();
            } else {
                alert('Quantity must be between 1 and 20');
                form.querySelector('input[name="quantity"]').value = 1;
            }
        }
        
        // Confirm order placement
        function confirmOrder() {
            const address = document.querySelector('textarea[name="delivery_address"]').value.trim();
            
            if (!address) {
                alert('Please enter your delivery address');
                return false;
            }
            
            const cartTotal = <?php echo $cart_total; ?>;
            const finalTotal = cartTotal + 2.99 + (cartTotal * 0.08);
            
            //return confirm(`CONFIRM YOUR ORDER\n\n• Total: $${finalTotal.toFixed(2)}\n• Payment: CASH ON DELIVERY\n• Please have exact cash ready\n\nYour food will be prepared immediately!`);
        }
    </script>
    <script src="../script.js"></script>
</body>
</html>