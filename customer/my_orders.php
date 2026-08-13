<?php
session_start();
require_once '../database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../login.php');
    exit();
}

// Get user's orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Grill N' Chill</title>
    <link rel="stylesheet" href="../index.css">
    <link rel="stylesheet" href="customer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>
    <nav class="customer-navbar">
        <a href="menu.php" class="logo">Grill N' Chill</a>
        <div class="nav-links">
            <a href="menu.php">Menu</a>
            <a href="reservations.php">Reservations</a>
            <a href="my_orders.php" class="active">My Orders</a>
        </div>
        <div class="nav-icons">
            <a href="cart.php" class="cart-icon" title="Cart">
                <i class="fa-solid fa-cart-shopping"></i>
                <?php 
                $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                if ($cart_count > 0): ?>
                <span class="cart-count"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="../logout.php" class="logout-icon" title="Logout">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </nav>

    <div class="customer-container">
        <div class="page-header">
            <h1>My Orders</h1>
            <p>Track your order history</p>
        </div>

        <?php if ($orders->num_rows == 0): ?>
        <div class="empty-state">
            <i class="fa-solid fa-receipt"></i>
            <h2>No orders yet</h2>
            <p>Start ordering delicious food!</p>
            <a href="menu.php" class="btn-primary">Browse Menu</a>
        </div>
        <?php else: ?>
        <div class="orders-list">
            <?php while ($order = $orders->fetch_assoc()): ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <h3>Order #<?php echo $order['id']; ?></h3>
                        <p class="order-date"><?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                    </div>
                    <span class="status <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                </div>
                <div class="order-details">
                    <p><strong>Type:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['order_type'])); ?></p>
                    <?php if ($order['table_number']): ?>
                    <p><strong>Table:</strong> <?php echo $order['table_number']; ?></p>
                    <?php endif; ?>
                    <p><strong>Total:</strong> $<?php echo number_format($order['total_amount'] * 1.10, 2); ?></p>
                </div>
                <div class="order-actions">
                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn-view">View Details</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
