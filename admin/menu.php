<?php
session_start();
require_once '../database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle add/edit/delete operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_item'])) {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = $_POST['price'];
        $category = $_POST['category'];
        $image = $_POST['image'];
        
        $stmt = $conn->prepare("INSERT INTO menu_items (name, description, price, category, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdss", $name, $description, $price, $category, $image);
        $stmt->execute();
    } elseif (isset($_POST['edit_item'])) {
        $id = $_POST['id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = $_POST['price'];
        $category = $_POST['category'];
        $image = $_POST['image'];
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        
        $stmt = $conn->prepare("UPDATE menu_items SET name=?, description=?, price=?, category=?, image=?, is_available=? WHERE id=?");
        $stmt->bind_param("ssdssii", $name, $description, $price, $category, $image, $is_available, $id);
        $stmt->execute();
    } elseif (isset($_POST['delete_item'])) {
        $id = $_POST['id'];
        $conn->query("DELETE FROM menu_items WHERE id = $id");
    }
}

// Get all menu items
$menu_items = $conn->query("SELECT * FROM menu_items ORDER BY category, name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management - Grill N' Chill</title>
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
                <a href="menu.php" class="active"><i class="fa-solid fa-utensils"></i> Menu Management</a>
                <a href="orders.php"><i class="fa-solid fa-receipt"></i> Orders</a>
                <a href="reservations.php"><i class="fa-solid fa-calendar"></i> Reservations</a>
                <a href="users.php"><i class="fa-solid fa-users"></i> Users</a>
                <a href="tables.php"><i class="fa-solid fa-chair"></i> Tables</a>
                <a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>Menu Management</h1>
                <button class="btn-add" onclick="document.getElementById('addModal').style.display='block'">
                    <i class="fa-solid fa-plus"></i> Add New Item
                </button>
            </div>

            <div class="menu-grid">
                <?php while ($item = $menu_items->fetch_assoc()): ?>
                <div class="menu-item-card">
                    <div class="menu-item-image">
                        <img src="../images/<?php echo $item['image'] ?: 'placeholder.jpg'; ?>" alt="<?php echo $item['name']; ?>">
                        <span class="availability <?php echo $item['is_available'] ? 'available' : 'unavailable'; ?>">
                            <?php echo $item['is_available'] ? 'Available' : 'Unavailable'; ?>
                        </span>
                    </div>
                    <div class="menu-item-details">
                        <h3><?php echo $item['name']; ?></h3>
                        <p class="category"><?php echo $item['category']; ?></p>
                        <p class="description"><?php echo $item['description']; ?></p>
                        <p class="price">$<?php echo number_format($item['price'], 2); ?></p>
                        <div class="menu-item-actions">
                            <button class="btn-edit" onclick="editItem(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                                <i class="fa-solid fa-edit"></i> Edit
                            </button>
                            <button class="btn-delete" onclick="deleteItem(<?php echo $item['id']; ?>)">
                                <i class="fa-solid fa-trash"></i> Delete
                            </button>
                        </div>
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
            <h2>Add New Menu Item</h2>
            <form method="POST" action="">
                <input type="hidden" name="add_item" value="1">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Price</label>
                    <input type="number" step="0.01" name="price" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" required>
                        <option value="Appetizer">Appetizer</option>
                        <option value="Main Course">Main Course</option>
                        <option value="Dessert">Dessert</option>
                        <option value="Beverage">Beverage</option>
                        <option value="Side">Side</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Image Filename</label>
                    <input type="text" name="image" placeholder="e.g., pizza.jpg">
                </div>
                <button type="submit" class="btn-submit">Add Item</button>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
            <h2>Edit Menu Item</h2>
            <form method="POST" action="">
                <input type="hidden" name="edit_item" value="1">
                <input type="hidden" name="id" id="editId">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" id="editName" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="editDescription" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Price</label>
                    <input type="number" step="0.01" name="price" id="editPrice" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" id="editCategory" required>
                        <option value="Appetizer">Appetizer</option>
                        <option value="Main Course">Main Course</option>
                        <option value="Dessert">Dessert</option>
                        <option value="Beverage">Beverage</option>
                        <option value="Side">Side</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Image Filename</label>
                    <input type="text" name="image" id="editImage">
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_available" id="editAvailable"> Available
                    </label>
                </div>
                <button type="submit" class="btn-submit">Update Item</button>
            </form>
        </div>
    </div>

    <script>
        function editItem(item) {
            document.getElementById('editId').value = item.id;
            document.getElementById('editName').value = item.name;
            document.getElementById('editDescription').value = item.description;
            document.getElementById('editPrice').value = item.price;
            document.getElementById('editCategory').value = item.category;
            document.getElementById('editImage').value = item.image;
            document.getElementById('editAvailable').checked = item.is_available == 1;
            document.getElementById('editModal').style.display = 'block';
        }

        function deleteItem(id) {
            if (confirm('Are you sure you want to delete this item?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="delete_item" value="1"><input type="hidden" name="id" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
