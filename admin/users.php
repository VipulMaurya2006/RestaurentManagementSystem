<?php
session_start();
require_once '../database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle add/edit/delete operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_user'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        $full_name = trim($_POST['full_name']);
        $phone = trim($_POST['phone']);
        
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, full_name, phone) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $username, $email, $password, $role, $full_name, $phone);
        $stmt->execute();
    } elseif (isset($_POST['edit_user'])) {
        $id = $_POST['id'];
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        $full_name = trim($_POST['full_name']);
        $phone = trim($_POST['phone']);
        
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username=?, email=?, password=?, role=?, full_name=?, phone=? WHERE id=?");
            $stmt->bind_param("ssssssi", $username, $email, $password, $role, $full_name, $phone, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username=?, email=?, role=?, full_name=?, phone=? WHERE id=?");
            $stmt->bind_param("sssssi", $username, $email, $role, $full_name, $phone, $id);
        }
        $stmt->execute();
    } elseif (isset($_POST['delete_user'])) {
        $id = $_POST['id'];
        $conn->query("DELETE FROM users WHERE id = $id");
    }
}

// Get all users
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - Grill N' Chill</title>
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
                <a href="users.php" class="active"><i class="fa-solid fa-users"></i> Users</a>
                <a href="tables.php"><i class="fa-solid fa-chair"></i> Tables</a>
                <a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>Users Management</h1>
                <button class="btn-add" onclick="document.getElementById('addModal').style.display='block'">
                    <i class="fa-solid fa-plus"></i> Add New User
                </button>
            </div>

            <div class="users-table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Full Name</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo $user['username']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td><?php echo $user['full_name']; ?></td>
                            <td><?php echo $user['phone']; ?></td>
                            <td><span class="role <?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <button class="btn-edit" onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                    <i class="fa-solid fa-edit"></i>
                                </button>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <button class="btn-delete" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
            <h2>Add New User</h2>
            <form method="POST" action="">
                <input type="hidden" name="add_user" value="1">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" required>
                        <option value="customer">Customer</option>
                        <option value="reception">Reception</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit">Add User</button>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
            <h2>Edit User</h2>
            <form method="POST" action="">
                <input type="hidden" name="edit_user" value="1">
                <input type="hidden" name="id" id="editId">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" id="editUsername" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="editEmail" required>
                </div>
                <div class="form-group">
                    <label>Password (leave blank to keep current)</label>
                    <input type="password" name="password" id="editPassword">
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" id="editFullName" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone" id="editPhone">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="editRole" required>
                        <option value="customer">Customer</option>
                        <option value="reception">Reception</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit">Update User</button>
            </form>
        </div>
    </div>

    <script>
        function editUser(user) {
            document.getElementById('editId').value = user.id;
            document.getElementById('editUsername').value = user.username;
            document.getElementById('editEmail').value = user.email;
            document.getElementById('editFullName').value = user.full_name;
            document.getElementById('editPhone').value = user.phone;
            document.getElementById('editRole').value = user.role;
            document.getElementById('editModal').style.display = 'block';
        }

        function deleteUser(id) {
            if (confirm('Are you sure you want to delete this user?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="delete_user" value="1"><input type="hidden" name="id" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
