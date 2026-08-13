# Restaurant Management System

A fully interactive restaurant management system with three user perspectives: Admin, Customer, and Reception.

## Features

### Admin Dashboard
- **Menu Management**: Add, edit, delete menu items with categories, prices, and availability
- **Order Management**: View and update order statuses (pending, preparing, ready, delivered, cancelled)
- **User Management**: Manage all system users with role assignments
- **Reservation Management**: View and manage all customer reservations
- **Table Management**: Manage restaurant tables, capacity, and status

### Customer Portal
- **Menu Browsing**: Browse menu items by category with detailed descriptions
- **Cart System**: Add items to cart, update quantities, and place orders
- **Order Types**: Support for dine-in, takeaway, and delivery orders
- **Reservation System**: Make table reservations with date, time, and guest count
- **Order Tracking**: View order history and track order status

### Reception Panel
- **Dashboard Overview**: View today's reservations, pending approvals, and table availability
- **Reservation Management**: Approve, confirm, seat, and manage reservations
- **Table Management**: Real-time table status updates (available, occupied, reserved)
- **Walk-in Customers**: Seat walk-in customers and manage their information

## Installation

### Prerequisites
- PHP 7.0 or higher
- MySQL 5.6 or higher
- Web server (Apache, Nginx, or PHP built-in server)

### Setup Instructions

1. **Clone or download the project**
   ```bash
   cd "d:\Vipul\Project\Restaurent Management System"
   ```

2. **Import the database**
   - Open phpMyAdmin or your MySQL client
   - Create a new database named `restaurant_management`
   - Import the `setup.sql` file to create all tables and sample data

3. **Configure database connection**
   - Open `database.php`
   - Update the database credentials if needed:
   ```php
   $host = 'localhost';
   $username = 'root';
   $password = '';
   $dbname = 'restaurant_management';
   ```

4. **Start the web server**
   - Using PHP built-in server:
   ```bash
   php -S localhost:8000
   ```
   - Or configure your Apache/Nginx to point to the project directory

5. **Access the application**
   - Open your browser and navigate to `http://localhost:8000`
   - You will see the landing page with login and registration options

## Default Login Credentials

### Admin Account
- **Username**: admin
- **Password**: admin123

### Customer Account
- Register a new customer account through the registration page

### Reception Account
- Create through Admin panel or register with reception role

## User Roles

### Admin
- Full access to all system features
- Can manage menu items, orders, users, reservations, and tables
- Default admin account: `admin` / `admin123`

### Customer
- Browse menu and place orders
- Make table reservations
- View order history and track status
- Manage cart and checkout

### Reception
- Manage reservations and table assignments
- Seat walk-in customers
- View dashboard with today's reservations
- Update table status in real-time

## Project Structure

```
Restaurent Management System/
├── admin/
│   ├── admin.css          # Admin dashboard styles
│   ├── dashboard.php      # Admin main dashboard
│   ├── menu.php           # Menu management
│   ├── orders.php         # Order management
│   ├── reservations.php   # Reservation management
│   ├── users.php          # User management
│   └── tables.php         # Table management
├── customer/
│   ├── customer.css       # Customer portal styles
│   ├── menu.php           # Menu browsing
│   ├── add_to_cart.php    # Cart functionality
│   ├── cart.php           # Shopping cart
│   ├── order_success.php  # Order confirmation
│   ├── my_orders.php      # Order history
│   ├── order_details.php  # Order details view
│   └── reservations.php   # Reservation system
├── reception/
│   ├── reception.css      # Reception panel styles
│   ├── dashboard.php      # Reception dashboard
│   ├── reservations.php   # Reservation management
│   ├── tables.php         # Table management
│   └── walk_in.php        # Walk-in customer handling
├── images/                # Restaurant images
├── database.php           # Database connection
├── setup.sql              # Database schema and sample data
├── index.php              # Landing page with routing
├── index.css              # Main stylesheet
├── login.php              # Login page
├── login.css              # Login styles
├── register.php           # Registration page
├── logout.php             # Logout handler
└── README.md              # This file
```

## Database Schema

The system uses the following tables:
- **users**: User authentication and role management
- **menu_items**: Restaurant menu with categories and pricing
- **orders**: Customer orders with status tracking
- **order_items**: Individual items within orders
- **reservations**: Table reservations with status
- **tables**: Restaurant table management
- **reviews**: Customer reviews and ratings

## Usage

### For Customers
1. Register a new account or login
2. Browse the menu by category
3. Add items to cart
4. Choose order type (dine-in, takeaway, delivery)
5. Place order and track status
6. Make table reservations for future visits

### For Admin
1. Login with admin credentials
2. Manage menu items (add, edit, delete)
3. Monitor and update order statuses
4. Manage user accounts and roles
5. Handle reservation requests
6. Configure restaurant tables

### For Reception
1. Login with reception credentials
2. View today's reservations and pending requests
3. Approve and confirm reservations
4. Assign tables to reservations
5. Manage table status in real-time
6. Handle walk-in customers

## Security Notes

- Passwords are hashed using PHP's `password_hash()` function
- Session-based authentication for all user types
- Role-based access control for each user type
- SQL injection prevention using prepared statements
- Input validation and sanitization

## Customization

### Adding Menu Items
- Access Admin panel → Menu Management
- Click "Add New Item"
- Fill in item details including name, description, price, category, and image

### Modifying User Roles
- Access Admin panel → Users Management
- Edit user and change role as needed (admin, customer, reception)

### Changing Restaurant Information
- Update restaurant name and details in `index.php`
- Modify colors and styling in respective CSS files

## Troubleshooting

### Database Connection Issues
- Ensure MySQL server is running
- Check database credentials in `database.php`
- Verify database name matches `restaurant_management`

### Session Issues
- Ensure PHP session support is enabled
- Check write permissions for session storage directory

### Image Display Issues
- Ensure images are placed in the `images/` directory
- Verify image filenames match database entries

## Support

For issues or questions, please check the code comments or contact the development team.

## License

This project is for educational and demonstration purposes.

## Credits

Developed as a comprehensive restaurant management system demonstration.