<?php
session_start();
require_once '../database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../login.php');
    exit();
}

// Get cart count
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

// Get menu items
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
if ($category_filter) {
    $stmt = $conn->prepare("SELECT * FROM menu_items WHERE category = ? AND is_available = 1 ORDER BY name");
    $stmt->bind_param("s", $category_filter);
    $stmt->execute();
    $menu_items = $stmt->get_result();
} else {
    $menu_items = $conn->query("SELECT * FROM menu_items WHERE is_available = 1 ORDER BY category, name");
}

// Get categories
$categories = $conn->query("SELECT DISTINCT category FROM menu_items WHERE is_available = 1 ORDER BY category");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Grill N' Chill</title>
    <link rel="stylesheet" href="../index.css">
    <link rel="stylesheet" href="customer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>
    <nav class="customer-navbar">
        <a href="menu.php" class="logo">Grill N' Chill</a>
        <div class="nav-links">
            <a href="menu.php" class="active">Menu</a>
            <a href="reservations.php">Reservations</a>
            <a href="my_orders.php">My Orders</a>
        </div>
        <div class="nav-icons">
            <a href="cart.php" class="cart-icon" title="Cart">
                <i class="fa-solid fa-cart-shopping"></i>
                <?php if ($cart_count > 0): ?>
                <span class="cart-count"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="../logout.php" class="logout-icon" title="Logout">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </nav>

    <div class="customer-container">
        <div class="menu-header">
            <h1>Our Menu</h1>
            <p>Delicious food made with fresh ingredients</p>
        </div>

        <div class="category-filter">
            <a href="menu.php" class="<?php echo !$category_filter ? 'active' : ''; ?>">All</a>
            <?php while ($cat = $categories->fetch_assoc()): ?>
            <a href="menu.php?category=<?php echo $cat['category']; ?>" class="<?php echo $category_filter == $cat['category'] ? 'active' : ''; ?>">
                <?php echo $cat['category']; ?>
            </a>
            <?php endwhile; ?>
        </div>

        <div class="menu-grid">
            <?php while ($item = $menu_items->fetch_assoc()): ?>
            <div class="menu-item">
                <div class="menu-item-image">
                    <img src="../images/<?php echo $item['image'] ?: 'placeholder.jpg'; ?>" alt="<?php echo $item['name']; ?>">
                </div>
                <div class="menu-item-content">
                    <h3><?php echo $item['name']; ?></h3>
                    <p class="category"><?php echo $item['category']; ?></p>
                    <p class="description"><?php echo $item['description']; ?></p>
                    <div class="menu-item-footer">
                        <span class="price">$<?php echo number_format($item['price'], 2); ?></span>
                        <form method="POST" action="add_to_cart.php">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="add-to-cart">
                                <i class="fa-solid fa-plus"></i> Add to Cart
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
