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
$razorpayOrder = null;
$selectedPaymentMethod = null;
$shippingAddressInput = '';

// Handle payment processing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['process_payment'])) {
    $selectedPaymentMethod = $_POST['payment_method'] ?? '';
    $shippingAddressInput = $_POST['shipping_address'] ?? '';

    if (in_array($selectedPaymentMethod, ['card', 'upi', 'netbanking'], true)) {
        // Prepare Razorpay order
        // Replace these with your actual Razorpay keys
        $RAZORPAY_KEY_ID = 'rzp_test_RVet1deigiEQNj'; // Put your key here
        $RAZORPAY_KEY_SECRET = 'Fl10MO1c24Vckxi5TnMXO5hV'; // Put your secret here

        if (empty($RAZORPAY_KEY_ID) || empty($RAZORPAY_KEY_SECRET)) {
            $message = 'Online payments not configured. Set RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET.';
        } else {
            $amountPaise = (int) round($cartTotal * 100);
            $payload = [
                'amount' => $amountPaise,
                'currency' => 'INR',
                'receipt' => 'rcpt_' . time(),
                'payment_capture' => 1
            ];

            $ch = curl_init('https://api.razorpay.com/v1/orders');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_USERPWD, $RAZORPAY_KEY_ID . ':' . $RAZORPAY_KEY_SECRET);

            $response = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            if ($curlErr) {
                $message = 'Payment gateway error: ' . $curlErr;
            } else {
                $data = json_decode($response, true);
                if ($httpCode >= 200 && $httpCode < 300 && isset($data['id'])) {
                    $razorpayOrder = $data;
                } else {
                    $message = 'Failed to initialize payment. Please try again.';
                }
            }
        }
    } else if ($selectedPaymentMethod === 'cod') {
        try {
            // Create order with payment and shipping info for COD
            $orderId = $order->createOrder($userId, $cartItems, $cartTotal, $selectedPaymentMethod, $shippingAddressInput);

            if ($orderId) {
                // Update order status
                $updateQuery = "UPDATE orders SET status = 'processing' WHERE id = :order_id";
                $stmt = $conn->prepare($updateQuery);
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
        
        <?php if ($message || isset($_SESSION['payment_error']) || isset($_SESSION['payment_success'])): ?>
            <?php if (isset($_SESSION['payment_error'])) { $message = $_SESSION['payment_error']; unset($_SESSION['payment_error']); }
                  if (isset($_SESSION['payment_success'])) { $message = $_SESSION['payment_success']; unset($_SESSION['payment_success']); } ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php if (strpos($message, 'successfully') !== false): ?>
                <div class="success-actions">
                    <a href="orders.php" class="btn">View My Orders</a>
                    <a href="products.php" class="btn btn-secondary">Continue Shopping</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (!$message): ?>
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
                                <option value="cod" <?php echo $selectedPaymentMethod==='cod'?'selected':''; ?>>Cash on Delivery</option>
                                <option value="card" <?php echo $selectedPaymentMethod==='card'?'selected':''; ?>>Credit/Debit Card</option>
                                <option value="upi" <?php echo $selectedPaymentMethod==='upi'?'selected':''; ?>>UPI Payment</option>
                                <option value="netbanking" <?php echo $selectedPaymentMethod==='netbanking'?'selected':''; ?>>Net Banking</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="shipping_address">Shipping Address:</label>
                            <textarea id="shipping_address" name="shipping_address" class="form-control" rows="4" required placeholder="Enter your complete shipping address"><?php echo htmlspecialchars($shippingAddressInput); ?></textarea>
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

    <?php if ($razorpayOrder): ?>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
    (function(){
        var options = {
            key: '<?php echo htmlspecialchars($RAZORPAY_KEY_ID); ?>',
            amount: <?php echo (int) round($cartTotal * 100); ?>,
            currency: 'INR',
            name: 'Kerala Spices',
            description: 'Order Payment',
            order_id: '<?php echo htmlspecialchars($razorpayOrder['id']); ?>',
            handler: function (response){
                // Post to server for verification and order creation
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = 'payment_verify.php';
                var fields = {
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_signature: response.razorpay_signature,
                    shipping_address: '<?php echo htmlspecialchars($shippingAddressInput, ENT_QUOTES); ?>',
                    payment_method: '<?php echo htmlspecialchars($selectedPaymentMethod ?: 'card', ENT_QUOTES); ?>'
                };
                Object.keys(fields).forEach(function(k){
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = k;
                    input.value = fields[k];
                    form.appendChild(input);
                });
                document.body.appendChild(form);
                form.submit();
            },
            theme: { color: '#0b8457' }
        };
        var rzp = new Razorpay(options);
        rzp.open();
    })();
    </script>
    <?php endif; ?>
</body>
</html>
