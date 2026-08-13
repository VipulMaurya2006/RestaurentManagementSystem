<?php
session_start();
require_once '../database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $reservation_id = $_POST['reservation_id'];
    $status = $_POST['status'];
    $table_number = $_POST['table_number'] ?: NULL;
    $stmt = $conn->prepare("UPDATE reservations SET status = ?, table_number = ? WHERE id = ?");
    $stmt->bind_param("sii", $status, $table_number, $reservation_id);
    $stmt->execute();
}

// Get all reservations
$reservations = $conn->query("SELECT r.*, u.username FROM reservations r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.date DESC, r.time DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservations Management - Grill N' Chill</title>
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
                <a href="dashboard.php"><i class="fa-solid fa-home"></i> Dashboard</a>
                <a href="menu.php"><i class="fa-solid fa-utensils"></i> Menu Management</a>
                <a href="orders.php"><i class="fa-solid fa-receipt"></i> Orders</a>
                <a href="reservations.php" class="active"><i class="fa-solid fa-calendar"></i> Reservations</a>
                <a href="users.php"><i class="fa-solid fa-users"></i> Users</a>
                <a href="tables.php"><i class="fa-solid fa-chair"></i> Tables</a>
                <a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>Reservations Management</h1>
            </div>

            <div class="reservations-table-container">
                <table class="reservations-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Date & Time</th>
                            <th>Guests</th>
                            <th>Table</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($reservation = $reservations->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $reservation['id']; ?></td>
                            <td>
                                <strong><?php echo $reservation['name']; ?></strong>
                                <?php if ($reservation['username']): ?>
                                <br><small><?php echo $reservation['username']; ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo $reservation['phone']; ?>
                                <br><small><?php echo $reservation['email']; ?></small>
                            </td>
                            <td>
                                <?php echo date('M j, Y', strtotime($reservation['date'])); ?>
                                <br><?php echo date('g:i A', strtotime($reservation['time'])); ?>
                            </td>
                            <td><?php echo $reservation['number_of_guests']; ?></td>
                            <td><?php echo $reservation['table_number'] ?: '-'; ?></td>
                            <td><span class="status <?php echo $reservation['status']; ?>"><?php echo ucfirst($reservation['status']); ?></span></td>
                            <td>
                                <form method="POST" action="" class="status-form">
                                    <input type="hidden" name="update_status" value="1">
                                    <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="pending" <?php echo $reservation['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $reservation['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="seated" <?php echo $reservation['status'] == 'seated' ? 'selected' : ''; ?>>Seated</option>
                                        <option value="completed" <?php echo $reservation['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $reservation['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <input type="number" name="table_number" placeholder="Table #" value="<?php echo $reservation['table_number']; ?>" style="width: 70px; margin-left: 5px;">
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
