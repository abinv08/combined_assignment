<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Auth.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = filter_var($_POST['username'] ?? '', FILTER_SANITIZE_STRING);
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'All fields are required';
    } else {
        $database = new Database();
        $db = $database->connect();
        
        if ($db) {
            $auth = new Auth($db);
            if (!$auth->login($username, $password)) {
                $error = 'Invalid username or password';
            }
        } else {
            $error = 'Database connection error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kerala Spices</title>
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* Hide header/footer on auth pages if desired */
    </style>
    </head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Welcome back</h2>
            </div>
            <div class="auth-body">
                <?php if ($error): ?>
                    <div class="auth-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn" style="width: 100%">Login</button>
                </form>

                <div class="auth-meta">
                    <p>Don't have an account? <a href="register.php">Create one</a></p>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>