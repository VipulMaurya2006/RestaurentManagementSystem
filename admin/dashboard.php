<?php
session_start();
require_once '../database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get statistics
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];
$total_reservations = $conn->query("SELECT COUNT(*) as count FROM reservations")->fetch_assoc()['count'];
$pending_reservations = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_menu_items = $conn->query("SELECT COUNT(*) as count FROM menu_items")->fetch_assoc()['count'];

// Get recent orders
$recent_orders = $conn->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");

// Get recent reservations
$recent_reservations = $conn->query("SELECT * FROM reservations ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Grill N' Chill</title>
    <link rel="stylesheet" href="../index.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
                <p>Welcome, <?php echo $_SESSION['full_name']; ?></p>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="active"><i class="fa-solid fa-home"></i> Dashboard</a>
                <a href="menu.php"><i class="fa-solid fa-utensils"></i> Menu Management</a>
                <a href="orders.php"><i class="fa-solid fa-receipt"></i> Orders</a>
                <a href="reservations.php"><i class="fa-solid fa-calendar"></i> Reservations</a>
                <a href="users.php"><i class="fa-solid fa-users"></i> Users</a>
                <a href="tables.php"><i class="fa-solid fa-chair"></i> Tables</a>
                <a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>Dashboard Overview</h1>
                <span class="date"><?php echo date('F j, Y'); ?></span>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon orders">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_orders; ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $pending_orders; ?></h3>
                        <p>Pending Orders</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon reservations">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_reservations; ?></h3>
                        <p>Total Reservations</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon pending-res">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $pending_reservations; ?></h3>
                        <p>Pending Reservations</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_users; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon menu">
                        <i class="fa-solid fa-utensils"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_menu_items; ?></h3>
                        <p>Menu Items</p>
                    </div>
                </div>
            </div>

            <div class="recent-section">
                <div class="recent-orders">
                    <h2>Recent Orders</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $recent_orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo $order['username']; ?></td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td><span class="status <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="recent-reservations">
                    <h2>Recent Reservations</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Date</th>
                                <th>Guests</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($reservation = $recent_reservations->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $reservation['id']; ?></td>
                                <td><?php echo $reservation['name']; ?></td>
                                <td><?php echo date('M j, Y', strtotime($reservation['date'])); ?></td>
                                <td><?php echo $reservation['number_of_guests']; ?></td>
                                <td><span class="status <?php echo $reservation['status']; ?>"><?php echo ucfirst($reservation['status']); ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
