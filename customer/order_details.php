<?php
session_start();
require_once '../database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../login.php');
    exit();
}

$order_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Get order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: my_orders.php');
    exit();
}

// Get order items
$stmt = $conn->prepare("SELECT oi.*, mi.name, mi.image FROM order_items oi JOIN menu_items mi ON oi.menu_item_id = mi.id WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Grill N' Chill</title>
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
        <div class="page-header">
            <a href="my_orders.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Orders</a>
            <h1>Order #<?php echo $order['id']; ?></h1>
        </div>

        <div class="order-details-container">
            <div class="order-info-card">
                <h2>Order Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Date:</label>
                        <span><?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Type:</label>
                        <span><?php echo ucfirst(str_replace('_', ' ', $order['order_type'])); ?></span>
                    </div>
                    <?php if ($order['table_number']): ?>
                    <div class="info-item">
                        <label>Table:</label>
                        <span><?php echo $order['table_number']; ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="info-item">
                        <label>Status:</label>
                        <span class="status <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                    </div>
                </div>
            </div>

            <div class="order-items-card">
                <h2>Order Items</h2>
                <div class="order-items-list">
                    <?php while ($item = $order_items->fetch_assoc()): ?>
                    <div class="order-item-detail">
                        <div class="item-image">
                            <img src="../images/<?php echo $item['image'] ?: 'placeholder.jpg'; ?>" alt="<?php echo $item['name']; ?>">
                        </div>
                        <div class="item-info">
                            <h3><?php echo $item['name']; ?></h3>
                            <p class="quantity">Quantity: <?php echo $item['quantity']; ?></p>
                        </div>
                        <div class="item-price">
                            <p>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <div class="order-total">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                    <div class="total-row">
                        <span>Tax (10%):</span>
                        <span>₹<?php echo number_format($order['total_amount'] * 0.10, 2); ?></span>
                    </div>
                    <div class="total-row final">
                        <span>Total:</span>
                        <span>₹<?php echo number_format($order['total_amount'] * 1.10, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
