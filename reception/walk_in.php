<?php
session_start();
require_once '../database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'reception') {
    header('Location: ../login.php');
    exit();
}

// Handle walk-in customer seating
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['seat_customer'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $number_of_guests = $_POST['number_of_guests'];
    $table_number = $_POST['table_number'];
    
    // Create reservation for walk-in
    $stmt = $conn->prepare("INSERT INTO reservations (user_id, name, phone, date, time, number_of_guests, table_number, status) VALUES (NULL, ?, ?, CURDATE(), CURTIME(), ?, ?, 'seated')");
    $stmt->bind_param("ssii", $name, $phone, $number_of_guests, $table_number);
    $stmt->execute();
    
    // Update table status
    $stmt = $conn->prepare("UPDATE tables SET status = 'occupied' WHERE table_number = ?");
    $stmt->bind_param("i", $table_number);
    $stmt->execute();
    
    $success = "Customer seated successfully at Table $table_number";
}

// Get available tables
$available_tables = $conn->query("SELECT * FROM tables WHERE status = 'available' ORDER BY table_number");

// Get today's walk-ins
$walk_ins = $conn->query("SELECT * FROM reservations WHERE user_id IS NULL AND date = CURDATE() ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Walk-in Customers - Grill N' Chill</title>
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
                <a href="tables.php"><i class="fa-solid fa-chair"></i> Table Management</a>
                <a href="walk_in.php" class="active"><i class="fa-solid fa-user-plus"></i> Walk-in Customers</a>
                <a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>Walk-in Customers</h1>
            </div>

            <?php if (isset($success)): ?>
            <div class="success-alert">
                <i class="fa-solid fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
            <?php endif; ?>

            <div class="walk-in-content">
                <div class="seat-customer-card">
                    <h2>Seat Walk-in Customer</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="seat_customer" value="1">
                        <div class="form-group">
                            <label>Customer Name</label>
                            <input type="text" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" required>
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
                        <div class="form-group">
                            <label>Select Available Table</label>
                            <select name="table_number" required>
                                <option value="">Choose a table</option>
                                <?php while ($table = $available_tables->fetch_assoc()): ?>
                                <option value="<?php echo $table['table_number']; ?>">
                                    Table <?php echo $table['table_number']; ?> - <?php echo $table['capacity']; ?> guests (<?php echo $table['location']; ?>)
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn-submit">Seat Customer</button>
                    </form>
                </div>

                <div class="today-walkins-card">
                    <h2>Today's Walk-ins</h2>
                    <div class="walkins-list">
                        <?php if ($walk_ins->num_rows == 0): ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-user-clock"></i>
                            <p>No walk-in customers today</p>
                        </div>
                        <?php else: ?>
                        <?php while ($walk_in = $walk_ins->fetch_assoc()): ?>
                        <div class="walkin-item">
                            <div class="walkin-info">
                                <h3><?php echo $walk_in['name']; ?></h3>
                                <p><?php echo $walk_in['phone']; ?></p>
                                <p><?php echo $walk_in['number_of_guests']; ?> guests</p>
                            </div>
                            <div class="walkin-details">
                                <span class="table-badge">Table <?php echo $walk_in['table_number']; ?></span>
                                <span class="time-badge"><?php echo date('g:i A', strtotime($walk_in['created_at'])); ?></span>
                                <span class="status <?php echo $walk_in['status']; ?>"><?php echo ucfirst($walk_in['status']); ?></span>
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
