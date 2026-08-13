<?php
session_start();
require_once '../database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'reception') {
    header('Location: ../login.php');
    exit();
}

// Handle table status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_status'])) {
        $table_id = $_POST['table_id'];
        $status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE tables SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $table_id);
        $stmt->execute();
    }
}

// Get all tables
$tables = $conn->query("SELECT * FROM tables ORDER BY table_number");

// Get table statistics
$total_tables = $conn->query("SELECT COUNT(*) as count FROM tables")->fetch_assoc()['count'];
$available_tables = $conn->query("SELECT COUNT(*) as count FROM tables WHERE status = 'available'")->fetch_assoc()['count'];
$occupied_tables = $conn->query("SELECT COUNT(*) as count FROM tables WHERE status = 'occupied'")->fetch_assoc()['count'];
$reserved_tables = $conn->query("SELECT COUNT(*) as count FROM tables WHERE status = 'reserved'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Table Management - Grill N' Chill</title>
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
                <a href="reservations.php"><i class="fa-solid fa-calendar"></i> Reservations</a>
                <a href="tables.php" class="active"><i class="fa-solid fa-chair"></i> Table Management</a>
                <a href="walk_in.php"><i class="fa-solid fa-user-plus"></i> Walk-in Customers</a>
                <a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>Table Management</h1>
            </div>

            <div class="table-stats">
                <div class="stat-box">
                    <span class="stat-number"><?php echo $total_tables; ?></span>
                    <span class="stat-label">Total Tables</span>
                </div>
                <div class="stat-box available">
                    <span class="stat-number"><?php echo $available_tables; ?></span>
                    <span class="stat-label">Available</span>
                </div>
                <div class="stat-box occupied">
                    <span class="stat-number"><?php echo $occupied_tables; ?></span>
                    <span class="stat-label">Occupied</span>
                </div>
                <div class="stat-box reserved">
                    <span class="stat-number"><?php echo $reserved_tables; ?></span>
                    <span class="stat-label">Reserved</span>
                </div>
            </div>

            <div class="tables-grid">
                <?php while ($table = $tables->fetch_assoc()): ?>
                <div class="table-card">
                    <div class="table-header">
                        <h3>Table <?php echo $table['table_number']; ?></h3>
                        <span class="status <?php echo $table['status']; ?>"><?php echo ucfirst($table['status']); ?></span>
                    </div>
                    <div class="table-body">
                        <div class="table-info">
                            <p><i class="fa-solid fa-users"></i> <?php echo $table['capacity']; ?> Guests</p>
                            <p><i class="fa-solid fa-location-dot"></i> <?php echo $table['location']; ?></p>
                        </div>
                        <div class="table-visual">
                            <div class="table-shape <?php echo $table['capacity'] > 4 ? 'large' : ''; ?>">
                                <span><?php echo $table['table_number']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="table-footer">
                        <form method="POST" action="" class="status-form">
                            <input type="hidden" name="update_status" value="1">
                            <input type="hidden" name="table_id" value="<?php echo $table['id']; ?>">
                            <select name="status" onchange="this.form.submit()">
                                <option value="available" <?php echo $table['status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                                <option value="occupied" <?php echo $table['status'] == 'occupied' ? 'selected' : ''; ?>>Occupied</option>
                                <option value="reserved" <?php echo $table['status'] == 'reserved' ? 'selected' : ''; ?>>Reserved</option>
                            </select>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</body>
</html>
