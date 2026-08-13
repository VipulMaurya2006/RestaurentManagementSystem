<?php
session_start();
require_once '../database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'reception') {
    header('Location: ../login.php');
    exit();
}

// Handle reservation actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve_reservation'])) {
        $reservation_id = $_POST['reservation_id'];
        $table_number = $_POST['table_number'] ?: NULL;
        $stmt = $conn->prepare("UPDATE reservations SET status = 'confirmed', table_number = ? WHERE id = ?");
        $stmt->bind_param("ii", $table_number, $reservation_id);
        $stmt->execute();
    } elseif (isset($_POST['update_status'])) {
        $reservation_id = $_POST['reservation_id'];
        $status = $_POST['status'];
        $table_number = $_POST['table_number'] ?: NULL;
        $stmt = $conn->prepare("UPDATE reservations SET status = ?, table_number = ? WHERE id = ?");
        $stmt->bind_param("sii", $status, $table_number, $reservation_id);
        $stmt->execute();
    } elseif (isset($_POST['cancel_reservation'])) {
        $reservation_id = $_POST['reservation_id'];
        $stmt = $conn->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();
    }
}

// Get all reservations
$reservations = $conn->query("SELECT r.*, u.username FROM reservations r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.date DESC, r.time DESC");

// Get available tables
$available_tables = $conn->query("SELECT * FROM tables WHERE status = 'available' ORDER BY table_number");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservations - Grill N' Chill</title>
    <link rel="stylesheet" href="../index.css">
    <link rel="stylesheet" href="reception.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>
    <div class="reception-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Reception Panel</h2>
                <p>Welcome, <?php echo $_SESSION['full_name']; ?></p>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php"><i class="fa-solid fa-home"></i> Dashboard</a>
                <a href="reservations.php" class="active"><i class="fa-solid fa-calendar"></i> Reservations</a>
                <a href="tables.php"><i class="fa-solid fa-chair"></i> Table Management</a>
                <a href="walk_in.php"><i class="fa-solid fa-user-plus"></i> Walk-in Customers</a>
                <a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>Reservation Management</h1>
                <button class="btn-add" onclick="document.getElementById('addModal').style.display='block'">
                    <i class="fa-solid fa-plus"></i> New Reservation
                </button>
            </div>

            <div class="filter-tabs">
                <a href="reservations.php" class="active">All</a>
                <a href="reservations.php?filter=pending">Pending</a>
                <a href="reservations.php?filter=confirmed">Confirmed</a>
                <a href="reservations.php?filter=today">Today</a>
            </div>

            <div class="reservations-table-container">
                <table class="reservations-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
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
                                <div class="action-buttons">
                                    <?php if ($reservation['status'] == 'pending'): ?>
                                    <form method="POST" action="" class="quick-action">
                                        <input type="hidden" name="approve_reservation" value="1">
                                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                        <select name="table_number" required>
                                            <option value="">Select Table</option>
                                            <?php 
                                            $available_tables->data_seek(0);
                                            while ($table = $available_tables->fetch_assoc()): ?>
                                            <option value="<?php echo $table['table_number']; ?>">Table <?php echo $table['table_number']; ?> (<?php echo $table['capacity']; ?>)</option>
                                            <?php endwhile; ?>
                                        </select>
                                        <button type="submit" class="btn-approve"><i class="fa-solid fa-check"></i></button>
                                    </form>
                                    <?php endif; ?>
                                    
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
                                    </form>
                                    
                                    <?php if ($reservation['status'] != 'cancelled' && $reservation['status'] != 'completed'): ?>
                                    <form method="POST" action="" class="cancel-form">
                                        <input type="hidden" name="cancel_reservation" value="1">
                                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                        <button type="submit" class="btn-cancel" onclick="return confirm('Cancel this reservation?')">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Reservation Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
            <h2>New Reservation</h2>
            <form method="POST" action="">
                <input type="hidden" name="add_reservation" value="1">
                <div class="form-group">
                    <label>Customer Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="date" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Time</label>
                        <input type="time" name="time" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Number of Guests</label>
                    <select name="number_of_guests" required>
                        <option value="1">1 Guest</option>
                        <option value="2">2 Guests</option>
                        <option value="3">3 Guests</option>
                        <option value="4">4 Guests</option>
                        <option value="5">5 Guests</option>
                        <option value="6">6 Guests</option>
                        <option value="7">7 Guests</option>
                        <option value="8">8 Guests</option>
                        <option value="9">9 Guests</option>
                        <option value="10">10+ Guests</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit">Create Reservation</button>
            </form>
        </div>
    </div>
</body>
</html>
