<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Cart.php';
require_once 'classes/Order.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$db = new Database();
$conn = $db->connect();
$cart = new Cart($conn);
$order = new Order($conn);

$userId = $_SESSION['user_id'];
$cartItems = $cart->getCartItems($userId);
$cartTotal = $cart->getCartTotal($userId);

if (empty($cartItems)) {
    header("Location: cart.php");
    exit();
}

$message = '';

// Handle payment processing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['process_payment'])) {
    $paymentMethod = $_POST['payment_method'];
    $shippingAddress = $_POST['shipping_address'];
    
    try {
        // Create order
        $orderId = $order->createOrder($userId, $cartItems, $cartTotal);
        
        if ($orderId) {
            // Update order with payment and shipping info
            $updateQuery = "UPDATE orders SET status = 'processing', payment_method = :payment_method, shipping_address = :shipping_address WHERE id = :order_id";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bindParam(':payment_method', $paymentMethod);
            $stmt->bindParam(':shipping_address', $shippingAddress);
            $stmt->bindParam(':order_id', $orderId);
            $stmt->execute();
            
            // Clear cart
            $cart->clearCart($userId);
            
            $message = "Order placed successfully! Order ID: #" . $orderId;
        } else {
            $message = "Failed to create order. Please try again.";
        }
    } catch (Exception $e) {
        $message = "Error processing payment: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Kerala Spices</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <h1>Payment & Checkout</h1>
        
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php if (strpos($message, 'successfully') !== false): ?>
                <div class="success-actions">
                    <a href="orders.php" class="btn">View My Orders</a>
                    <a href="products.php" class="btn btn-secondary">Continue Shopping</a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="checkout-container">
                <div class="order-summary">
                    <h2>Order Summary</h2>
                    <div class="order-items">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="order-item">
                                <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="item-quantity">Qty: <?php echo $item['quantity']; ?></div>
                                <div class="item-price">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="order-total">
                        <strong>Total: ₹<?php echo number_format($cartTotal, 2); ?></strong>
                    </div>
                </div>
                
                <div class="payment-form">
                    <h2>Payment Information</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="payment_method">Payment Method:</label>
                            <select id="payment_method" name="payment_method" class="form-control" required>
                                <option value="">Select Payment Method</option>
                                <option value="cod">Cash on Delivery</option>
                                <option value="card">Credit/Debit Card</option>
                                <option value="upi">UPI Payment</option>
                                <option value="netbanking">Net Banking</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="shipping_address">Shipping Address:</label>
                            <textarea id="shipping_address" name="shipping_address" class="form-control" rows="4" required placeholder="Enter your complete shipping address"></textarea>
                        </div>
                        
                        <div class="payment-actions">
                            <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
                            <button type="submit" name="process_payment" class="btn">Place Order</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
