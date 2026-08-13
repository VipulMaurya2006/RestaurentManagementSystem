<?php
session_start();
require_once '../database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../login.php');
    exit();
}

// Handle reservation submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['make_reservation'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $date = $_POST['date'];
    $time = $_POST['time'];
    $number_of_guests = $_POST['number_of_guests'];
    
    $stmt = $conn->prepare("INSERT INTO reservations (user_id, name, phone, email, date, time, number_of_guests, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("issssi", $_SESSION['user_id'], $name, $phone, $email, $date, $time, $number_of_guests);
    $stmt->execute();
    
    $success = "Reservation submitted successfully! We'll confirm your reservation shortly.";
}

// Get user's reservations
$stmt = $conn->prepare("SELECT * FROM reservations WHERE user_id = ? ORDER BY date DESC, time DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$reservations = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservations - Grill N' Chill</title>
    <link rel="stylesheet" href="../index.css">
    <link rel="stylesheet" href="customer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>
    <nav class="customer-navbar">
        <a href="menu.php" class="logo">Grill N' Chill</a>
        <div class="nav-links">
            <a href="menu.php">Menu</a>
            <a href="reservations.php" class="active">Reservations</a>
            <a href="my_orders.php">My Orders</a>
        </div>
        <div class="nav-icons">
            <a href="cart.php" class="cart-icon" title="Cart">
                <i class="fa-solid fa-cart-shopping"></i>
                <?php 
                $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                if ($cart_count > 0): ?>
                <span class="cart-count"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="../logout.php" class="logout-icon" title="Logout">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </nav>

    <div class="customer-container">
        <div class="page-header">
            <h1>Table Reservations</h1>
            <p>Reserve your table for a memorable dining experience</p>
        </div>

        <?php if (isset($success)): ?>
        <div class="success-alert">
            <i class="fa-solid fa-check-circle"></i>
            <?php echo $success; ?>
        </div>
        <?php endif; ?>

        <div class="reservations-content">
            <div class="reservation-form-card">
                <h2>Make a Reservation</h2>
                <form method="POST" action="">
                    <input type="hidden" name="make_reservation" value="1">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" value="<?php echo $_SESSION['full_name']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo $_SESSION['email']; ?>" required>
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
                    <button type="submit" class="btn-submit">Submit Reservation</button>
                </form>
            </div>

            <div class="my-reservations-card">
                <h2>My Reservations</h2>
                <?php if ($reservations->num_rows == 0): ?>
                <div class="empty-reservations">
                    <i class="fa-solid fa-calendar-xmark"></i>
                    <p>No reservations yet</p>
                </div>
                <?php else: ?>
                <div class="reservations-list">
                    <?php while ($reservation = $reservations->fetch_assoc()): ?>
                    <div class="reservation-item">
                        <div class="reservation-date">
                            <i class="fa-solid fa-calendar"></i>
                            <div>
                                <strong><?php echo date('F j, Y', strtotime($reservation['date'])); ?></strong>
                                <p><?php echo date('g:i A', strtotime($reservation['time'])); ?></p>
                            </div>
                        </div>
                        <div class="reservation-details">
                            <p><i class="fa-solid fa-users"></i> <?php echo $reservation['number_of_guests']; ?> Guests</p>
                            <?php if ($reservation['table_number']): ?>
                            <p><i class="fa-solid fa-chair"></i> Table <?php echo $reservation['table_number']; ?></p>
                            <?php endif; ?>
                        </div>
                        <span class="status <?php echo $reservation['status']; ?>"><?php echo ucfirst($reservation['status']); ?></span>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
