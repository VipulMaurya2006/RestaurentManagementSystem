-- Restaurant Management System Database Schema

CREATE DATABASE IF NOT EXISTS restaurant_management;
USE restaurant_management;

-- Users table for authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer', 'reception') NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menu items table
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(50) NOT NULL,
    image VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'preparing', 'ready', 'delivered', 'cancelled') DEFAULT 'pending',
    order_type ENUM('dine_in', 'takeaway', 'delivery') NOT NULL,
    table_number INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
);

-- Reservations table
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    date DATE NOT NULL,
    time TIME NOT NULL,
    number_of_guests INT NOT NULL,
    table_number INT,
    status ENUM('pending', 'confirmed', 'seated', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tables table for restaurant seating
CREATE TABLE IF NOT EXISTS tables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_number INT UNIQUE NOT NULL,
    capacity INT NOT NULL,
    status ENUM('available', 'occupied', 'reserved') DEFAULT 'available',
    location VARCHAR(50)
);

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_id INT,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, role, full_name, phone) 
VALUES ('admin', 'admin@restaurant.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Administrator', '1234567890')
ON DUPLICATE KEY UPDATE username=username;

-- Insert sample menu items
INSERT INTO menu_items (name, description, price, category, image) VALUES
('Grilled Chicken', 'Tender grilled chicken with herbs', 15.99, 'Main Course', 'chicken.jpg'),
('Beef Burger', 'Juicy beef burger with cheese', 12.99, 'Main Course', 'burger.jpg'),
('Caesar Salad', 'Fresh caesar salad with croutons', 8.99, 'Appetizer', 'salad.jpg'),
('Pizza Margherita', 'Classic pizza with tomato and mozzarella', 14.99, 'Main Course', 'pizza.jpg'),
('Chocolate Cake', 'Rich chocolate dessert', 6.99, 'Dessert', 'cake.jpg'),
('French Fries', 'Crispy golden fries', 4.99, 'Side', 'fries.jpg')
ON DUPLICATE KEY UPDATE name=name;

-- Insert sample tables
INSERT INTO tables (table_number, capacity, status, location) VALUES
(1, 4, 'available', 'Indoor'),
(2, 4, 'available', 'Indoor'),
(3, 6, 'available', 'Indoor'),
(4, 2, 'available', 'Indoor'),
(5, 8, 'available', 'Outdoor'),
(6, 4, 'available', 'Outdoor')
ON DUPLICATE KEY UPDATE table_number=table_number;
