<?php
session_start();
require_once '../database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'reception') {
    header('Location: ../login.php');
    exit();
}

// Get statistics
$pending_reservations = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'")->fetch_assoc()['count'];
$confirmed_reservations = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'confirmed'")->fetch_assoc()['count'];
$today_reservations = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE date = CURDATE()")->fetch_assoc()['count'];
$available_tables = $conn->query("SELECT COUNT(*) as count FROM tables WHERE status = 'available'")->fetch_assoc()['count'];

// Get today's reservations
$today_reservations_list = $conn->query("SELECT * FROM reservations WHERE date = CURDATE() ORDER BY time ASC");

// Get pending reservations
$pending_reservations_list = $conn->query("SELECT * FROM reservations WHERE status = 'pending' ORDER BY date ASC, time ASC LIMIT 5");

// Get all tables
$tables = $conn->query("SELECT * FROM tables ORDER BY table_number");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reception Dashboard - Grill N' Chill</title>
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
                <a href="dashboard.php" class="active"><i class="fa-solid fa-home"></i> Dashboard</a>
                <a href="reservations.php"><i class="fa-solid fa-calendar"></i> Reservations</a>
                <a href="tables.php"><i class="fa-solid fa-chair"></i> Table Management</a>
                <a href="walk_in.php"><i class="fa-solid fa-user-plus"></i> Walk-in Customers</a>
                <a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>Reception Dashboard</h1>
                <span class="date"><?php echo date('F j, Y'); ?></span>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $pending_reservations; ?></h3>
                        <p>Pending Reservations</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon confirmed">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $confirmed_reservations; ?></h3>
                        <p>Confirmed Today</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon today">
                        <i class="fa-solid fa-calendar-day"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $today_reservations; ?></h3>
                        <p>Total Today</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon tables">
                        <i class="fa-solid fa-chair"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $available_tables; ?></h3>
                        <p>Available Tables</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-sections">
                <div class="section-card">
                    <div class="section-header">
                        <h2>Today's Reservations</h2>
                        <a href="reservations.php" class="view-all">View All</a>
                    </div>
                    <div class="reservations-list">
                        <?php if ($today_reservations_list->num_rows == 0): ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-calendar-xmark"></i>
                            <p>No reservations for today</p>
                        </div>
                        <?php else: ?>
                        <?php while ($reservation = $today_reservations_list->fetch_assoc()): ?>
                        <div class="reservation-item">
                            <div class="reservation-time">
                                <span class="time"><?php echo date('g:i A', strtotime($reservation['time'])); ?></span>
                                <span class="guests"><?php echo $reservation['number_of_guests']; ?> guests</span>
                            </div>
                            <div class="reservation-info">
                                <h3><?php echo $reservation['name']; ?></h3>
                                <p><?php echo $reservation['phone']; ?></p>
                            </div>
                            <span class="status <?php echo $reservation['status']; ?>"><?php echo ucfirst($reservation['status']); ?></span>
                            <?php if ($reservation['table_number']): ?>
                            <span class="table-badge">Table <?php echo $reservation['table_number']; ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-header">
                        <h2>Table Overview</h2>
                        <a href="tables.php" class="view-all">Manage Tables</a>
                    </div>
                    <div class="tables-grid">
                        <?php while ($table = $tables->fetch_assoc()): ?>
                        <div class="table-mini-card">
                            <div class="table-number">Table <?php echo $table['table_number']; ?></div>
                            <div class="table-capacity"><?php echo $table['capacity']; ?> seats</div>
                            <span class="status <?php echo $table['status']; ?>"><?php echo ucfirst($table['status']); ?></span>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-header">
                        <h2>Pending Approvals</h2>
                        <a href="reservations.php" class="view-all">View All</a>
                    </div>
                    <div class="reservations-list">
                        <?php if ($pending_reservations_list->num_rows == 0): ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-check-circle"></i>
                            <p>All reservations processed</p>
                        </div>
                        <?php else: ?>
                        <?php while ($reservation = $pending_reservations_list->fetch_assoc()): ?>
                        <div class="reservation-item">
                            <div class="reservation-time">
                                <span class="date"><?php echo date('M j', strtotime($reservation['date'])); ?></span>
                                <span class="time"><?php echo date('g:i A', strtotime($reservation['time'])); ?></span>
                            </div>
                            <div class="reservation-info">
                                <h3><?php echo $reservation['name']; ?></h3>
                                <p><?php echo $reservation['number_of_guests']; ?> guests</p>
                            </div>
                            <div class="reservation-actions">
                                <form method="POST" action="reservations.php">
                                    <input type="hidden" name="approve_reservation" value="1">
                                    <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                    <button type="submit" class="btn-approve"><i class="fa-solid fa-check"></i></button>
                                </form>
                            </div>
                        </div>
                        <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
