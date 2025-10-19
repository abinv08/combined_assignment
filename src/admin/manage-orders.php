<?php
session_start();
require_once '../classes/Auth.php';
require_once '../classes/Database.php';
require_once '../classes/Order.php';

// Check if user is admin
$auth = new Auth(null);
if (!$auth->isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$db = new Database();
$conn = $db->connect();
$orderModel = new Order($conn);

$message = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $status = $_POST['status'] ?? 'pending';
    if ($orderModel->updateOrderStatus($orderId, $status)) {
        $message = 'Order status updated!';
    } else {
        $message = 'Failed to update order status';
    }
}

$orders = $orderModel->getAllOrders();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/css/style.css">
    <title>Manage Orders</title>
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
            <h1>Manage Orders</h1>

            <?php if ($message): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="admin-section">
                <div class="table">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Update</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td>#<?php echo $o['id']; ?></td>
                                    <td><?php echo htmlspecialchars($o['customer_name'] ?? ('User #' . $o['user_id'])); ?></td>
                                    <td>₹<?php echo number_format($o['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="role-badge role-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline-flex; gap: 0.5rem; align-items: center;">
                                            <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                                            <select name="status" class="form-control">
                                                <?php foreach (['pending','processing','shipped','completed','cancelled'] as $st): ?>
                                                    <option value="<?php echo $st; ?>" <?php echo $o['status'] === $st ? 'selected' : ''; ?>><?php echo ucfirst($st); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-sm">Save</button>
                                        </form>
                                    </td>
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