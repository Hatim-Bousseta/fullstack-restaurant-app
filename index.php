<?php
require_once 'php/db.php';

// Get message if any
$message = getMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSHOP | Best Food Delivery</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- Linking Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Login Required Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .confirm-modal {
            background: white;
            border-radius: 15px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.3s ease;
            overflow: hidden;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            background: linear-gradient(135deg, #d32f2f, #9B0000);
            color: white;
            padding: 20px 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .close-modal {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            line-height: 1;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .close-modal:hover {
            background: rgba(255,255,255,0.3);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 30px 25px;
        }

        .modal-footer {
            padding: 0 25px 25px;
            display: flex;
            gap: 12px;
        }

        .modal-btn {
            padding: 14px 20px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            flex: 1;
        }

        .cancel-btn {
            background: #f5f5f5;
            color: #666;
            border: 2px solid #f5f5f5;
        }

        .cancel-btn:hover {
            background: #e8e8e8;
            border-color: #e8e8e8;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .confirm-btn {
            background: linear-gradient(135deg, #d32f2f, #9B0000);
            color: white;
            border: 2px solid #d32f2f;
            text-decoration: none;
        }

        .confirm-btn:hover {
            background: linear-gradient(135deg, #b71c1c, #7a0000);
            border-color: #b71c1c;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(211, 47, 47, 0.3);
        }

        /* Delivery Zone Styles */
        .delivery-area {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .delivery-area h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .delivery-area h3 i {
            color: #dc3545;
        }

        .zones-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }

        .zone-option input[type="radio"] {
            display: none;
        }

        .zone-card {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            height: 100%;
        }

        .zone-option input[type="radio"]:checked + .zone-card {
            border-color: var(--primary-color);
            background: linear-gradient(135deg, #fff5f5, #ffffff);
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(220, 53, 69, 0.1);
        }

        .zone-icon {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .zone-option input[type="radio"]:checked + .zone-card .zone-icon {
            border-color: var(--primary-color);
            background: var(--primary-color);
            color: white;
        }

        .zone-icon i {
            font-size: 24px;
            color: #666;
        }

        .zone-option input[type="radio"]:checked + .zone-card .zone-icon i {
            color: white;
        }

        .zone-card h4 {
            margin: 10px 0 5px;
            color: #333;
            font-size: 18px;
        }

        .zone-card p {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .zone-tag {
            display: inline-block;
            background: #d4edda;
            color: #155724;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .zone-result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .zone-result.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
            display: block;
        }

        .zone-result.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .zones-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .zones-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar section-content">
            <a href="#" class="nav-logo">
                <h2 class="logo-text">FoodSHOP</h2>
            </a>

            <ul class="nav-menu">
                <li class="nav-item">
                    <button id="menu-close-button" class="fas fa-times"></button>
                    <a href="#home" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="#about" class="nav-link">About</a>
                </li>
                <li class="nav-item">
                    <a href="#menu" class="nav-link">Menu</a>
                </li>
                <li class="nav-item">
                    <a href="#delivery" class="nav-link">Delivery</a>
                </li>
                <li class="nav-item">
                    <a href="#testimonials" class="nav-link">Reviews</a>
                </li>
                <li class="nav-item">
                    <a href="#contact" class="nav-link">Contact</a>
                </li>
                <li class="nav-item cart-icon">
                    <a href="pages/cart.php" class="nav-link"><i class="fas fa-shopping-cart"></i> <span class="cart-count">0</span></a>
                </li>

                <li class="nav-item">
                    <?php if (isLoggedIn()): ?>
                        <a href="pages/profile.php" class="nav-link">
                            <i class="fas fa-user"></i> 
                            <?php 
                                // Display first name or username
                                $name = $_SESSION['username'];
                                if (strpos($name, ' ') !== false) {
                                    echo htmlspecialchars(explode(' ', $name)[0]);
                                } else {
                                    echo htmlspecialchars($name);
                                }
                            ?>
                        </a>

                    <?php else: ?>
                        <a href="pages/login.php" class="nav-link">
                            <i class="fas fa-user"></i> Account
                        </a>
                    <?php endif; ?>
                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                       <a href="pages/admin.php" class="nav-link">
                    <i class="fas fa-crown"></i> Admin
                      </a>
                    </li>
                   <?php endif; ?>
                </li>
            </ul>
            <button id="menu-open-button" class="fas fa-bars"></button>
        </nav>
    </header>

    <main>
        <!-- Display message if any -->
        <?php if ($message): ?>
            <div class="message-alert" style="position: fixed; top: 80px; left: 50%; transform: translateX(-50%); 
                   z-index: 1000; padding: 15px 30px; border-radius: var(--border-radius-s); 
                   background: <?php echo $message['type'] === 'success' ? '#d4edda' : '#f8d7da'; ?>;
                   color: <?php echo $message['type'] === 'success' ? '#155724' : '#721c24'; ?>;
                   border: 1px solid <?php echo $message['type'] === 'success' ? '#c3e6cb' : '#f5c6cb'; ?>;
                   box-shadow: 0 5px 15px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-<?php echo $message['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message['text']); ?>
            </div>
            <script>
                setTimeout(() => {
                    const alert = document.querySelector('.message-alert');
                    if (alert) alert.style.display = 'none';
                }, 5000);
            </script>
        <?php endif; ?>

        <!-- Hero Section -->
        <section class="hero-section" id="home">
            <div class="section-content">
                <div class="hero-details">
                    <h2 class="title">Delicious Food Delivered</h2>
                    <h3 class="subtitle">Fresh, Hot, and Always Perfect!</h3>
                    <p class="description">From gourmet meals to comfort classics, we bring restaurant-quality food to your door. Cooked with passion and delivered with care in 30 minutes or less.</p>
                    
                    <div class="buttons">
                        <a href="#menu" class="button order-now">Order Now</a>
                        <a href="#delivery" class="button contact-us">Track Delivery</a>
                    </div>
                    
                    <div class="features">
                        <div class="feature">
                            <i class="fas fa-clock"></i>
                            <span>30-Min Delivery</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-leaf"></i>
                            <span>Fresh Ingredients</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-award"></i>
                            <span>Premium Quality</span>
                        </div>
                    </div>
                </div>
                <div class="hero-image-wrapper">
                    <img src="images/burger.png" alt="Delicious Food" class="hero-image">
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section class="about-section" id="about">
            <div class="section-content">
                <div class="about-image-wrapper">
                    <img src="images/chef.png" alt="Our Kitchen Team" class="about-image">
                </div>
                <div class="about-details">
                    <h2 class="section-title">Our Story</h2>
                    <p class="text">Born from a passion for quality food and convenience, FoodSHOP brings restaurant-quality meals to your home. We work with local farmers for fresh ingredients and use special delivery packaging to ensure your food arrives hot and delicious. Our diverse menu caters to all tastes and dietary preferences.</p>
                    
                    <div class="stats">
                        <div class="stat">
                            <h3>5000+</h3>
                            <p>Happy Customers</p>
                        </div>
                        <div class="stat">
                            <h3>28 min</h3>
                            <p>Avg Delivery Time</p>
                        </div>
                        <div class="stat">
                            <h3>4.9 ★</h3>
                            <p>Customer Rating</p>
                        </div>
                    </div>
                    
                    <div class="social-link-list">
                        <a href="#" class="social-link"><i class="fa-brands fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fa-brands fa-x-twitter"></i></a>
                    </div>
                </div>
            </div>
        </section>

        
 <?php
// Get ALL menu items from database
$menu_items = getMenuItems();

// Group by price - items over $8.00 are main dishes, others are sides/drinks
$main_items = [];
$extra_items = [];

foreach ($menu_items as $item) {
    // If price is high (over $8), likely a main dish
    if ($item['price'] > 8.00) {
        $main_items[] = $item;
    } else {
        $extra_items[] = $item;
    }
}
?>

<!-- Main Dishes Section -->
<section class="menu-section" id="menu">
    <h2 class="section-title">Main Dishes</h2>
    <div class="section-content">
        <?php if (empty($main_items)): ?>
            <p style="color: white; text-align: center; padding: 40px; font-size: var(--font-size-l);">
                <i class="fas fa-utensils"></i> Main dishes coming soon!
            </p>
        <?php else: ?>
            <ul class="menu-list">
                <?php foreach ($main_items as $item): ?>
                <li class="menu-item">
                    <?php 
                    $image_file = $item['image_path'] ?? 'default-food.jpg';
                    $image_path = "images/" . $image_file;
                    $actual_image_path = $_SERVER['DOCUMENT_ROOT'] . '/FOOD/' . $image_path;
                    
                    // Check if image exists, use default if not
                    if (!file_exists($actual_image_path)) {
                        $image_path = "images/default-food.jpg";
                    }
                    ?>
                    <img src="<?php echo $image_path; ?>" 
                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                         class="menu-image">
                    <h3 class="name"><?php echo htmlspecialchars($item['name']); ?></h3>
                    <p class="text"><?php echo htmlspecialchars($item['description'] ?? 'Delicious food item'); ?></p>
                    <div class="price-add">
                        <span class="price">$<?php echo number_format($item['price'], 2); ?></span>
                        <?php if (isLoggedIn()): ?>
                            <button class="add-to-cart" 
                                    onclick="addToCart(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['name'])); ?>', <?php echo $item['price']; ?>)">
                                Add to Cart
                            </button>
                        <?php else: ?>
                            <button class="add-to-cart" onclick="showLoginAlert()">
                                Add to Cart
                            </button>
                        <?php endif; ?>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>
    
<!-- Sides & Drinks Section -->
<div class="extras-section">
    <h3 class="subtitle" style="color: black;">Sides & Drinks</h3>
    <div class="extras-list">
        <?php if (empty($extra_items)): ?>
            <p style="text-align: center; padding: 20px; color: #666; font-style: italic;">
                No sides or drinks available at the moment.
            </p>
        <?php else: ?>
            <?php foreach ($extra_items as $item): 
                $image_file = $item['image_path'] ?? 'default-food.jpg';
                $image_path = "images/" . $image_file;
                $actual_image_path = $_SERVER['DOCUMENT_ROOT'] . '/FOOD/' . $image_path;
                
                // Check if image exists, use default if not
                if (!file_exists($actual_image_path)) {
                    $image_path = "images/default-food.jpg";
                }
            ?>
            <div class="extra-item">
                <div class="extra-item-image">
                    <img src="<?php echo $image_path; ?>" 
                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                         class="extra-image">
                </div>
                <div class="extra-item-content">
                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                    <p><?php echo htmlspecialchars($item['description'] ?? 'Tasty side item'); ?></p>
                    <span class="price">$<?php echo number_format($item['price'], 2); ?></span>
                    <?php if (isLoggedIn()): ?>
                        <button class="add-to-cart" 
                                onclick="addToCart(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['name'])); ?>', <?php echo $item['price']; ?>)">
                            Add
                        </button>
                    <?php else: ?>
                        <button class="add-to-cart" onclick="showLoginAlert()">
                            Add
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

        <!-- Delivery Info Section -->
        <section class="delivery-section" id="delivery">
            <div class="section-content">
                <h2 class="section-title" style="color: black;">How It Works</h2>
                <div class="steps">
                    <div class="step">
                        <div class="step-icon">1</div>
                        <h3>Choose Your Meal</h3>
                        <p>Browse our diverse menu and add items to your cart</p>
                    </div>
                    <div class="step">
                        <div class="step-icon">2</div>
                        <h3>Checkout & Pay</h3>
                        <p>Enter your address and secure payment details</p>
                    </div>
                    <div class="step">
                        <div class="step-icon">3</div>
                        <h3>Track Your Order</h3>
                        <p>Watch as we prepare and deliver your food</p>
                    </div>
                    <div class="step">
                        <div class="step-icon">4</div>
                        <h3>Enjoy!</h3>
                        <p>Hot, delicious food at your doorstep</p>
                    </div>
                </div>
                
                <div class="delivery-area">
                    <h3><i class="fas fa-map-marker-alt"></i> Delivery Zones</h3>
                    <p>Select your zone to check delivery availability:</p>
                    
                    <div class="zones-grid">
                        <label class="zone-option">
                            <input type="radio" name="delivery-zone" value="Martil">
                            <div class="zone-card">
                                <div class="zone-icon">
                                    <i class="fas fa-umbrella-beach"></i>
                                </div>
                                <h4>Martil</h4>
                                <p>Beach area delivery</p>
                                <span class="zone-tag">Available</span>
                            </div>
                        </label>
                        
                        <label class="zone-option">
                            <input type="radio" name="delivery-zone" value="Tetouan">
                            <div class="zone-card">
                                <div class="zone-icon">
                                    <i class="fas fa-city"></i>
                                </div>
                                <h4>Tetouan</h4>
                                <p>City center & suburbs</p>
                                <span class="zone-tag">Available</span>
                            </div>
                        </label>
                        
                        <label class="zone-option">
                            <input type="radio" name="delivery-zone" value="Fnideq">
                            <div class="zone-card">
                                <div class="zone-icon">
                                    <i class="fas fa-water"></i>
                                </div>
                                <h4>Fnideq</h4>
                                <p>Coastal area</p>
                                <span class="zone-tag">Available</span>
                            </div>
                        </label>
                        
                        <label class="zone-option">
                            <input type="radio" name="delivery-zone" value="Mdiq">
                            <div class="zone-card">
                                <div class="zone-icon">
                                    <i class="fas fa-fish"></i>
                                </div>
                                <h4>Mdiq</h4>
                                <p>Port area</p>
                                <span class="zone-tag">Available</span>
                            </div>
                        </label>
                    </div>
                    
                    <div id="zone-result" class="zone-result"></div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="testimonials-section" id="testimonials">
           <h2 class="section-title" style="color: black;">Customer Reviews</h2>
            <div class="section-content">
                <div class="slider-container swiper">
                    <div class="slider-wrapper">
                        <ul class="testimonials-list swiper-wrapper">
                            <li class="testimonial swiper-slide">
                                <img src="images/user-1.jpg" alt="Customer" class="user-image">
                                <h3 class="name">Michael Rodriguez</h3>
                                <div class="rating">★★★★★</div>
                                <i class="feedback">"Best food delivery in town! The pasta arrived hot and perfectly cooked. Will order again!"</i>
                            </li>
                            <li class="testimonial swiper-slide">
                                <img src="images/user-2.jpg" alt="Customer" class="user-image">
                                <h3 class="name">Sarah Johnson</h3>
                                <div class="rating">★★★★★</div>
                                <i class="feedback">"The Caesar salad was amazing! Fresh ingredients and generous portions. Delivery was super fast."</i>
                            </li>
                            <li class="testimonial swiper-slide">
                                <img src="images/user-3.jpg" alt="Customer" class="user-image">
                                <h3 class="name">David Chen</h3>
                                <div class="rating">★★★★☆</div>
                                <i class="feedback">"Consistently great quality. The sushi platter was fresh and beautifully presented. Perfect for date night."</i>
                            </li>
                            <li class="testimonial swiper-slide">
                                <img src="images/user-4.jpg" alt="Customer" class="user-image">
                                <h3 class="name">Emma Wilson</h3>
                                <div class="rating">★★★★★</div>
                                <i class="feedback">"Their pizza is to die for! The crust is perfect and toppings are generous. Worth every penny."</i>
                            </li>
                            <li class="testimonial swiper-slide">
                                <img src="images/user-5.jpg" alt="Customer" class="user-image">
                                <h3 class="name">James Thompson</h3>
                                <div class="rating">★★★★★</div>
                                <i class="feedback">"Ordered for a family dinner - everyone loved their meals. The packaging kept everything hot!"</i>
                            </li>
                        </ul>
                        <div class="swiper-pagination"></div>
                        <div class="swiper-slide-button swiper-button-prev"></div>
                        <div class="swiper-slide-button swiper-button-next"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section class="contact-section" id="contact">
            <h2 class="section-title">Contact Us</h2>
            <div class="section-content contact-static">
                <div class="contact-info-full">
                    <h3>Get In Touch</h3>
                    <p class="contact-description">Have questions or feedback? Reach out to us through any of these channels:</p>
                    
                    <ul class="contact-info-list">
                        <li class="contact-info">
                            <i class="fa-solid fa-location-dot"></i>
                            <div>
                                <h4>Our Location</h4>
                                <p>123 Gourmet Street, Food District, NY 10001</p>
                            </div>
                        </li>
                        <li class="contact-info">
                            <i class="fa-solid fa-phone"></i>
                            <div>
                                <h4>Phone Number</h4>
                                <p>(555) 123-4567</p>
                                <small>Mon-Sun: 11:00 AM - 11:00 PM</small>
                            </div>
                        </li>
                        <li class="contact-info">
                            <i class="fa-solid fa-envelope"></i>
                            <div>
                                <h4>Email Address</h4>
                                <p>contact@foodshop.com</p>
                                <p>support@foodshop.com</p>
                            </div>
                        </li>
                        <li class="contact-info">
                            <i class="fa-solid fa-clock"></i>
                            <div>
                                <h4>Business Hours</h4>
                                <p><strong>Monday - Sunday:</strong> 11:00 AM - 11:00 PM</p>
                                <p><strong>Delivery Hours:</strong> 11:00 AM - 10:30 PM</p>
                            </div>
                        </li>
                    </ul>
                    
                    <div class="contact-extra">
                        <h4>Follow Us</h4>
                        <div class="social-link-list">
                            <a href="#" class="social-link"><i class="fa-brands fa-facebook"></i> Facebook</a>
                            <a href="#" class="social-link"><i class="fa-brands fa-instagram"></i> Instagram</a>
                            <a href="#" class="social-link"><i class="fa-brands fa-x-twitter"></i> Twitter</a>
                            <a href="#" class="social-link"><i class="fa-brands fa-tiktok"></i> TikTok</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer Section -->
        <footer class="footer-section" id="footer">
            <div class="section-content">
                <div class="footer-content">
                    <div class="footer-column">
                        <h3> FoodSHOP</h3>
                        <p>Delicious meals delivered hot and fresh to your door.</p>
                    </div>
                    <div class="footer-column">
                        <h3>Quick Links</h3>
                        <a href="#menu">Menu</a>
                        <a href="#delivery">Delivery Info</a>
                        <a href="#contact">Contact</a>
                        <a href="#">Careers</a>
                    </div>
                    <div class="footer-column">
                        <h3>Legal</h3>
                        <a href="#">Privacy Policy</a>
                        <a href="#">Terms of Service</a>
                        <a href="#">Refund Policy</a>
                        <a href="#">Food Safety</a>
                    </div>
                    <div class="footer-column">
                        <h3>Stay Connected</h3>
                        <div class="social-link-list">
                            <a href="#" class="social-link"><i class="fa-brands fa-facebook"></i></a>
                            <a href="#" class="social-link"><i class="fa-brands fa-instagram"></i></a>
                            <a href="#" class="social-link"><i class="fa-brands fa-x-twitter"></i></a>
                            <a href="#" class="social-link"><i class="fa-brands fa-tiktok"></i></a>
                        </div>
                        <p>Subscribe to our newsletter</p>
                        <div class="newsletter">
                            <input type="email" placeholder="Your email">
                            <button>Subscribe</button>
                        </div>
                    </div>
                </div>
                
                <div class="footer-bottom">
                    <p class="copyright-text">© 2024 FoodSHOP. All rights reserved.</p>
                    <p class="payment-methods">
                        <i class="fab fa-cc-visa"></i>
                        <i class="fab fa-cc-mastercard"></i>
                        <i class="fab fa-cc-amex"></i>
                        <i class="fab fa-cc-paypal"></i>
                    </p>
                </div>
            </div>
        </footer>
    </main>

    <!-- Login Required Modal -->
    <div id="loginRequiredModal" class="modal-overlay" style="display: none;">
        <div class="confirm-modal" style="max-width: 450px;">
            <div class="modal-header" style="background: linear-gradient(135deg, #d32f2f, #9B0000);">
                <h3><i class="fas fa-shopping-cart"></i> Login Required</h3>
                <button class="close-modal" onclick="closeLoginModal()">&times;</button>
            </div>
            <div class="modal-body" style="text-align: center; padding: 30px;">
                <div class="modal-icon" style="width: 80px; height: 80px; margin: 0 auto 20px; background: #fff2f2; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-lock" style="font-size: 36px; color: #d32f2f;"></i>
                </div>
                <h4 style="color: #333; margin-bottom: 10px;">Login to Continue</h4>
                <p style="color: #666; font-size: 15px; line-height: 1.5;">
                    Please login to add items to your cart and continue shopping.
                </p>
            </div>
            <div class="modal-footer" style="padding: 0 25px 25px; gap: 10px;">
                <button class="modal-btn cancel-btn" onclick="closeLoginModal()" style="flex: 1; background: #f5f5f5; color: #666;">
                    <i class="fas fa-arrow-left"></i> Continue Shopping
                </button>
                <a href="pages/login.php" class="modal-btn confirm-btn" style="flex: 1; background: linear-gradient(135deg, #d32f2f, #9B0000); color: white; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="fas fa-sign-in-alt"></i> Go to Login
                </a>
            </div>
        </div>
    </div>

    <!-- Cart Sidebar -->
    <div class="cart-sidebar" id="cart-sidebar">
        <div class="cart-header">
            <h3>Your Cart</h3>
            <button class="close-cart">&times;</button>
        </div>
        <div class="cart-body" id="cart-body">
            <!-- Cart items will be loaded dynamically -->
            <div class="cart-empty-message" style="text-align: center; padding: 40px 20px;">
                <i class="fas fa-shopping-cart" style="font-size: 3rem; color: #ccc; margin-bottom: 20px;"></i>
                <p>Your cart is empty</p>
                <p style="font-size: 0.9rem; color: #666; margin-top: 10px;">Add items from the menu</p>
            </div>
        </div>
        <div class="cart-footer">
            <div class="cart-total">
                Total: $<span id="sidebar-total">0.00</span>
            </div>
            <button class="checkout-btn" onclick="goToCheckout()" style="color: white;">
                Checkout
            </button>
        </div>
    </div>

    <!-- Cart Overlay -->
    <div class="cart-overlay" id="cart-overlay"></div>

    <!-- Linking Swiper script -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="script.js"></script>

    <!-- Cart Functionality JavaScript -->
    <script>
    
    // Cart functions
    let cartItems = [];
    let cartCount = 0;
    let cartTotal = 0;

    // Initialize cart from localStorage or server
    function initCart() {
        // Try to get cart from localStorage
        const savedCart = localStorage.getItem('foodshop_cart');
        if (savedCart) {
            try {
                cartItems = JSON.parse(savedCart);
                updateCartDisplay();
            } catch (e) {
                console.error('Error loading cart:', e);
            }
        }
        
        // Update cart count in header
        updateCartHeader();
    }

    // Add item to cart
    function addToCart(itemId, itemName, itemPrice) {
        // Check if user is logged in
        const isLoggedIn = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
        
        if (!isLoggedIn) {
            showLoginAlert();
            return;
        }
        
        // Show loading
        showLoading('Adding to cart...');
        
        // Make AJAX request to add to cart
        fetch('php/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `item_id=${itemId}&quantity=1`
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                // Show success message
                showNotification(itemName + ' added to cart!', 'success');
                
                // Update cart display
                updateCartHeader();
                
                // Refresh cart sidebar if open
                if (document.getElementById('cart-sidebar').classList.contains('active')) {
                    loadCartSidebar();
                }
            } else {
                if (data.login_required) {
                    showLoginAlert();
                } else {
                    showNotification(data.message || 'Failed to add to cart', 'error');
                }
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showNotification('Network error. Please try again.', 'error');
        });
    }

    // Update cart header count
    function updateCartHeader() {
        fetch('php/get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const cartCountElement = document.querySelector('.cart-count');
                    if (cartCountElement) {
                        cartCountElement.textContent = data.cart_count;
                    }
                }
            })
            .catch(error => {
                console.error('Error updating cart count:', error);
            });
    }

    // Show notification
    function showNotification(message, type = 'success') {
        // Remove existing notifications
        const existing = document.querySelector('.cart-notification');
        if (existing) existing.remove();
        
        // Create notification
        const notification = document.createElement('div');
        notification.className = `cart-notification ${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            ${message}
        `;
        
        // Add styles
        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            background: ${type === 'success' ? 'var(--success-color)' : '#dc3545'};
            color: white;
            padding: 15px 25px;
            border-radius: var(--border-radius-s);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 10000;
            animation: slideIn 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            max-width: 300px;
        `;
        
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Show loading indicator
    function showLoading(message = 'Loading...') {
        // Remove existing loading
        hideLoading();
        
        const loading = document.createElement('div');
        loading.id = 'loading-overlay';
        loading.innerHTML = `
            <div class="loading-content">
                <div class="spinner"></div>
                <p>${message}</p>
            </div>
        `;
        
        loading.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
        `;
        
        const style = document.createElement('style');
        style.textContent = `
            .loading-content {
                background: white;
                padding: 30px;
                border-radius: var(--border-radius-m);
                text-align: center;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            }
            .spinner {
                width: 50px;
                height: 50px;
                border: 5px solid var(--light-pink-color);
                border-top: 5px solid var(--primary-color);
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin: 0 auto 15px;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        
        document.head.appendChild(style);
        document.body.appendChild(loading);
    }

    function hideLoading() {
        const loading = document.getElementById('loading-overlay');
        if (loading) loading.remove();
    }

    // Login Modal Functions
    function showLoginAlert() {
        document.getElementById('loginRequiredModal').style.display = 'flex';
        return false;
    }

    function closeLoginModal() {
        document.getElementById('loginRequiredModal').style.display = 'none';
    }

    // Delivery Zone Selection
    document.addEventListener('DOMContentLoaded', function() {
        const zoneRadios = document.querySelectorAll('input[name="delivery-zone"]');
        const zoneResult = document.getElementById('zone-result');
        
        zoneRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    const zone = this.value;
                    
                    showZoneResult(
                        `✓ Delivery available to <strong>${zone}</strong>!<br>
                         <small>Delivery fee: $2.99 • Estimated time: 30-45 minutes</small>`,
                        'success'
                    );
                    
                    const addressField = document.querySelector('textarea[name="delivery_address"]');
                    if (addressField) {
                        addressField.value = `Delivery to ${zone} area`;
                    }
                }
            });
        });
        
        function showZoneResult(message, type) {
            zoneResult.innerHTML = message;
            zoneResult.className = `zone-result ${type}`;
            
            setTimeout(() => {
                zoneResult.style.opacity = '0';
                zoneResult.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    zoneResult.className = 'zone-result';
                    zoneResult.style.opacity = '';
                    zoneResult.style.transform = '';
                }, 300);
            }, 5000);
        }
        
        // Initialize modal handlers
        const modal = document.getElementById('loginRequiredModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeLoginModal();
                }
            });
            
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.style.display === 'flex') {
                    closeLoginModal();
                }
            });
        }
        
        initCart();
        
        // Add click handlers to existing add-to-cart buttons
        document.querySelectorAll('.add-to-cart[data-id]').forEach(button => {
            button.addEventListener('click', function() {
                const itemId = this.getAttribute('data-id');
                const itemName = this.getAttribute('data-name');
                const itemPrice = this.getAttribute('data-price');
                addToCart(itemId, itemName, itemPrice);
            });
        });
        
        // Update cart icon click handler
        document.querySelector('.cart-icon a').addEventListener('click', function(e) {
            e.preventDefault();
            openCartSidebar();
        });
    });

    // Open cart sidebar
    function openCartSidebar() {
        if (!<?php echo isLoggedIn() ? 'true' : 'false'; ?>) {
            showLoginAlert();
            return;
        }
        
        document.getElementById('cart-sidebar').classList.add('active');
        document.getElementById('cart-overlay').classList.add('active');
        loadCartSidebar();
    }

    // Close cart sidebar
    function closeCartSidebar() {
        document.getElementById('cart-sidebar').classList.remove('active');
        document.getElementById('cart-overlay').classList.remove('active');
    }

    // Load cart items into sidebar
    function loadCartSidebar() {
        fetch('php/get_cart_items.php')
            .then(response => response.json())
            .then(data => {
                const cartBody = document.getElementById('cart-body');
                const totalElement = document.getElementById('sidebar-total');
                
                if (data.success && data.items.length > 0) {
                    let html = '';
                    let subtotal = 0;
                    
                    data.items.forEach(item => {
                        const itemTotal = item.price * item.quantity;
                        subtotal += itemTotal;
                        
                        html += `
                            <div class="sidebar-cart-item" data-cart-id="${item.cart_id}">
                                <div class="sidebar-item-image">
                                    <img src="images/${item.image}" alt="${item.name}">
                                </div>
                                <div class="sidebar-item-details">
                                    <h4>${item.name}</h4>
                                    <div class="sidebar-item-controls">
                                        <button onclick="updateCartItem(${item.cart_id}, ${item.quantity - 1})">-</button>
                                        <span>${item.quantity}</span>
                                        <button onclick="updateCartItem(${item.cart_id}, ${item.quantity + 1})">+</button>
                                    </div>
                                </div>
                                <div class="sidebar-item-price">
                                    $${itemTotal.toFixed(2)}
                                    <button class="remove-item" onclick="removeCartItem(${item.cart_id})">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                    
                    cartBody.innerHTML = html;
                    totalElement.textContent = subtotal.toFixed(2);
                    
                    // Remove empty message
                    const emptyMsg = document.querySelector('.cart-empty-message');
                    if (emptyMsg) emptyMsg.style.display = 'none';
                } else {
                    cartBody.innerHTML = `
                        <div class="cart-empty-message" style="text-align: center; padding: 40px 20px;">
                            <i class="fas fa-shopping-cart" style="font-size: 3rem; color: #ccc; margin-bottom: 20px;"></i>
                            <p>Your cart is empty</p>
                            <p style="font-size: 0.9rem; color: #666; margin-top: 10px;">Add items from the menu</p>
                        </div>
                    `;
                    totalElement.textContent = '0.00';
                }
            })
            .catch(error => {
                console.error('Error loading cart:', error);
            });
    }

    // Update cart item quantity
    function updateCartItem(cartId, newQuantity) {
        if (newQuantity < 1) {
            removeCartItem(cartId);
            return;
        }
        
        showLoading('Updating cart...');
        
        fetch('php/update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cart_id=${cartId}&quantity=${newQuantity}`
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                showNotification('Cart updated', 'success');
                loadCartSidebar();
                updateCartHeader();
            } else {
                showNotification(data.message || 'Update failed', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            showNotification('Network error', 'error');
        });
    }

    // Remove cart item
    function removeCartItem(cartId) {
        showLoading('Removing item...');
        
        fetch('php/remove_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cart_id=${cartId}`
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                showNotification('Item removed', 'success');
                loadCartSidebar();
                updateCartHeader();
            } else {
                showNotification(data.message || 'Remove failed', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            showNotification('Network error', 'error');
        });
    }

    // Go to checkout (full cart page)
    function goToCheckout() {
        window.location.href = 'pages/cart.php';
    }

    // Close cart handlers
    document.querySelector('.close-cart').addEventListener('click', closeCartSidebar);
    document.getElementById('cart-overlay').addEventListener('click', closeCartSidebar);
    </script>
        <style>
        /* Login Required Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .confirm-modal {
            background: white;
            border-radius: 15px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.3s ease;
            overflow: hidden;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            background: linear-gradient(135deg, #d32f2f, #9B0000);
            color: white;
            padding: 20px 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .close-modal {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            line-height: 1;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .close-modal:hover {
            background: rgba(255,255,255,0.3);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 30px 25px;
        }

        .modal-footer {
            padding: 0 25px 25px;
            display: flex;
            gap: 12px;
        }

        .modal-btn {
            padding: 14px 20px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            flex: 1;
        }

        .cancel-btn {
            background: #f5f5f5;
            color: #666;
            border: 2px solid #f5f5f5;
        }

        .cancel-btn:hover {
            background: #e8e8e8;
            border-color: #e8e8e8;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .confirm-btn {
            background: linear-gradient(135deg, #d32f2f, #9B0000);
            color: white;
            border: 2px solid #d32f2f;
            text-decoration: none;
        }

        .confirm-btn:hover {
            background: linear-gradient(135deg, #b71c1c, #7a0000);
            border-color: #b71c1c;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(211, 47, 47, 0.3);
        }

        /* Delivery Zone Styles */
        .delivery-area {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .delivery-area h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .delivery-area h3 i {
            color: #dc3545;
        }

        .zones-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }

        .zone-option input[type="radio"] {
            display: none;
        }

        .zone-card {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            height: 100%;
        }

        .zone-option input[type="radio"]:checked + .zone-card {
            border-color: var(--primary-color);
            background: linear-gradient(135deg, #fff5f5, #ffffff);
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(220, 53, 69, 0.1);
        }

        .zone-icon {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .zone-option input[type="radio"]:checked + .zone-card .zone-icon {
            border-color: var(--primary-color);
            background: var(--primary-color);
            color: white;
        }

        .zone-icon i {
            font-size: 24px;
            color: #666;
        }

        .zone-option input[type="radio"]:checked + .zone-card .zone-icon i {
            color: white;
        }

        .zone-card h4 {
            margin: 10px 0 5px;
            color: #333;
            font-size: 18px;
        }

        .zone-card p {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .zone-tag {
            display: inline-block;
            background: #d4edda;
            color: #155724;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .zone-result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .zone-result.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
            display: block;
        }

        .zone-result.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Sidebar Cart Item Styles - ADD THIS BACK */
        .sidebar-cart-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            gap: 15px;
        }

        .sidebar-item-image {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .sidebar-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sidebar-item-details {
            flex: 1;
        }

        .sidebar-item-details h4 {
            margin: 0 0 8px 0;
            font-size: 0.95rem;
            color: #333;
        }

        .sidebar-item-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sidebar-item-controls button {
            width: 25px;
            height: 25px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 4px;
            cursor: pointer;
        }

        .sidebar-item-controls span {
            min-width: 30px;
            text-align: center;
        }

        .sidebar-item-price {
            font-weight: bold;
            color: var(--primary-color);
            position: relative;
        }

        .remove-item {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff6b6b;
            color: white;
            border: none;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            font-size: 0.6rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            display: none;
        }

        .sidebar-cart-item:hover .remove-item {
            display: flex;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .zones-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .zones-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>