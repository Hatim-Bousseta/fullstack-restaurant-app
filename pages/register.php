<?php
require_once '../php/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $address = trim($_POST['address'] ?? '');
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // Check if email already exists
        $sql = "SELECT id FROM users WHERE email = :email";
        $existing = fetchOne($sql, [':email' => $email]);
        
        if ($existing) {
            $error = 'Email already registered';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $user_data = [
                'username' => $username,
                'email' => $email,
                'phone' => $phone,
                'password' => $hashed_password,
                'address' => $address,
                'is_admin' => 0
            ];
            
            $user_id = insert('users', $user_data);
            
            if ($user_id) {
                // Auto login after registration
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $_SESSION['is_admin'] = 0;
                
                // Set success message
                setMessage('success', 'Welcome to FoodSHOP, ' . $username . '!');
                
                // Redirect to home
                header('Location: ' . SITE_URL . '/index.php');
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
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
    <title>Register - FoodSHOP</title>
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
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(139, 0, 0, 0.1);
            animation: fadeIn 0.5s ease;
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

        .form-group label.required::after {
            content: " *";
            color: var(--primary-color);
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

        .password-strength {
            height: 5px;
            background: var(--medium-gray-color);
            border-radius: var(--border-radius-s);
            margin-top: 5px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease, background-color 0.3s ease;
        }

        .password-requirements {
            font-size: var(--font-size-s);
            color: #666;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
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
                <li class="nav-item">
                    <a href="login.php" class="nav-link">Login</a>
                </li>
            </ul>
            <button id="menu-open-button" class="fas fa-bars"></button>
        </nav>
    </header>

    <!-- Registration Form -->
    <section class="auth-section">
        <div class="auth-container">
            <div class="auth-header">
                <h2>Join FoodSHOP</h2>
                <p>Create your account to start ordering delicious food</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="registerForm">
                <div class="form-group">
                    <label for="username" class="required">Full Name</label>
                    <input type="text" id="username" name="username" class="form-control" required 
                           placeholder="Enter your full name" 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="email" class="required">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required 
                           placeholder="Enter your email"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password" class="required">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required 
                               placeholder="Enter password">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password" class="required">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required 
                               placeholder="Confirm password">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control"
                               placeholder="Enter phone number"
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Delivery Address</label>
                    <input type="text" id="address" name="address" class="form-control"
                           placeholder="Enter delivery address"
                           value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Sign in here</a></p>
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

    <script>
        // Password validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const form = document.getElementById('registerForm');

        function validatePassword() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity("Passwords don't match");
                return false;
            } else {
                confirmPassword.setCustomValidity('');
                return true;
            }
        }

        password.addEventListener('change', validatePassword);
        confirmPassword.addEventListener('keyup', validatePassword);

        form.addEventListener('submit', function(e) {
            if (!validatePassword()) {
                e.preventDefault();
                alert('Please make sure passwords match!');
                return false;
            }
            
            if (password.value.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long');
                return false;
            }
            
            return true;
        });
    </script>
    <script src="../script.js"></script>
</body>
</html>