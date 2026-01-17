<?php
require_once '../php/db.php';

$error = '';
$message = getMessage();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password';
    } else {
        // Check user
        $sql = "SELECT id, username, email, password, is_admin FROM users WHERE email = :email";
        $user = fetchOne($sql, [':email' => $email]);
        
        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            // Set success message
            setMessage('success', 'Welcome back, ' . $user['username'] . '!');
            
            // Redirect to menu
            header('Location: ' . SITE_URL . '/index.php');
            exit;
        } else {
            $error = 'Invalid email or password';
        }
    }
}

// Check if already logged in
if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FoodSHOP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <style>
        .auth-section {
            min-height: 100vh;
            padding: 100px 20px 50px;
            background: var(--light-pink-color);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-container {
            background: var(--white-color);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 10px 30px rgba(139, 0, 0, 0.1);
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-header h2 {
            color: var(--primary-color);
            font-size: var(--font-size-xl);
            margin-bottom: 10px;
        }

        .auth-header p {
            color: #666;
            font-size: var(--font-size-m);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark-color);
            font-weight: var(--font-weight-medium);
            font-size: var(--font-size-m);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--medium-gray-color);
            border-radius: var(--border-radius-s);
            font-size: var(--font-size-m);
            transition: all 0.3s ease;
            background: var(--white-color);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
        }

        .btn-primary {
            width: 100%;
            padding: 15px;
            background: var(--primary-color);
            color: var(--white-color);
            border: none;
            border-radius: var(--border-radius-s);
            font-size: var(--font-size-m);
            font-weight: var(--font-weight-bold);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary:hover {
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

        .auth-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--medium-gray-color);
        }

        .auth-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: var(--font-weight-medium);
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 15px;
            border-radius: var(--border-radius-s);
            margin-bottom: 20px;
            font-size: var(--font-size-m);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: var(--font-size-s);
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .forgot-link {
            color: var(--primary-color);
            text-decoration: none;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .demo-note {
            background: var(--light-pink-color);
            border-radius: var(--border-radius-s);
            padding: 15px;
            margin-top: 20px;
            font-size: var(--font-size-s);
            text-align: center;
        }

        .demo-note h4 {
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: var(--font-size-m);
        }
    </style>
</head>
<body>
    <!-- Header - Same as index.html -->
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
                <li class="nav-item">
                    <a href="register.php" class="nav-link">Register</a>
                </li>
            </ul>
            <button id="menu-open-button" class="fas fa-bars"></button>
        </nav>
    </header>

    <!-- Login Form -->
    <section class="auth-section">
        <div class="auth-container">
            <div class="auth-header">
                <h2>Welcome Back!</h2>
                <p>Sign in to your FoodSHOP account</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <i class="fas fa-<?php echo $message['type'] === 'success' ? 'check-circle' : 'info-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message['text']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required 
                           placeholder="Enter your email"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required 
                           placeholder="Enter your password">
                </div>

                <div class="remember-forgot">
                    <label class="remember">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="forgot.php" class="forgot-link">Forgot Password?</a>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>

                <a href="register.php" class="btn-secondary">
                    <i class="fas fa-user-plus"></i> Create New Account
                </a>
            </form>

            

            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Sign up here</a></p>
                <p><a href="../index.php">← Back to Home</a></p>
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
            </div>
            <div class="footer-bottom">
                <p class="copyright-text">© 2024 FoodSHOP. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../script.js"></script>
</body>
</html>