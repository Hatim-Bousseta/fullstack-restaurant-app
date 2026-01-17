<?php
/**
 * Oracle Database Connection
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
// Load local config if present (copy php/config.php.example -> php/config.php and set values)
// php/config.php should define DB_USER, DB_PASS, DB_CONN and SITE_URL (do not commit php/config.php)
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
} else {
    // Fallback to environment variables or placeholder values
    define('DB_USER', getenv('DB_USER') ?: 'FOOD_DB_USER');
    define('DB_PASS', getenv('DB_PASS') ?: 'FOOD_DB_PASS');
    define('DB_CONN', getenv('DB_CONN') ?: 'localhost/XEPDB1');
    define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost/FOOD');
}

// Get database connection
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        // Try to connect
        $conn = oci_connect(DB_USER, DB_PASS, DB_CONN);
        
        if (!$conn) {
            $error = oci_error();
            // Don't die in production, just return false
            error_log("Database connection failed: " . $error['message']);
            return false;
        }
    }
    
    return $conn;
}

// Helper to convert Oracle UPPERCASE keys to lowercase
function fixOracleKeys($row) {
    if (!$row) return null;
    
    $fixed = [];
    foreach ($row as $key => $value) {
        $fixed[strtolower($key)] = $value;
    }
    return $fixed;
}

// Execute query and return statement
function executeQuery($sql, $params = []) {
    $conn = getDBConnection();
    if (!$conn) {
        error_log("No database connection");
        return false;
    }
    
    // Parse the SQL statement
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $error = oci_error($conn);
        error_log("Parse failed: " . $error['message']);
        return false;
    }
    
    // Bind parameters if any
    if (!empty($params)) {
        foreach ($params as $key => $value) {
            // Handle both :key and key formats
            $bindKey = (strpos($key, ':') === 0) ? $key : ':' . $key;
            // Bind by reference
            if (!oci_bind_by_name($stmt, $bindKey, $params[$key])) {
                $error = oci_error($stmt);
                error_log("Bind failed for $bindKey: " . $error['message']);
                oci_free_statement($stmt);
                return false;
            }
        }
    }
    
    // Execute the statement
    if (!oci_execute($stmt)) {
        $error = oci_error($stmt);
        error_log("Execute failed: " . $error['message'] . " SQL: " . $sql);
        oci_free_statement($stmt);
        return false;
    }
    
    return $stmt;
}

// Test database connection
function testConnection() {
    $conn = oci_connect(DB_USER, DB_PASS, DB_CONN, 'AL32UTF8');
    if (!$conn) {
        $error = oci_error();
        return "Connection failed: " . $error['message'];
    }
    
    // Test if tables exist
    $tables = ['USERS', 'MENU_ITEMS', 'CART', 'ORDERS'];
    $missing = [];
    
    foreach ($tables as $table) {
        $stmt = oci_parse($conn, "SELECT COUNT(*) FROM $table");
        if (!oci_execute($stmt)) {
            $missing[] = $table;
        }
        oci_free_statement($stmt);
    }
    
    oci_close($conn);
    
    if (empty($missing)) {
        return "Connection successful! All tables exist.";
    } else {
        return "Connection OK but missing tables: " . implode(', ', $missing);
    }
}

// Fetch all rows as associative array with lowercase keys
function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    if (!$stmt) return [];
    
    $rows = [];
    while ($row = oci_fetch_assoc($stmt)) {
        $rows[] = fixOracleKeys($row);
    }
    
    oci_free_statement($stmt);
    return $rows;
}

// Fetch single row with lowercase keys
function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    if (!$stmt) return null;
    
    $row = oci_fetch_assoc($stmt);
    oci_free_statement($stmt);
    return fixOracleKeys($row);
}

// Insert data
function insert($table, $data) {
    $columns = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));
    
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    
    $conn = getDBConnection();
    if (!$conn) return false;
    
    $stmt = oci_parse($conn, $sql);
    
    // Bind parameters
    foreach ($data as $key => $value) {
        oci_bind_by_name($stmt, ":$key", $data[$key]);
    }
    
    if (!oci_execute($stmt)) {
        $error = oci_error($stmt);
        error_log("Insert failed: " . $error['message']);
        oci_free_statement($stmt);
        return false;
    }
    
    oci_free_statement($stmt);
    
    // Get last insert ID (Oracle specific)
    $sql = "SELECT id FROM $table WHERE ROWID = (SELECT MAX(ROWID) FROM $table)";
    $result = fetchOne($sql);
    
    return $result ? $result['id'] : true;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user is admin (checks database)
function isAdmin() {
    if (!isLoggedIn()) return false;
    
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT is_admin FROM USERS WHERE id = :user_id";
    $user = fetchOne($sql, [':user_id' => $user_id]);
    
    // FIXED LINE 79: Check if user exists and has is_admin property
    return ($user && isset($user['is_admin']) && $user['is_admin'] == 1);
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/pages/login.php');
        exit;
    }
}

// Redirect if not admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }
}

// Simple message function
function setMessage($type, $text) {
    $_SESSION['message'] = ['type' => $type, 'text' => $text];
}

function getMessage() {
    if (isset($_SESSION['message'])) {
        $msg = $_SESSION['message'];
        unset($_SESSION['message']);
        return $msg;
    }
    return null;
}

// ========== CART FUNCTIONS ==========

// Get cart items for current user
function getCartItems($user_id = null) {
    if (!$user_id) {
        if (!isLoggedIn()) return [];
        $user_id = $_SESSION['user_id'];
    }
    
    $sql = "SELECT c.id as cart_id, c.quantity, m.id as item_id, m.name, m.price, m.image_path 
            FROM CART c 
            JOIN MENU_ITEMS m ON c.item_id = m.id 
            WHERE c.user_id = :user_id 
            ORDER BY c.added_date DESC";
    
    return fetchAll($sql, [':user_id' => $user_id]);
}

// Add item to cart
function addToCart($item_id, $quantity = 1, $user_id = null) {
    if (!$user_id) {
        if (!isLoggedIn()) return false;
        $user_id = $_SESSION['user_id'];
    }
    
    // Check if item already in cart
    $sql = "SELECT id, quantity FROM CART WHERE user_id = :user_id AND item_id = :item_id";
    $existing = fetchOne($sql, [':user_id' => $user_id, ':item_id' => $item_id]);
    
    if ($existing) {
        // Update quantity
        $sql = "UPDATE CART SET quantity = quantity + :quantity WHERE id = :cart_id";
        return executeQuery($sql, [':quantity' => $quantity, ':cart_id' => $existing['id']]);
    } else {
        // Insert new item
        $sql = "INSERT INTO CART (user_id, item_id, quantity) VALUES (:user_id, :item_id, :quantity)";
        return executeQuery($sql, [':user_id' => $user_id, ':item_id' => $item_id, ':quantity' => $quantity]);
    }
}

// Update cart item quantity
function updateCartItem($cart_id, $quantity, $user_id = null) {
    if (!$user_id) {
        if (!isLoggedIn()) return false;
        $user_id = $_SESSION['user_id'];
    }
    
    if ($quantity <= 0) {
        // Remove item if quantity is 0 or less
        return removeFromCart($cart_id, $user_id);
    }
    
    $sql = "UPDATE CART SET quantity = :quantity WHERE id = :cart_id AND user_id = :user_id";
    return executeQuery($sql, [':quantity' => $quantity, ':cart_id' => $cart_id, ':user_id' => $user_id]);
}

// Remove item from cart
function removeFromCart($cart_id, $user_id = null) {
    if (!$user_id) {
        if (!isLoggedIn()) return false;
        $user_id = $_SESSION['user_id'];
    }
    
    $sql = "DELETE FROM CART WHERE id = :cart_id AND user_id = :user_id";
    return executeQuery($sql, [':cart_id' => $cart_id, ':user_id' => $user_id]);
}

// Clear user's cart
function clearCart($user_id = null) {
    if (!$user_id) {
        if (!isLoggedIn()) return false;
        $user_id = $_SESSION['user_id'];
    }
    
    $sql = "DELETE FROM CART WHERE user_id = :user_id";
    return executeQuery($sql, [':user_id' => $user_id]);
}

// Get cart total
function getCartTotal($user_id = null) {
    if (!$user_id) {
        if (!isLoggedIn()) return 0;
        $user_id = $_SESSION['user_id'];
    }
    
    $sql = "SELECT NVL(SUM(c.quantity * m.price), 0) as total 
            FROM CART c 
            JOIN MENU_ITEMS m ON c.item_id = m.id 
            WHERE c.user_id = :user_id";
    
    $result = fetchOne($sql, [':user_id' => $user_id]);
    return $result ? (float)$result['total'] : 0;
}

// Get cart item count
function getCartCount($user_id = null) {
    if (!$user_id) {
        if (!isLoggedIn()) return 0;
        $user_id = $_SESSION['user_id'];
    }
    
    $sql = "SELECT NVL(SUM(quantity), 0) as count FROM CART WHERE user_id = :user_id";
    $result = fetchOne($sql, [':user_id' => $user_id]);
    return $result ? (int)$result['count'] : 0;
}

// ========== MENU FUNCTIONS ==========

// Get all menu items
function getMenuItems($category = null) {
    $sql = "SELECT * FROM MENU_ITEMS WHERE is_available = 1";
    
    if ($category) {
        // If you add categories later
        $sql .= " AND category = :category";
        return fetchAll($sql, [':category' => $category]);
    }
    
    return fetchAll($sql);
}

// Get single menu item
function getMenuItem($item_id) {
    $sql = "SELECT * FROM MENU_ITEMS WHERE id = :id AND is_available = 1";
    return fetchOne($sql, [':id' => $item_id]);
}

// ========== ORDER FUNCTIONS ==========

// Create order from cart
// Create order from cart
function createOrder($delivery_address = null, $user_id = null) {
    if (!$user_id) {
        if (!isLoggedIn()) return false;
        $user_id = $_SESSION['user_id'];
    }
    
    if (!$delivery_address) {
        // Get user's address if not provided
        $user = fetchOne("SELECT address FROM USERS WHERE id = :id", [':id' => $user_id]);
        $delivery_address = $user ? $user['address'] : '';
    }
    
    $cart_items = getCartItems($user_id);
    
    if (empty($cart_items)) {
        return false; // No items in cart
    }
    
    // Calculate cart total
    $cart_total = getCartTotal($user_id);
    
    if ($cart_total <= 0) {
        return false;
    }
    
    $conn = getDBConnection();
    if (!$conn) return false;
    
    try {
        // Start transaction
        oci_execute(oci_parse($conn, "BEGIN"));
        
        // Create order
        $sql = "INSERT INTO ORDERS (user_id, total_price, delivery_address) 
                VALUES (:user_id, :total_price, :delivery_address)";
        
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':user_id', $user_id);
        oci_bind_by_name($stmt, ':total_price', $cart_total);
        oci_bind_by_name($stmt, ':delivery_address', $delivery_address);
        
        if (!oci_execute($stmt)) {
            throw new Exception("Failed to create order");
        }
        oci_free_statement($stmt);
        
        // Get the order ID
        $sql = "SELECT id FROM ORDERS WHERE user_id = :user_id ORDER BY order_date DESC";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':user_id', $user_id);
        oci_execute($stmt);
        $order_row = oci_fetch_assoc($stmt);
        $order_id = $order_row['ID'];
        oci_free_statement($stmt);
        
        if (!$order_id) {
            throw new Exception("Failed to get order ID");
        }
        
        // Save each cart item to ORDER_ITEMS
        foreach ($cart_items as $item) {
            $item_total = $item['quantity'] * $item['price'];
            
            $sql = "INSERT INTO ORDER_ITEMS (order_id, item_id, item_name, quantity, price, total) 
                    VALUES (:order_id, :item_id, :item_name, :quantity, :price, :total)";
            
            $stmt = oci_parse($conn, $sql);
            oci_bind_by_name($stmt, ':order_id', $order_id);
            oci_bind_by_name($stmt, ':item_id', $item['item_id']);
            oci_bind_by_name($stmt, ':item_name', $item['name']);
            oci_bind_by_name($stmt, ':quantity', $item['quantity']);
            oci_bind_by_name($stmt, ':price', $item['price']);
            oci_bind_by_name($stmt, ':total', $item_total);
            
            if (!oci_execute($stmt)) {
                throw new Exception("Failed to save order items");
            }
            oci_free_statement($stmt);
        }
        
        // Clear cart after successful order
        $sql = "DELETE FROM CART WHERE user_id = :user_id";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':user_id', $user_id);
        oci_execute($stmt);
        oci_free_statement($stmt);
        
        // Commit transaction
        oci_execute(oci_parse($conn, "COMMIT"));
        
        return $order_id;
        
    } catch (Exception $e) {
        // Rollback on error
        oci_execute(oci_parse($conn, "ROLLBACK"));
        error_log("Order creation failed: " . $e->getMessage());
        return false;
    }
}

// Get user's orders
function getUserOrders($user_id = null) {
    if (!$user_id) {
        if (!isLoggedIn()) return [];
        $user_id = $_SESSION['user_id'];
    }
    
    $sql = "SELECT * FROM ORDERS WHERE user_id = :user_id ORDER BY order_date DESC";
    return fetchAll($sql, [':user_id' => $user_id]);
}

// ========== ADMIN FUNCTIONS ==========

// Get all users (for admin)
function getAllUsers() {
    if (!isAdmin()) return [];
    
    $sql = "SELECT id, username, email, phone, address, is_admin, created_date 
            FROM USERS 
            ORDER BY created_date DESC";
    return fetchAll($sql);
}

// Get all orders with user info (for admin)
function getAllOrders() {
    if (!isAdmin()) return [];
    
    $sql = "SELECT o.*, u.username, u.email 
            FROM ORDERS o 
            JOIN USERS u ON o.user_id = u.id 
            ORDER BY o.order_date DESC";
    return fetchAll($sql);
}

// Update order status
function updateOrderStatus($order_id, $status) {
    if (!isAdmin()) return false;
    
    $valid_statuses = ['Pending', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'];
    if (!in_array($status, $valid_statuses)) return false;
    
    $sql = "UPDATE ORDERS SET status = :status WHERE id = :order_id";
    return executeQuery($sql, [':status' => $status, ':order_id' => $order_id]);
}

// Add new menu item
function addMenuItem($name, $description, $price, $image_path = '', $is_available = 1) {
    if (!isAdmin()) return false;
    
    $sql = "INSERT INTO MENU_ITEMS (name, description, price, image_path, is_available) 
            VALUES (:name, :description, :price, :image_path, :is_available)";
    
    return executeQuery($sql, [
        ':name' => $name,
        ':description' => $description,
        ':price' => $price,
        ':image_path' => $image_path,
        ':is_available' => $is_available
    ]);
}

// Update menu item
function updateMenuItem($item_id, $name, $description, $price, $image_path = null, $is_available = null) {
    if (!isAdmin()) return false;
    
    // Build dynamic query
    $sql = "UPDATE MENU_ITEMS SET name = :name, description = :description, price = :price";
    $params = [
        ':item_id' => $item_id,
        ':name' => $name,
        ':description' => $description,
        ':price' => $price
    ];
    
    if ($image_path !== null) {
        $sql .= ", image_path = :image_path";
        $params[':image_path'] = $image_path;
    }
    
    if ($is_available !== null) {
        $sql .= ", is_available = :is_available";
        $params[':is_available'] = $is_available;
    }
    
    $sql .= " WHERE id = :item_id";
    
    return executeQuery($sql, $params);
}

// Delete menu item
// Delete menu item
function deleteMenuItem($item_id) {
    if (!isAdmin()) return false;
    
    // First check if item exists in any order items or cart
    $check_order_items = "SELECT COUNT(*) as count FROM ORDER_ITEMS WHERE item_id = :item_id";
    $check_cart = "SELECT COUNT(*) as count FROM CART WHERE item_id = :item_id";
    
    $order_items_check = fetchOne($check_order_items, [':item_id' => $item_id]);
    $cart_check = fetchOne($check_cart, [':item_id' => $item_id]);
    
    // If item is referenced anywhere, mark as unavailable instead of deleting
    if (($order_items_check && $order_items_check['count'] > 0) || 
        ($cart_check && $cart_check['count'] > 0)) {
        // Mark as unavailable
        $sql = "UPDATE MENU_ITEMS SET is_available = 0 WHERE id = :item_id";
    } else {
        // Delete completely if not referenced anywhere
        $sql = "DELETE FROM MENU_ITEMS WHERE id = :item_id";
    }
    
    return executeQuery($sql, [':item_id' => $item_id]);
}
// Get sales statistics
function getSalesStats() {
    if (!isAdmin()) return [];
    
    $sql = "SELECT 
            COUNT(*) as total_orders,
            SUM(total_price) as total_revenue,
            AVG(total_price) as avg_order_value,
            MIN(order_date) as first_order_date,
            MAX(order_date) as last_order_date
            FROM ORDERS";
    
    return fetchOne($sql);
}

// Get popular items
function getPopularItems($limit = 5) {
    if (!isAdmin()) return [];
    
    $sql = "SELECT 
            m.id, m.name, m.price,
            SUM(oi.quantity) as total_sold,
            SUM(oi.total) as total_revenue
            FROM ORDER_ITEMS oi
            JOIN MENU_ITEMS m ON oi.item_id = m.id
            GROUP BY m.id, m.name, m.price
            ORDER BY total_sold DESC";
    
    return fetchAll($sql);
}
?>