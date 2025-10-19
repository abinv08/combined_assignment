<?php
session_start();
require_once '../classes/Auth.php';
require_once '../classes/Database.php';
require_once '../classes/Order.php';
require_once '../classes/User.php';
require_once '../classes/Product.php';

// Check if user is admin
$auth = new Auth(null);
if (!$auth->isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$db = new Database();
$conn = $db->connect();

$user = new User($conn);
$order = new Order($conn);
$product = new Product($conn);

// Get statistics
$totalUsers = $user->getTotalUsers();
$totalOrders = $order->getTotalOrders();
$totalProducts = $product->getTotalProducts();
$recentOrders = $order->getRecentOrders(5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Kerala Spices</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <nav>
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="manage-products.php" class="nav-link">Manage Products</a>
                <a href="manage-orders.php" class="nav-link">Manage Orders</a>
                <a href="manage-users.php" class="nav-link">Manage Users</a>
            </nav>
            <form action="../logout.php" method="POST">
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
        
        <div class="main-content">
            <h1>Dashboard</h1>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <p><?php echo $totalUsers; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <p><?php echo $totalOrders; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Products</h3>
                    <p><?php echo $totalProducts; ?></p>
                </div>
            </div>
            
            <div class="recent-orders admin-section">
                <h2>Recent Orders</h2>
                <div class="table">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><span class="role-badge role-<?php echo $order['status']; ?>"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></span></td>
                            <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>