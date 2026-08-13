<?php
session_start();
require_once '../database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle add/edit/delete operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_table'])) {
        $table_number = $_POST['table_number'];
        $capacity = $_POST['capacity'];
        $location = $_POST['location'];
        
        $stmt = $conn->prepare("INSERT INTO tables (table_number, capacity, location) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $table_number, $capacity, $location);
        $stmt->execute();
    } elseif (isset($_POST['edit_table'])) {
        $id = $_POST['id'];
        $table_number = $_POST['table_number'];
        $capacity = $_POST['capacity'];
        $location = $_POST['location'];
        $status = $_POST['status'];
        
        $stmt = $conn->prepare("UPDATE tables SET table_number=?, capacity=?, location=?, status=? WHERE id=?");
        $stmt->bind_param("iissi", $table_number, $capacity, $location, $status, $id);
        $stmt->execute();
    } elseif (isset($_POST['delete_table'])) {
        $id = $_POST['id'];
        $conn->query("DELETE FROM tables WHERE id = $id");
    } elseif (isset($_POST['update_status'])) {
        $id = $_POST['id'];
        $status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE tables SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
    }
}

// Get all tables
$tables = $conn->query("SELECT * FROM tables ORDER BY table_number");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tables Management - Grill N' Chill</title>
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
                <a href="reservations.php"><i class="fa-solid fa-calendar"></i> Reservations</a>
                <a href="users.php"><i class="fa-solid fa-users"></i> Users</a>
                <a href="tables.php" class="active"><i class="fa-solid fa-chair"></i> Tables</a>
                <a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>Tables Management</h1>
                <button class="btn-add" onclick="document.getElementById('addModal').style.display='block'">
                    <i class="fa-solid fa-plus"></i> Add New Table
                </button>
            </div>

            <div class="tables-grid">
                <?php while ($table = $tables->fetch_assoc()): ?>
                <div class="table-card">
                    <div class="table-header">
                        <h3>Table <?php echo $table['table_number']; ?></h3>
                        <span class="status <?php echo $table['status']; ?>"><?php echo ucfirst($table['status']); ?></span>
                    </div>
                    <div class="table-details">
                        <p><i class="fa-solid fa-users"></i> Capacity: <?php echo $table['capacity']; ?> guests</p>
                        <p><i class="fa-solid fa-location-dot"></i> <?php echo $table['location']; ?></p>
                    </div>
                    <div class="table-actions">
                        <form method="POST" action="" class="quick-status">
                            <input type="hidden" name="update_status" value="1">
                            <input type="hidden" name="id" value="<?php echo $table['id']; ?>">
                            <select name="status" onchange="this.form.submit()">
                                <option value="available" <?php echo $table['status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                                <option value="occupied" <?php echo $table['status'] == 'occupied' ? 'selected' : ''; ?>>Occupied</option>
                                <option value="reserved" <?php echo $table['status'] == 'reserved' ? 'selected' : ''; ?>>Reserved</option>
                            </select>
                        </form>
                        <button class="btn-edit" onclick="editTable(<?php echo htmlspecialchars(json_encode($table)); ?>)">
                            <i class="fa-solid fa-edit"></i>
                        </button>
                        <button class="btn-delete" onclick="deleteTable(<?php echo $table['id']; ?>)">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
            <h2>Add New Table</h2>
            <form method="POST" action="">
                <input type="hidden" name="add_table" value="1">
                <div class="form-group">
                    <label>Table Number</label>
                    <input type="number" name="table_number" required>
                </div>
                <div class="form-group">
                    <label>Capacity (guests)</label>
                    <input type="number" name="capacity" required>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <select name="location" required>
                        <option value="Indoor">Indoor</option>
                        <option value="Outdoor">Outdoor</option>
                        <option value="Private Room">Private Room</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit">Add Table</button>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
            <h2>Edit Table</h2>
            <form method="POST" action="">
                <input type="hidden" name="edit_table" value="1">
                <input type="hidden" name="id" id="editId">
                <div class="form-group">
                    <label>Table Number</label>
                    <input type="number" name="table_number" id="editTableNumber" required>
                </div>
                <div class="form-group">
                    <label>Capacity (guests)</label>
                    <input type="number" name="capacity" id="editCapacity" required>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <select name="location" id="editLocation" required>
                        <option value="Indoor">Indoor</option>
                        <option value="Outdoor">Outdoor</option>
                        <option value="Private Room">Private Room</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="editStatus" required>
                        <option value="available">Available</option>
                        <option value="occupied">Occupied</option>
                        <option value="reserved">Reserved</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit">Update Table</button>
            </form>
        </div>
    </div>

    <script>
        function editTable(table) {
            document.getElementById('editId').value = table.id;
            document.getElementById('editTableNumber').value = table.table_number;
            document.getElementById('editCapacity').value = table.capacity;
            document.getElementById('editLocation').value = table.location;
            document.getElementById('editStatus').value = table.status;
            document.getElementById('editModal').style.display = 'block';
        }

        function deleteTable(id) {
            if (confirm('Are you sure you want to delete this table?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="delete_table" value="1"><input type="hidden" name="id" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
