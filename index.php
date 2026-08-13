<?php
session_start();
require_once 'database.php';

// Redirect logged-in users to their respective dashboards
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header('Location: admin/dashboard.php');
            break;
        case 'customer':
            header('Location: customer/menu.php');
            break;
        case 'reception':
            header('Location: reception/dashboard.php');
            break;
        default:
            header('Location: login.php');
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grill N' Chill - Restaurant Management System</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body>
    <nav class="navbar">
        <a href="index.php" class="logo">Grill N' Chill</a>
        <div class="nav-items">
            <a href="#about" class="nav-item">About</a>
            <a href="#features" class="nav-item">Features</a>
        </div>
        <div class="nav-auth">
            <a href="login.php" class="btn-login">Login</a>
            <a href="register.php" class="btn-register">Register</a>
        </div>
    </nav>
    
    <div class="content">
        <div class="hero">
            <div class="hero-text">
                <h1>Welcome to <span>Grill N' Chill</span></h1>

                <p>
                    Experience the perfect blend of delicious flavors, warm hospitality,
                    and a cozy atmosphere at <strong>Grill N' Chill</strong>. From
                    sizzling grilled specialties to mouth-watering desserts, every dish
                    is prepared with fresh ingredients and served with passion.
                </p>

                <p>
                    Whether you're planning a family dinner, a romantic date, or a
                    celebration with friends, we promise an unforgettable dining
                    experience filled with taste, comfort, and exceptional service.
                </p>

                <div class="hero-buttons">
                    <a href="register.php" class="btn-primary">
                        Get Started <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    <a href="login.php" class="btn-secondary">
                        Login <i class="fa-solid fa-user"></i>
                    </a>
                </div>  
            </div>

            <div class="hero-image">
                <img src="images/Pizza.png" alt="Restaurant Food">
            </div>
        </div>
        
        <div id="features" class="features-section">
            <h2>Our Features</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fa-solid fa-utensils"></i>
                    <h3>Easy Ordering</h3>
                    <p>Browse our menu and place orders online with just a few clicks.</p>
                </div>
                <div class="feature-card">
                    <i class="fa-solid fa-calendar-check"></i>
                    <h3>Table Reservations</h3>
                    <p>Reserve your table in advance to avoid waiting.</p>
                </div>
                <div class="feature-card">
                    <i class="fa-solid fa-truck"></i>
                    <h3>Fast Delivery</h3>
                    <p>Get your food delivered hot and fresh to your doorstep.</p>
                </div>
                <div class="feature-card">
                    <i class="fa-solid fa-headset"></i>
                    <h3>24/7 Support</h3>
                    <p>Our customer support team is always ready to help you.</p>
                </div>
            </div>
        </div>
        
        <div id="about" class="about-section">
            <h2>About Our System</h2>
            <p>Our restaurant management system provides three user perspectives:</p>
            <div class="user-roles">
                <div class="role-card">
                    <i class="fa-solid fa-user-shield"></i>
                    <h3>Admin</h3>
                    <p>Manage menu items, orders, reservations, users, and tables with full control.</p>
                </div>
                <div class="role-card">
                    <i class="fa-solid fa-user"></i>
                    <h3>Customer</h3>
                    <p>Browse menu, place orders, make reservations, and track order history.</p>
                </div>
                <div class="role-card">
                    <i class="fa-solid fa-bell-concierge"></i>
                    <h3>Reception</h3>
                    <p>Manage reservations, table assignments, and walk-in customers efficiently.</p>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="footer">
        <p>&copy; 2026 Grill N' Chill. All rights reserved.</p>
    </footer>
</body>

</html>