<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Cart.php';
require_once 'classes/Order.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$conn = $db->connect();
if ($conn === null) {
    die('Database connection failed.');
}

$userId = (int)$_SESSION['user_id'];
$cart = new Cart($conn);
$orderService = new Order($conn);

// Expect POST with Razorpay details
$paymentId = $_POST['razorpay_payment_id'] ?? '';
$orderId = $_POST['razorpay_order_id'] ?? '';
$signature = $_POST['razorpay_signature'] ?? '';
$shippingAddress = $_POST['shipping_address'] ?? '';
$paymentMethod = $_POST['payment_method'] ?? 'card';

if (!$paymentId || !$orderId || !$signature) {
    header('Location: payment.php');
    exit();
}

// Replace with your actual Razorpay secret
$keySecret = 'Fl10MO1c24Vckxi5TnMXO5hV'; // Put your secret here
if (empty($keySecret)) {
    die('Payment verification not configured. Set RAZORPAY_KEY_SECRET.');
}

$payload = $orderId . '|' . $paymentId;
$expectedSignature = hash_hmac('sha256', $payload, $keySecret);

if (!hash_equals($expectedSignature, $signature)) {
    $_SESSION['payment_error'] = 'Payment verification failed. Signature mismatch.';
    header('Location: payment.php');
    exit();
}

// Signature valid → create local order and clear cart
$cartItems = $cart->getCartItems($userId);
if (empty($cartItems)) {
    header('Location: cart.php');
    exit();
}

$cartTotal = $cart->getCartTotal($userId);

try {
    $createdOrderId = $orderService->createOrder($userId, $cartItems, $cartTotal, $paymentMethod, $shippingAddress);
    if ($createdOrderId) {
        $statusStmt = $conn->prepare("UPDATE orders SET status = 'processing' WHERE id = :id");
        $statusStmt->bindParam(':id', $createdOrderId, PDO::PARAM_INT);
        $statusStmt->execute();

        $cart->clearCart($userId);

        $_SESSION['payment_success'] = 'Order placed successfully! Order ID: #' . $createdOrderId;
        header('Location: payment.php');
        exit();
    }
    $_SESSION['payment_error'] = 'Failed to create order after payment.';
    header('Location: payment.php');
    exit();
} catch (Exception $e) {
    $_SESSION['payment_error'] = 'Error finalizing order: ' . $e->getMessage();
    header('Location: payment.php');
    exit();
}


