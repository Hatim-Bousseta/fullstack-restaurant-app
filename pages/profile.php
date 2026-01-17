<?php
require_once '../php/db.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get user data
$sql = "SELECT username, email, phone, address, is_admin FROM users WHERE id = :id";
$user = fetchOne($sql, [':id' => $user_id]);

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    if (empty($username)) {
        $error = 'Username is required';
    } else {
        $sql = "UPDATE users SET username = :username, phone = :phone, address = :address WHERE id = :id";
        $params = [
            ':username' => $username,
            ':phone' => $phone,
            ':address' => $address,
            ':id' => $user_id
        ];
        
        if (executeQuery($sql, $params)) {
            $success = 'Profile updated successfully!';
            $_SESSION['username'] = $username;
            
            // Refresh user data
            $user = fetchOne($sql, [':id' => $user_id]);
        } else {
            $error = 'Failed to update profile. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - FoodSHOP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <style>
        .profile-section {
            padding: 100px 20px 40px;
            background: var(--light-pink-color);
            min-height: 100vh;
        }

        .profile-container {
            max-width: var(--site-max-width);
            margin: 0 auto;
        }

        .profile-header {
            background: var(--white-color);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 25px;
        }

        .user-avatar {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(139, 0, 0, 0.15);
        }

        .user-info h2 {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .user-info p {
            color: #666;
            font-size: 0.9rem;
        }

        .user-meta {
            text-align: right;
        }

        .user-meta .badge {
            background: var(--light-pink-color);
            color: var(--primary-color);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 25px;
        }

        .profile-card {
            background: var(--white-color);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-pink-color);
        }

        .card-header h3 {
            color: var(--primary-color);
            font-size: 1.2rem;
            margin: 0;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: var(--dark-color);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background: var(--white-color);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
        }

        .form-control:disabled {
            background: #f8f8f8;
            color: #666;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px solid var(--light-pink-color);
        }

        .btn {
            padding: 12px 28px;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white-color);
        }

        .btn-primary:hover {
            background: #9B0000;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(139, 0, 0, 0.2);
        }

        .btn-secondary {
            background: var(--secondary-color);
            color: var(--white-color);
        }

        .btn-secondary:hover {
            background: #e69500;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 165, 0, 0.2);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: var(--white-color);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #f0fff4;
            color: #1f8722;
            border: 1px solid #c6f6d5;
        }

        .alert-error {
            background: #fff5f5;
            color: #c53030;
            border: 1px solid #fed7d7;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }

        .stat-item {
            padding: 15px;
            background: var(--light-pink-color);
            border-radius: 10px;
            text-align: center;
        }

        .stat-item i {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .stat-item h4 {
            font-size: 1.1rem;
            color: var(--dark-color);
            margin: 5px 0;
        }

        .stat-item p {
            color: #666;
            font-size: 0.8rem;
            margin: 0;
        }

        .quick-actions {
            margin-top: 25px;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .action-item {
            padding: 14px;
            background: var(--light-pink-color);
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--dark-color);
            transition: all 0.2s ease;
        }

        .action-item:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .action-item i {
            font-size: 1.1rem;
        }

        .action-item span {
            font-size: 0.9rem;
            font-weight: 500;
        }

        .admin-section {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px solid var(--light-pink-color);
        }

        .admin-section h4 {
            color: var(--primary-color);
            font-size: 1rem;
            margin-bottom: 15px;
        }

        @media (max-width: 992px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-header {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 15px;
            }
            
            .user-meta {
                text-align: center;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .action-grid {
                grid-template-columns: 1fr;
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
                    <a href="cart.php" class="nav-link"><i class="fas fa-shopping-cart"></i> <span class="cart-count">0</span></a>
                </li>
                <li class="nav-item">
                    <a href="profile.php" class="nav-link active">
                        <i class="fas fa-user"></i> 
                    <?php 
                     if (isset($user['username'])) {
                          echo htmlspecialchars($user['username']);
                    } else {
                          echo 'Profile';
                     }
                    ?>
                    </a>
                </li>
                <?php if (isAdmin()): ?>
                 <li class="nav-item">
                  <a href="./admin.php" class="nav-link">
                    <i class="fas fa-crown"></i> Admin
                  </a>
                 </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="../php/logout.php" class="nav-link">Logout</a>
                </li>
            </ul>
            <button id="menu-open-button" class="fas fa-bars"></button>
        </nav>
    </header>

    <!-- Profile Content -->
    <section class="profile-section">
        <div class="profile-container">
            <div class="profile-header">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                </div>
                <div class="user-info">
                    <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                <div class="user-meta">
                    <span class="badge">
                        <i class="fas fa-user-circle"></i> 
                        <?php echo $user['is_admin'] == 1 ? 'Administrator' : 'Premium Member'; ?>
                    </span>
                </div>
            </div>

            <div class="profile-grid">
                <!-- Left Column - Edit Form -->
                <div class="profile-card">
                    <div class="card-header">
                        <h3>Profile Information</h3>
                        <span style="color: #666; font-size: 0.85rem;">
                            <i class="fas fa-info-circle"></i> Update your personal details
                        </span>
                    </div>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Full Name *</label>
                                <input type="text" id="username" name="username" class="form-control" required 
                                       value="<?php echo htmlspecialchars($user['username']); ?>">
                            </div>

                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-control"
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                       placeholder="+1 (555) 123-4567">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" class="form-control" disabled
                                   value="<?php echo htmlspecialchars($user['email']); ?>">
                            <small style="color: #888; font-size: 0.8rem; display: block; margin-top: 4px;">
                                Contact support to change your email
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="address">Delivery Address</label>
                            <input type="text" id="address" name="address" class="form-control"
                                   value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>"
                                   placeholder="Enter your complete delivery address">
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <a href="change_password.php" class="btn btn-secondary">
                                <i class="fas fa-key"></i> Change Password
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Right Column - Stats & Actions -->
                <div>
                    <!-- Stats Card -->
                    <div class="profile-card" style="margin-bottom: 25px;">
                        <div class="card-header">
                            <h3>Account Overview</h3>
                        </div>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <i class="fas fa-shopping-bag"></i>
                                <h4>0</h4>
                                <p>Orders</p>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-star"></i>
                                <h4>0</h4>
                                <p>Reviews</p>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-calendar-alt"></i>
                                <h4>Today</h4>
                                <p>Joined</p>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-percentage"></i>
                                <h4>0%</h4>
                                <p>Discount</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions Card -->
                    <div class="profile-card">
                        <div class="card-header">
                            <h3>Quick Actions</h3>
                        </div>
                        <div class="quick-actions">
                            <div class="action-grid">
                                <a href="orders.php" class="action-item">
                                    <i class="fas fa-clipboard-list"></i>
                                    <span>My Orders</span>
                                </a>
                                <a href="cart.php" class="action-item">
                                    <i class="fas fa-shopping-cart"></i>
                                    <span>View Cart</span>
                                </a>
                                <a href="addresses.php" class="action-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>Addresses</span>
                                </a>
                                <a href="settings.php" class="action-item">
                                    <i class="fas fa-cog"></i>
                                    <span>Settings</span>
                                </a>
                            </div>
                        </div>

                        <?php if ($user['is_admin'] == 1): ?>
                            <div class="admin-section">
                                <h4>Administrator Panel</h4>
                                <div class="action-grid">
                                    <a href="../admin/dashboard.php" class="action-item" style="background: #fff0f0;">
                                        <i class="fas fa-tachometer-alt"></i>
                                        <span>Dashboard</span>
                                    </a>
                                    <a href="../admin/products.php" class="action-item" style="background: #fff0f0;">
                                        <i class="fas fa-hamburger"></i>
                                        <span>Manage Menu</span>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
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
                    <a href="orders.php">Orders</a>
                    <a href="cart.php">Cart</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p class="copyright-text">© 2024 FoodSHOP. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../script.js"></script>
</body>
</html>