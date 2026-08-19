<?php
session_start();
require_once '../database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../login.php');
    exit();
}

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_quantity'])) {
        $item_id = $_POST['item_id'];
        $quantity = $_POST['quantity'];
        
        if ($quantity <= 0) {
            // Remove item
            foreach ($_SESSION['cart'] as $key => $item) {
                if ($item['id'] == $item_id) {
                    unset($_SESSION['cart'][$key]);
                    break;
                }
            }
        } else {
            // Update quantity
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['id'] == $item_id) {
                    $item['quantity'] = $quantity;
                    break;
                }
            }
        }
    } elseif (isset($_POST['remove_item'])) {
        $item_id = $_POST['item_id'];
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['id'] == $item_id) {
                unset($_SESSION['cart'][$key]);
                break;
            }
        }
    } elseif (isset($_POST['place_order'])) {
        // Place order
        if (!empty($_SESSION['cart'])) {
            $total_amount = 0;
            foreach ($_SESSION['cart'] as $item) {
                $total_amount += $item['price'] * $item['quantity'];
            }
            
            $order_type = $_POST['order_type'];
            $table_number = $order_type == 'dine_in' ? $_POST['table_number'] : NULL;
            
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, order_type, table_number, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->bind_param("idsi", $_SESSION['user_id'], $total_amount, $order_type, $table_number);
            $stmt->execute();
            
            $order_id = $conn->insert_id;
            
            // Add order items
            foreach ($_SESSION['cart'] as $item) {
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
                $stmt->execute();
            }
            
            // Clear cart
            $_SESSION['cart'] = [];
            
            header('Location: order_success.php?order_id=' . $order_id);
            exit();
        }
    }
}

// Get cart items
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}

// Get available tables
$tables = $conn->query("SELECT * FROM tables WHERE status = 'available' ORDER BY table_number");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Grill N' Chill</title>
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
            <a href="cart.php" class="cart-icon active" title="Cart">
                <i class="fa-solid fa-cart-shopping"></i>
                <?php if (count($cart_items) > 0): ?>
                <span class="cart-count"><?php echo count($cart_items); ?></span>
                <?php endif; ?>
            </a>
            <a href="../logout.php" class="logout-icon" title="Logout">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </nav>

    <div class="customer-container">
        <div class="cart-header">
            <h1>Your Cart</h1>
            <p><?php echo count($cart_items); ?> items</p>
        </div>

        <?php if (empty($cart_items)): ?>
        <div class="empty-cart">
            <i class="fa-solid fa-cart-shopping"></i>
            <h2>Your cart is empty</h2>
            <p>Add some delicious items to get started!</p>
            <a href="menu.php" class="btn-primary">Browse Menu</a>
        </div>
        <?php else: ?>
        <div class="cart-content">
            <div class="cart-items">
                <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <div class="cart-item-image">
                        <img src="../images/<?php echo $item['image'] ?: 'placeholder.jpg'; ?>" alt="<?php echo $item['name']; ?>">
                    </div>
                    <div class="cart-item-details">
                        <h3><?php echo $item['name']; ?></h3>
                        <p class="price">₹<?php echo number_format($item['price'], 2); ?></p>
                    </div>
                    <div class="cart-item-quantity">
                        <form method="POST" action="">
                            <input type="hidden" name="update_quantity" value="1">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            <button type="button" onclick="this.form.quantity.value = Math.max(1, parseInt(this.form.quantity.value) - 1); this.form.submit();">-</button>
                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" readonly>
                            <button type="button" onclick="this.form.quantity.value = parseInt(this.form.quantity.value) + 1; this.form.submit();">+</button>
                        </form>
                    </div>
                    <div class="cart-item-total">
                        <p>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                    </div>
                    <div class="cart-item-remove">
                        <form method="POST" action="">
                            <input type="hidden" name="remove_item" value="1">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            <button type="submit"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <div class="summary-card">
                    <h2>Order Summary</h2>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>₹<?php echo number_format($total_amount, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Tax (10%)</span>
                        <span>₹<?php echo number_format($total_amount * 0.10, 2); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>₹<?php echo number_format($total_amount * 1.10, 2); ?></span>
                    </div>

                    <form method="POST" action="" class="order-type-form">
                        <h3>Order Type</h3>
                        <div class="order-type-options">
                            <label>
                                <input type="radio" name="order_type" value="takeaway" checked onchange="toggleTableSelect()">
                                <span>Takeaway</span>
                            </label>
                            <label>
                                <input type="radio" name="order_type" value="delivery" onchange="toggleTableSelect()">
                                <span>Delivery</span>
                            </label>
                            <label>
                                <input type="radio" name="order_type" value="dine_in" onchange="toggleTableSelect()">
                                <span>Dine In</span>
                            </label>
                        </div>
                        <div id="table-select" style="display: none;">
                            <label>Select Table:</label>
                            <select name="table_number">
                                <?php while ($table = $tables->fetch_assoc()): ?>
                                <option value="<?php echo $table['table_number']; ?>">Table <?php echo $table['table_number']; ?> (<?php echo $table['capacity']; ?> guests)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button type="submit" name="place_order" class="btn-checkout">Place Order</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleTableSelect() {
            const orderType = document.querySelector('input[name="order_type"]:checked').value;
            document.getElementById('table-select').style.display = orderType === 'dine_in' ? 'block' : 'none';
        }
    </script>
</body>
</html>
