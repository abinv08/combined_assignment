<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Product.php';
require_once 'classes/Cart.php';

$db = new Database();
$conn = $db->connect();
$product = new Product($conn);
$cart = new Cart($conn);

$message = '';

if (isset($_GET['id'])) {
    $productId = $_GET['id'];
    $productDetails = $product->getProductDetails($productId);
    
    if (!$productDetails) {
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        
        // Debug: Check if user is logged in and product exists
        if (!$userId) {
            $message = "User not logged in properly.";
        } elseif (!$productId) {
            $message = "Product ID not found.";
        } else {
            if ($cart->addToCart($userId, $productId, $quantity)) {
                $message = "Product added to cart successfully!";
            } else {
                $message = "Failed to add product to cart. Please check if cart table exists in database.";
            }
        }
    } else {
        $message = "Please login to add items to cart.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="public/css/style.css">
    <title><?php echo htmlspecialchars($productDetails['name']); ?> - Kerala Spices</title>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="product-details">
            <div class="product-image-large">
                <?php if (!empty($productDetails['image'])): ?>
                    <img src="public/images/<?php echo htmlspecialchars($productDetails['image']); ?>" alt="<?php echo htmlspecialchars($productDetails['name']); ?>" style="width:100%;height:100%;object-fit:cover;border-radius:12px;" />
                <?php else: ?>
                    🌶️
                <?php endif; ?>
            </div>
            <div class="product-info-large">
                <h1><?php echo htmlspecialchars($productDetails['name']); ?></h1>
                <div class="product-price">₹<?php echo number_format($productDetails['price'], 2); ?></div>
                <p class="product-description"><?php echo htmlspecialchars($productDetails['description']); ?></p>
                
                <?php if ($message): ?>
                    <div class="message"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="quantity">Quantity:</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="10" class="form-control" style="width: 100px;">
                        </div>
                        <button type="submit" name="add_to_cart" class="btn">Add to Cart</button>
                        <a href="cart.php" class="btn btn-secondary">View Cart</a>
                    </form>
                <?php else: ?>
                    <p>Please <a href="login.php">login</a> to add items to cart.</p>
                <?php endif; ?>
                
                <div class="product-actions">
                    <a href="products.php" class="btn btn-secondary">Back to Products</a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>