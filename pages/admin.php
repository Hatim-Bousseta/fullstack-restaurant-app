<?php
require_once '../php/db.php';

// Check if user is admin
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_order_status':
            $order_id = $_POST['order_id'] ?? 0;
            $status = $_POST['status'] ?? '';
            
            if ($order_id > 0 && $status && updateOrderStatus($order_id, $status)) {
                $success_msg = "Order #$order_id status updated to $status";
            } else {
                $error_msg = "Failed to update order status";
            }
            break;
            
        case 'add_menu_item':
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $is_available = isset($_POST['is_available']) ? 1 : 0;
            
            // Handle image upload
            $image_path = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/FOOD/images/';
                $file_name = time() . '_' . basename($_FILES['image']['name']);
                $target_file = $upload_dir . $file_name;
                
                // Check if file is an image
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($imageFileType, $allowed_types)) {
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $image_path = $file_name;
                    }
                }
            }
            
            if ($name && $price > 0) {
                if (addMenuItem($name, $description, $price, $image_path, $is_available)) {
                    $success_msg = "Menu item '$name' added successfully";
                } else {
                    $error_msg = "Failed to add menu item";
                }
            } else {
                $error_msg = "Please provide name and valid price";
            }
            break;
            
        case 'update_menu_item':
    $item_id = $_POST['item_id'] ?? 0;
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    // Handle image update if new image uploaded
    $image_path = null; // null means don't update the image
    
    if (isset($_FILES['edit_image']) && $_FILES['edit_image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/FOOD/images/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['edit_image']['name']));
        $target_file = $upload_dir . $file_name;
        
        // Check if file is an image
        $check = getimagesize($_FILES['edit_image']['tmp_name']);
        if ($check !== false) {
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($imageFileType, $allowed_types)) {
                if (move_uploaded_file($_FILES['edit_image']['tmp_name'], $target_file)) {
                    $image_path = $file_name;
                } else {
                    $error_msg = "Failed to upload image.";
                }
            } else {
                $error_msg = "Only JPG, JPEG, PNG & GIF files are allowed.";
            }
        } else {
            $error_msg = "File is not an image.";
        }
    }
    
    if ($item_id > 0 && $name && $price > 0) {
        if (updateMenuItem($item_id, $name, $description, $price, $image_path, $is_available)) {
            $success_msg = "Menu item updated successfully";
        } else {
            $error_msg = "Failed to update menu item";
        }
    }
    break;
            
        case 'delete_menu_item':
            $item_id = $_POST['item_id'] ?? 0;
            
            if ($item_id > 0 && deleteMenuItem($item_id)) {
                $success_msg = "Menu item deleted/disabled successfully";
            } else {
                $error_msg = "Failed to delete menu item";
            }
            break;
            
        case 'update_user_role':
            $user_to_update = $_POST['user_id'] ?? 0;
            $is_admin = isset($_POST['is_admin']) ? 1 : 0;
            
            if ($user_to_update > 0) {
                $sql = "UPDATE USERS SET is_admin = :is_admin WHERE id = :user_id";
                if (executeQuery($sql, [':is_admin' => $is_admin, ':user_id' => $user_to_update])) {
                    $success_msg = "User role updated successfully";
                } else {
                    $error_msg = "Failed to update user role";
                }
            }
            break;
    }
}

