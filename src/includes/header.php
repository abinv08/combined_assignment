<?php
// header.php

session_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kerala Spices E-commerce</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <header style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #eee;background:#fff;">
        <a href="index.php" style="display:flex;align-items:center;gap:10px;text-decoration:none;">
            <span style="font-size:22px;line-height:1;">🌶️</span>
            <span style="font-weight:700;color:#0b8457;font-size:20px;">Kerala Spices</span>
        </a>
        <nav>
            <ul style="display:flex;gap:14px;list-style:none;margin:0;padding:0;align-items:center;">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php" class="btn btn-secondary">Products</a></li>
                    <li><a href="cart.php" class="btn">Cart</a></li>
                    <li><a href="orders.php" class="btn btn-secondary">My Orders</a></li>
                    <li><a href="logout.php" class="btn">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>