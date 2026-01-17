<?php
require_once '../php/db.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$message = '';

// Line 8 - change to:
$order = fetchOne("SELECT * FROM ORDERS WHERE user_id = :user_id ORDER BY order_date DESC", 
                  [':user_id' => $user_id]);

// Line 12 - change to:
$order_items = fetchAll("SELECT * FROM ORDER_ITEMS WHERE order_id = :order_id", 
                       [':order_id' => $order['id']]);

// Format order date
$order_date = date('F j, Y \a\t g:i A', strtotime($order['order_date']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed! - FoodSHOP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            background: var(--light-pink-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .confirmation-container {
            max-width: 800px;
            margin: 100px auto 50px;
            padding: 0 20px;
        }
        
        .confirmation-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background: #4CAF50;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }
        
        .success-icon i {
            font-size: 3.5rem;
            color: white;
        }
        
        .confirmation-card h1 {
            color: var(--primary-color);
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .confirmation-card .subtitle {
            color: #666;
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
        
        .order-number {
            background: var(--light-pink-color);
            padding: 15px 30px;
            border-radius: 50px;
            display: inline-block;
            margin: 20px 0;
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .order-details {
            background: #f9f9f9;
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
            text-align: left;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dashed #ddd;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-row.total {
            font-weight: bold;
            font-size: 1.2rem;
            color: var(--primary-color);
            padding-top: 15px;
            margin-top: 15px;
            border-top: 2px solid var(--primary-color);
        }
        
        .order-items {
            margin: 20px 0;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .status-badge {
            display: inline-block;
            background: #FFF3CD;
            color: #856404;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            margin: 15px 0;
        }
        
        .payment-note {
            background: #E7F5FF;
            border-left: 4px solid #0066CC;
            padding: 15px;
            border-radius: 8px;
            margin: 25px 0;
            text-align: left;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 40px;
            flex-wrap: wrap;
        }
        
        .btn-primary {
            padding: 15px 35px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-primary:hover {
            background: #9B0000;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(139, 0, 0, 0.3);
        }
        
        .btn-secondary {
            padding: 15px 35px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-3px);
        }
        
        .time-estimate {
            background: #D4EDDA;
            color: #155724;
            padding: 15px;
            border-radius: 10px;
            margin: 25px 0;
            font-size: 1.1rem;
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
                    <a href="cart.php" class="nav-link">
                        <i class="fas fa-shopping-cart"></i> 
                        <span class="cart-count">0</span>
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

    <!-- Confirmation Content -->
    <div class="confirmation-container">
        <div class="confirmation-card">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            
            <h1>Order Confirmed!</h1>
            <p class="subtitle">Thank you for your order. Your food is being prepared!</p>
            
            <div class="order-number">
                Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?>
            </div>
            
            <div class="status-badge">
                <i class="fas fa-utensils"></i> PREPARING YOUR ORDER
            </div>
            
            <div class="time-estimate">
                <i class="fas fa-clock"></i> 
                Estimated Delivery: 30-45 minutes
            </div>
            
            <div class="order-details">
                <h3 style="color: var(--primary-color); margin-bottom: 20px; text-align: center;">
                    <i class="fas fa-receipt"></i> Order Details
                </h3>
                
                <div class="detail-row">
                    <span>Order Date:</span>
                    <span><?php echo $order_date; ?></span>
                </div>
                
                <div class="detail-row">
                    <span>Delivery Address:</span>
                    <span><?php echo htmlspecialchars($order['delivery_address']); ?></span>
                </div>
                
                <div class="detail-row">
                    <span>Payment Method:</span>
                    <span><i class="fas fa-money-bill-wave"></i> Cash on Delivery</span>
                </div>
                
                <h4 style="margin: 25px 0 15px 0; color: #333;">Items Ordered:</h4>
                
                <div class="order-items">
                    <?php foreach ($order_items as $item): ?>
                    <div class="order-item">
                        <span><?php echo htmlspecialchars($item['item_name']); ?> × <?php echo $item['quantity']; ?></span>
                        <span>$<?php echo number_format($item['total'], 2); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="detail-row total">
                    <span>Total Amount:</span>
                    <span>$<?php echo number_format($order['total_price'], 2); ?></span>
                </div>
            </div>
            
            <div class="payment-note">
                <i class="fas fa-info-circle"></i> 
                <strong>Cash Payment Note:</strong> Please have exact cash ready for delivery. 
                Our delivery person will collect $<?php echo number_format($order['total_price'], 2); ?> upon arrival.
            </div>
            
            <div class="action-buttons">
                <a href="orders.php" class="btn-primary">
                    <i class="fas fa-clipboard-list"></i> View All Orders
                </a>
                <a href="../index.php#menu" class="btn-secondary">
                    <i class="fas fa-utensils"></i> Order More
                </a>
                <a href="../index.php" class="btn-secondary">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>
        </div>
    </div>

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
                    <a href="orders.php">Orders</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p class="copyright-text">© 2024 FoodSHOP. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../script.js"></script>
    <script>
        // Update cart count to 0 since order is placed
        document.addEventListener('DOMContentLoaded', function() {
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                cartCount.textContent = '0';
            }
        });
    </script>
</body>
</html>