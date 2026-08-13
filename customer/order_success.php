<?php
session_start();
require_once '../database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../login.php');
    exit();
}

$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : 0;

// Get order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - Grill N' Chill</title>
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
            <a href="my_orders.php">My Orders</a>
        </div>
        <div class="nav-icons">
            <a href="cart.php" class="cart-icon" title="Cart">
                <i class="fa-solid fa-cart-shopping"></i>
            </a>
            <a href="../logout.php" class="logout-icon" title="Logout">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </nav>

    <div class="customer-container">
        <div class="success-message">
            <div class="success-icon">
                <i class="fa-solid fa-check-circle"></i>
            </div>
            <h1>Order Placed Successfully!</h1>
            <p>Your order #<?php echo $order_id; ?> has been received</p>
            <p class="order-status">Status: <span><?php echo ucfirst($order['status']); ?></span></p>
            <p class="order-total">Total: $<?php echo number_format($order['total_amount'] * 1.10, 2); ?></p>
            <div class="success-actions">
                <a href="my_orders.php" class="btn-primary">View My Orders</a>
                <a href="menu.php" class="btn-secondary">Order More</a>
            </div>
        </div>
    </div>
</body>
</html>
