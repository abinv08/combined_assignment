<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Cart.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$db = new Database();
$conn = $db->connect();
$cart = new Cart($conn);

$message = '';

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_SESSION['user_id'];
    
    if (isset($_POST['update_cart'])) {
        $productId = $_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        
        if ($cart->updateCartItem($userId, $productId, $quantity)) {
            $message = "Cart updated successfully!";
        } else {
            $message = "Failed to update cart.";
        }
    } elseif (isset($_POST['remove_item'])) {
        $productId = $_POST['product_id'];
        
        if ($cart->removeFromCart($userId, $productId)) {
            $message = "Item removed from cart!";
        } else {
            $message = "Failed to remove item.";
        }
    }
}

$cartItems = $cart->getCartItems($_SESSION['user_id']);
$cartTotal = $cart->getCartTotal($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="public/css/style.css">
    <title>Shopping Cart - Kerala Spices</title>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <h1>Your Shopping Cart</h1>
        
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <h2>Your cart is empty</h2>
                <p>Add some delicious Kerala spices to your cart!</p>
                <a href="products.php" class="btn">Shop Now</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <div class="item-info">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                            <div class="item-price">₹<?php echo number_format($item['price'], 2); ?> each</div>
                        </div>
                        <div class="item-controls">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="10" class="form-control" style="width: 80px; display: inline-block;">
                                <button type="submit" name="update_cart" class="btn btn-sm">Update</button>
                            </form>
                            <form method="POST" style="display: inline; margin-left: 10px;">
                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                <button type="submit" name="remove_item" class="btn btn-danger btn-sm">Remove</button>
                            </form>
                        </div>
                        <div class="item-total">
                            ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-total">
                <h2>Total: ₹<?php echo number_format($cartTotal, 2); ?></h2>
            </div>
            
            <div class="cart-actions">
                <a href="products.php" class="btn btn-secondary">Continue Shopping</a>
                <a href="checkout.php" class="btn">Proceed to Checkout</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>