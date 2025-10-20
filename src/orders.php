<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Auth.php';
require_once 'classes/Order.php';

$db = new Database();
$conn = $db->connect();
if ($conn === null) {
    die('Database connection failed.');
}

$auth = new Auth($conn);
$orderService = new Order($conn);

if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$userOrders = $orderService->getUserOrders($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders</title>
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        .orders-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
        .orders-subtitle { color: #666; font-size: 14px; }
        .orders-card { background: #fff; border: 1px solid #eee; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.04); }
        .orders-table { width: 100%; border-collapse: collapse; }
        .orders-table thead th { text-align: left; font-weight: 600; color: #444; background: #fafafa; padding: 14px 16px; border-bottom: 1px solid #eee; }
        .orders-table tbody td { padding: 14px 16px; border-bottom: 1px solid #f1f1f1; color: #222; }
        .orders-table tbody tr:hover { background: #fcfcfc; }
        .order-id { font-weight: 600; color: #0b8457; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .badge-pending { background: #fff7e6; color: #b34700; border: 1px solid #ffe1b3; }
        .badge-processing { background: #e6f4ff; color: #095aba; border: 1px solid #cce7ff; }
        .badge-shipped { background: #eef7ff; color: #0b5ed7; border: 1px solid #d6ecff; }
        .badge-completed { background: #e9f9ef; color: #0f8a4b; border: 1px solid #c9f0d7; }
        .badge-cancelled { background: #fde8e8; color: #c11d1d; border: 1px solid #f8cccc; }
        .empty-orders { background: #fff; border: 1px solid #eee; border-radius: 12px; padding: 24px; text-align: center; color: #555; }
        .orders-total { font-weight: 700; color: #111; }
        @media (max-width: 640px) {
            .orders-table thead { display: none; }
            .orders-table, .orders-table tbody, .orders-table tr, .orders-table td { display: block; width: 100%; }
            .orders-table tr { border-bottom: 1px solid #f1f1f1; }
            .orders-table tbody td { padding: 10px 16px; }
            .orders-table tbody td[data-label]::before { content: attr(data-label) ": "; font-weight: 600; color: #666; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="orders-header">
            <h1>Your Orders</h1>
            <div class="orders-subtitle">Track your recent purchases</div>
        </div>
        <?php if (empty($userOrders)): ?>
            <div class="empty-orders">
                <h3>No orders yet</h3>
                <p>Browse our products and place your first order.</p>
                <a href="products.php" class="btn">Shop Spices</a>
            </div>
        <?php else: ?>
            <div class="orders-card">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($userOrders as $userOrder): ?>
                            <?php
                                $status = $userOrder['status'] ?? '';
                                $badgeClass = 'badge';
                                if ($status === 'pending') { $badgeClass .= ' badge-pending'; }
                                elseif ($status === 'processing') { $badgeClass .= ' badge-processing'; }
                                elseif ($status === 'shipped') { $badgeClass .= ' badge-shipped'; }
                                elseif ($status === 'completed') { $badgeClass .= ' badge-completed'; }
                                elseif ($status === 'cancelled') { $badgeClass .= ' badge-cancelled'; }
                                $dateStr = '';
                                if (!empty($userOrder['created_at'])) { $ts = strtotime($userOrder['created_at']); if ($ts) { $dateStr = date('d M Y, h:i A', $ts); } }
                                $totalStr = isset($userOrder['total_amount']) ? '₹' . number_format((float)$userOrder['total_amount'], 2) : '';
                            ?>
                            <tr>
                                <td data-label="Order"><span class="order-id">#<?php echo htmlspecialchars($userOrder['id']); ?></span></td>
                                <td data-label="Date"><?php echo htmlspecialchars($dateStr); ?></td>
                                <td data-label="Status"><span class="<?php echo $badgeClass; ?>"><?php echo htmlspecialchars(ucfirst($status)); ?></span></td>
                                <td data-label="Total"><span class="orders-total"><?php echo htmlspecialchars($totalStr); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>