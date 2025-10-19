<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Product.php';

$db = new Database();
$conn = $db->connect();
$product = new Product($conn);

// Get featured products (limit 6)
$featuredProducts = $product->getFeaturedProducts(6);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kerala Spices - Authentic Spices from God's Own Country</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="hero-section">
            <h1>Welcome to Kerala Spices</h1>
            <p class="lead">Discover the authentic flavors of God's Own Country through our premium collection of Kerala spices. From aromatic cardamom to fiery red chilies, we bring you the finest spices directly from the spice gardens of Kerala.</p>
            <a href="products.php" class="btn">Explore Our Spices</a>
        </div>

        <div class="featured-section">
            <h2>Featured Products</h2>
            <div class="product-grid">
                <?php if (!empty($featuredProducts)): ?>
                    <?php foreach ($featuredProducts as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                🌶️
                            </div>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                                <div class="product-price">₹<?php echo number_format($product['price'], 2); ?></div>
                                <div class="product-actions">
                                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">
                        <h3>No products available</h3>
                        <p>Please check back later for our amazing spice collection.</p>
                        <a href="products.php" class="btn">View All Products</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="about-section">
            <h2>Why Choose Kerala Spices?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <h3>🌿 Authentic</h3>
                    <p>Directly sourced from the spice gardens of Kerala</p>
                </div>
                <div class="feature-card">
                    <h3>🏆 Premium Quality</h3>
                    <p>Handpicked and carefully selected for the best flavor</p>
                </div>
                <div class="feature-card">
                    <h3>🚚 Fresh Delivery</h3>
                    <p>Delivered fresh to your doorstep</p>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>