// Get data for display
$all_orders = getAllOrders();
$all_users = getAllUsers();
$menu_items = getMenuItems();
$sales_stats = getSalesStats();
$popular_items = getPopularItems();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FoodSHOP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <style>
        
        .admin-section {
            padding: 100px 20px 50px;
            background: var(--light-pink-color);
            min-height: 100vh;
        }
        
        .admin-container {
            max-width: var(--site-max-width);
            margin: 0 auto;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #2c3e50, #4a6491);
            color: white;
            border-radius: 20px;
            padding: 30px 40px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        
        .admin-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .admin-header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .stat-card.orders i { color: #3498db; }
        .stat-card.revenue i { color: #2ecc71; }
        .stat-card.avg i { color: #e74c3c; }
        .stat-card.users i { color: #9b59b6; }
        
        .stat-card h3 {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-card p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .admin-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            background: white;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .tab-btn {
            padding: 12px 25px;
            background: #f8f9fa;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .tab-btn.active {
            background: var(--primary-color);
            color: white;
        }
        
        .tab-btn:hover:not(.active) {
            background: #e9ecef;
        }
        
        .tab-content {
            display: none;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
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
        
        .table-container {
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid #dee2e6;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }
        
        .data-table td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }
        
        .data-table tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-preparing { background: #d1ecf1; color: #0c5460; }
        .status-delivery { background: #d4edda; color: #155724; }
        .status-delivered { background: #c3e6cb; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-sm {
            padding: 6px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-edit {
            background: #17a2b8;
            color: white;
        }
        
        .btn-edit:hover {
            background: #138496;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background: #c82333;
        }
        
        .btn-status {
            background: #28a745;
            color: white;
        }
        
        .btn-status:hover {
            background: #218838;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn-submit {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-submit:hover {
            background: #9B0000;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 0, 0, 0.2);
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            animation: modalFadeIn 0.3s ease;
        }
        
        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #2c3e50;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6c757d;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 15px 0;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }
        
        .checkbox-group label {
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .admin-tabs {
                flex-direction: column;
            }
        }
        // Add this CSS to the style section in admin.php header
<style>
    /* Add this to your existing CSS */
    .message-container {
        position: fixed;
        top: 100px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
        animation: slideIn 0.3s ease;
    }
    
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    .message-box {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .message-success {
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        color: white;
        border-left: 4px solid #27ae60;
    }
    
    .message-error {
        background: linear-gradient(135deg, #e74c3c, #c0392b);
        color: white;
        border-left: 4px solid #c0392b;
    }
    
    .message-box i {
        font-size: 1.2rem;
    }
    
    .message-close {
        margin-left: auto;
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        font-size: 1.2rem;
    }
    /* Confirmation Modal Styles */
.confirmation-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9998;
    align-items: center;
    justify-content: center;
}

.confirmation-modal.active {
    display: flex;
}

.confirmation-content {
    background: white;
    border-radius: 15px;
    padding: 30px;
    width: 90%;
    max-width: 400px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    animation: modalFadeIn 0.3s ease;
}

.confirmation-icon {
    font-size: 3rem;
    margin-bottom: 15px;
}

.delete-icon { color: #e74c3c; }
.role-icon { color: #e13a3aff; }
.update-icon { color: #2ecc71; }

.confirmation-message {
    font-size: 1.2rem;
    color: #2c3e50;
    margin-bottom: 25px;
    line-height: 1.5;
}

.confirmation-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.confirm-btn {
    padding: 10px 25px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 100px;
}

.confirm-yes {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
}

.confirm-yes:hover {
    background: linear-gradient(135deg, #c0392b, #a93226);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(192, 57, 43, 0.3);
}

.confirm-no {
    background: #f8f9fa;
    color: #495057;
    border: 2px solid #dee2e6;
}

.confirm-no:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

.confirm-warning {
    background: linear-gradient(135deg, #ed1a1aff, #2980b9);
    color: white;
}

.confirm-warning:hover {
    background: linear-gradient(135deg, #2980b9, #1f618d);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(41, 128, 185, 0.3);
}
</style>

<!-- Add this JavaScript function -->
<script>
function showMessage(type, text) {
    const container = document.querySelector('.message-container');
    const message = document.createElement('div');
    message.className = `message-box message-${type}`;
    message.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${text}</span>
        <button class="message-close" onclick="this.parentElement.remove()">&times;</button>
    `;
    container.appendChild(message);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (message.parentElement) {
            message.remove();
        }
    }, 5000);
}


// Variables to store confirmation data
let currentFormToSubmit = null;
let currentDeleteForm = null;
let currentRoleForm = null;

// Delete confirmation
function confirmDeleteItem(form) {
    currentDeleteForm = form;
    document.getElementById('deleteConfirmModal').classList.add('active');
    return false; // Prevent form submission
}

function confirmDelete() {
    if (currentDeleteForm) {
        currentDeleteForm.submit();
    }
    closeConfirmation('deleteConfirmModal');
}

// Role change confirmation
function confirmRoleChangeItem(form, username, newRole) {
    currentRoleForm = form;
    const message = `Are you sure you want to change <strong>${username}</strong> to <strong>${newRole}</strong>?`;
    document.getElementById('roleConfirmMessage').innerHTML = message;
    document.getElementById('roleConfirmModal').classList.add('active');
    return false; // Prevent form submission
}

function confirmRoleChange() {
    if (currentRoleForm) {
        currentRoleForm.submit();
    }
    closeConfirmation('roleConfirmModal');
}

// Update confirmation (for edit modal)
function confirmUpdateItem(form) {
    currentFormToSubmit = form;
    document.getElementById('updateConfirmModal').classList.add('active');
    return false; // Prevent form submission
}

function confirmUpdate() {
    if (currentFormToSubmit) {
        currentFormToSubmit.submit();
    }
    closeConfirmation('updateConfirmModal');
}

// Close confirmation modal
function closeConfirmation(modalId) {
    document.getElementById(modalId).classList.remove('active');
    currentFormToSubmit = null;
    currentDeleteForm = null;
    currentRoleForm = null;
}

// Close modal on outside click
document.querySelectorAll('.confirmation-modal').forEach(modal => {
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeConfirmation(modal.id);
        }
    });
});
</script>

<!-- Add this HTML container (place it right after opening body tag) -->
<div class="message-container"></div>
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
                        <span class="cart-count"><?php echo getCartCount(); ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admin.php" class="nav-link active">
                        <i class="fas fa-crown"></i> Admin
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

    <!-- Admin Content -->
    <section class="admin-section">
        <div class="admin-container">
            <div class="admin-header">
                <h1><i class="fas fa-crown"></i> Admin Dashboard</h1>
                <p>Manage orders, menu items, users, and view statistics</p>
                
                <div class="stats-grid">
                    <div class="stat-card orders">
                        <i class="fas fa-shopping-bag"></i>
                        <h3><?php echo $sales_stats['total_orders'] ?? 0; ?></h3>
                        <p>Total Orders</p>
                    </div>
                    
                    <div class="stat-card revenue">
                        <i class="fas fa-dollar-sign"></i>
                        <h3>$<?php echo number_format($sales_stats['total_revenue'] ?? 0, 2); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                    
                    <div class="stat-card avg">
                        <i class="fas fa-chart-line"></i>
                        <h3>$<?php echo number_format($sales_stats['avg_order_value'] ?? 0, 2); ?></h3>
                        <p>Average Order</p>
                    </div>
                    
                    <div class="stat-card users">
                        <i class="fas fa-users"></i>
                        <h3><?php echo count($all_users); ?></h3>
                        <p>Registered Users</p>
                    </div>
                </div>
            </div>
            
            <?php if ($success_msg): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_msg); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_msg): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_msg); ?>
                </div>
            <?php endif; ?>
            
            <div class="admin-tabs">
                <button class="tab-btn active" data-tab="orders">
                    <i class="fas fa-shopping-bag"></i> Orders
                </button>
                <button class="tab-btn" data-tab="menu">
                    <i class="fas fa-utensils"></i> Menu Items
                </button>
                <button class="tab-btn" data-tab="users">
                    <i class="fas fa-users"></i> Users
                </button>
                <button class="tab-btn" data-tab="add-item">
                    <i class="fas fa-plus-circle"></i> Add Item
                </button>
                <button class="tab-btn" data-tab="stats">
                    <i class="fas fa-chart-bar"></i> Statistics
                </button>
            </div>
            
            <!-- Orders Tab -->
            <div id="orders" class="tab-content active">
                <h2 style="margin-bottom: 20px; color: #2c3e50;">Manage Orders</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_orders as $order): ?>
                            <tr>
                                <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($order['username']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($order['email']); ?></small>
                                </td>
                                <td><?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?></td>
                                <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($order['delivery_address']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-sm btn-status" onclick="openStatusModal(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')">
                                            <i class="fas fa-edit"></i> Status
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Menu Items Tab -->
            <div id="menu" class="tab-content">
                <h2 style="margin-bottom: 20px; color: #2c3e50;">Manage Menu Items</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($menu_items as $item): ?>
                            <tr>
                                <td><?php echo $item['id']; ?></td>
                                <td>
                                    <img src="../images/<?php echo htmlspecialchars($item['image_path'] ?? 'default-food.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                         style="width: 60px; height: 60px; border-radius: 8px; object-fit: cover;">
                                </td>
                                <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars(substr($item['description'] ?? '', 0, 80)) . '...'; ?></td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <?php if ($item['is_available'] == 1): ?>
                                        <span style="color: #28a745;"><i class="fas fa-check-circle"></i> Available</span>
                                    <?php else: ?>
                                        <span style="color: #dc3545;"><i class="fas fa-times-circle"></i> Unavailable</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-sm btn-edit" onclick="openEditModal(
    <?php echo $item['id']; ?>, 
    '<?php echo addslashes($item['name']); ?>', 
    '<?php echo addslashes($item['description']); ?>', 
    <?php echo $item['price']; ?>, 
    <?php echo $item['is_available']; ?>,
    '<?php echo addslashes($item['image_path'] ?? 'default-food.jpg'); ?>'
)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form method="POST" action="" style="display: inline;" onsubmit="return confirmDeleteItem(this)">
    <input type="hidden" name="action" value="delete_menu_item">
    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
    <button type="submit" class="btn-sm btn-delete">
        <i class="fas fa-trash"></i> Delete
    </button>
</form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Users Tab -->
            <div id="users" class="tab-content">
                <h2 style="margin-bottom: 20px; color: #2c3e50;">Manage Users</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($user['address'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if ($user['is_admin'] == 1): ?>
                                        <span style="color: #e74c3c;"><i class="fas fa-crown"></i> Admin</span>
                                    <?php else: ?>
                                        <span style="color: #db3434ff;"><i class="fas fa-user"></i> Customer</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($user['created_date'])); ?></td>
                                <td>
                                    <?php if ($user['id'] != $user_id): ?>
                                    <form method="POST" action="" onsubmit="return confirmRoleChangeItem(
    this, 
    '<?php echo addslashes($user['username']); ?>', 
    '<?php echo $user['is_admin'] == 1 ? 'Customer' : 'Admin'; ?>'
)">
    <input type="hidden" name="action" value="update_user_role">
    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
    <input type="hidden" name="is_admin" value="<?php echo $user['is_admin'] == 1 ? '0' : '1'; ?>">
    <button type="submit" class="btn-sm btn-status">
        <i class="fas fa-user-cog"></i>
        <?php echo $user['is_admin'] == 1 ? 'Make Customer' : 'Make Admin'; ?>
    </button>
</form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Add Item Tab -->
            <div id="add-item" class="tab-content">
                <h2 style="margin-bottom: 20px; color: #2c3e50;">Add New Menu Item</h2>
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_menu_item">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name"><i class="fas fa-utensils"></i> Item Name *</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="price"><i class="fas fa-dollar-sign"></i> Price *</label>
                            <input type="number" id="price" name="price" class="form-control" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description"><i class="fas fa-align-left"></i> Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="image"><i class="fas fa-image"></i> Image</label>
                        <input type="file" id="image" name="image" class="form-control" accept="image/*">
                        <small style="color: #666; display: block; margin-top: 5px;">JPG, PNG, or GIF files only</small>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_available" name="is_available" value="1" checked>
                        <label for="is_available">Available for ordering</label>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-plus-circle"></i> Add Menu Item
                    </button>
                </form>
            </div>
            
            <!-- Statistics Tab -->
            <div id="stats" class="tab-content">
                <h2 style="margin-bottom: 20px; color: #2c3e50;">Sales Statistics</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <div class="stat-card" style="text-align: center;">
                            <i class="fas fa-calendar-alt" style="color: #d6290aff;"></i>
                            <h3><?php echo $sales_stats['first_order_date'] ? date('M j, Y', strtotime($sales_stats['first_order_date'])) : 'N/A'; ?></h3>
                            <p>First Order</p>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="stat-card" style="text-align: center;">
                            <i class="fas fa-calendar-check" style="color: #2ecc71;"></i>
                            <h3><?php echo $sales_stats['last_order_date'] ? date('M j, Y', strtotime($sales_stats['last_order_date'])) : 'N/A'; ?></h3>
                            <p>Last Order</p>
                        </div>
                    </div>
                </div>
                
                <h3 style="margin: 30px 0 20px 0; color: #2c3e50;">Popular Items</h3>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Price</th>
                                <th>Units Sold</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($popular_items as $item): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['total_sold']; ?></td>
                                <td>$<?php echo number_format($item['total_revenue'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Status Update Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Update Order Status</h3>
                <button class="close-modal" onclick="closeModal('statusModal')">&times;</button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_order_status">
                <input type="hidden" id="modal_order_id" name="order_id">
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="Pending">Pending</option>
                        <option value="Preparing">Preparing</option>
                        <option value="Out for Delivery">Out for Delivery</option>
                        <option value="Delivered">Delivered</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Update Status
                </button>
            </form>
        </div>
    </div>
    
    <!-- Edit Item Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Menu Item</h3>
            <button class="close-modal" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_menu_item">
            <input type="hidden" id="edit_item_id" name="item_id">
            
            <div class="form-group">
                <label for="edit_name">Item Name</label>
                <input type="text" id="edit_name" name="name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="edit_description">Description</label>
                <textarea id="edit_description" name="description" class="form-control" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label for="edit_price">Price</label>
                <input type="number" id="edit_price" name="price" class="form-control" step="0.01" required>
            </div>
            
            <!-- ADD THIS IMAGE UPLOAD FIELD -->
            <div class="form-group">
                <label for="edit_image"><i class="fas fa-image"></i> Update Image</label>
                <div style="margin-bottom: 10px;">
                    <img id="current_image_preview" src="" alt="Current Image" 
                         style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                </div>
                <input type="file" id="edit_image" name="edit_image" class="form-control" accept="image/*">
                <small style="color: #666;">Leave empty to keep current image</small>
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" id="edit_is_available" name="is_available" value="1">
                <label for="edit_is_available">Available for ordering</label>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Update Item
            </button>
        </form>
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
                    <a href="admin.php">Admin Panel</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p class="copyright-text">© 2024 FoodSHOP. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <script>
        // Tab functionality
        document.querySelectorAll('.tab-btn').forEach(button => {
            button.addEventListener('click', () => {
                const tabId = button.getAttribute('data-tab');
                
                // Update active tab button
                document.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                button.classList.add('active');
                
                // Update active tab content
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // Modal functions
        function openStatusModal(orderId, currentStatus) {
            document.getElementById('modal_order_id').value = orderId;
            document.getElementById('status').value = currentStatus;
            document.getElementById('statusModal').classList.add('active');
        }
        
        function openEditModal(itemId, name, description, price, isAvailable, currentImage = 'default-food.jpg') {
    document.getElementById('edit_item_id').value = itemId;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_price').value = price;
    document.getElementById('edit_is_available').checked = isAvailable == 1;
    
    // Set current image preview
    const imagePath = currentImage || 'default-food.jpg';
    document.getElementById('current_image_preview').src = '../images/' + imagePath;
    
    document.getElementById('editModal').classList.add('active');
}
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
        
        // Close modal on outside click
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('active');
                }
            });
        });
        
        // Auto-refresh orders every 30 seconds
        setInterval(() => {
            const activeTab = document.querySelector('.tab-content.active');
            if (activeTab && activeTab.id === 'orders') {
                window.location.reload();
            }
        }, 30000);
    </script>
    <script src="../script.js"></script>
    <!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="confirmation-modal">
    <div class="confirmation-content">
        <div class="confirmation-icon delete-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3>Confirm Delete</h3>
        <p class="confirmation-message">Are you sure you want to delete this menu item? This action cannot be undone.</p>
        <div class="confirmation-buttons">
            <button type="button" class="confirm-btn confirm-yes" onclick="confirmDelete()">
                <i class="fas fa-trash"></i> Yes, Delete
            </button>
            <button type="button" class="confirm-btn confirm-no" onclick="closeConfirmation('deleteConfirmModal')">
                <i class="fas fa-times"></i> Cancel
            </button>
        </div>
    </div>
</div>

<!-- Role Change Confirmation Modal -->
<div id="roleConfirmModal" class="confirmation-modal">
    <div class="confirmation-content">
        <div class="confirmation-icon role-icon">
            <i class="fas fa-user-cog"></i>
        </div>
        <h3>Change User Role</h3>
        <p class="confirmation-message" id="roleConfirmMessage">Are you sure you want to change this user's role?</p>
        <div class="confirmation-buttons">
            <button type="button" class="confirm-btn confirm-warning" onclick="confirmRoleChange()">
                <i class="fas fa-check"></i> Yes, Change
            </button>
            <button type="button" class="confirm-btn confirm-no" onclick="closeConfirmation('roleConfirmModal')">
                <i class="fas fa-times"></i> Cancel
            </button>
        </div>
    </div>
</div>

<!-- Update Confirmation Modal -->
<div id="updateConfirmModal" class="confirmation-modal">
    <div class="confirmation-content">
        <div class="confirmation-icon update-icon">
            <i class="fas fa-save"></i>
        </div>
        <h3>Confirm Update</h3>
        <p class="confirmation-message">Are you sure you want to save these changes?</p>
        <div class="confirmation-buttons">
            <button type="button" class="confirm-btn confirm-warning" onclick="confirmUpdate()">
                <i class="fas fa-check"></i> Yes, Update
            </button>
            <button type="button" class="confirm-btn confirm-no" onclick="closeConfirmation('updateConfirmModal')">
                <i class="fas fa-times"></i> Cancel
            </button>
        </div>
    </div>
</div>
</body>
</html>
