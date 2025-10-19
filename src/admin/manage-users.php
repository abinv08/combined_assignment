<?php
session_start();
require_once '../classes/Auth.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';

// Check if user is admin
$auth = new Auth(null);
if (!$auth->isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$db = new Database();
$conn = $db->connect();
$user = new User($conn);

$message = '';

if (isset($_POST['delete_user'])) {
    $userId = $_POST['user_id'];
    if ($user->deleteUser($userId)) {
        $message = "User deleted successfully!";
    } else {
        $message = "Failed to delete user.";
    }
}

$users = $user->getAllUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
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
            <h1>Manage Users</h1>
            
            <?php if ($message): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <div class="admin-section">
                <h2>All Users</h2>
                <div class="table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td>
                                        <span class="role-badge role-<?php echo $user['role']; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" name="delete_user" class="btn btn-danger btn-sm" 
                                                    onclick="return confirm('Are you sure you want to delete this user?')">
                                                Delete
                                            </button>
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