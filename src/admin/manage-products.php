<?php
session_start();
require_once '../classes/Auth.php';
require_once '../classes/Database.php';
require_once '../classes/Product.php';

// Check if user is admin
$auth = new Auth(null);
if (!$auth->isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$db = new Database();
$conn = $db->connect();
$product = new Product($conn);

$message = '';

if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];
    $imageFileName = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadsDir = realpath(__DIR__ . '/../public/images');
        if ($uploadsDir === false) {
            $uploadsDir = __DIR__ . '/../public/images';
        }
        if (!is_dir($uploadsDir)) {
            @mkdir($uploadsDir, 0775, true);
        }

        $originalName = basename($_FILES['image']['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed, true)) {
            $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '-', pathinfo($originalName, PATHINFO_FILENAME));
            $imageFileName = $safeBase . '-' . time() . '.' . $ext;
            $targetPath = rtrim($uploadsDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $imageFileName;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $imageFileName = null;
            }
        }
    }

    if ($product->addProduct($name, $description, $price, $quantity, $imageFileName)) {
        $message = "Product added successfully!";
    } else {
        $message = "Failed to add product.";
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($product->deleteProduct($id)) {
        $message = "Product deleted successfully!";
    } else {
        $message = "Failed to delete product.";
    }
}

$products = $product->getAllProducts();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin Panel</title>
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
            <h1>Manage Products</h1>
            
            <?php if ($message): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <div class="admin-section">
                <h2>Add New Product</h2>
                <form action="" method="POST" class="admin-form" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Product Name:</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="price">Price (₹):</label>
                        <input type="number" id="price" name="price" step="0.01" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="image">Product Image:</label>
                        <input type="file" id="image" name="image" class="form-control" accept="image/*" required>
                    </div>
                    <button type="submit" name="add_product" class="btn">Add Product</button>
                </form>
            </div>

            <div class="admin-section">
                <h2>Existing Products</h2>
                <div class="table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Image</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $prod): ?>
                            <tr>
                                <td><?php echo $prod['id']; ?></td>
                                <td><?php echo htmlspecialchars($prod['name']); ?></td>
                                <td>₹<?php echo number_format($prod['price'], 2); ?></td>
                                <td><?php echo $prod['quantity']; ?></td>
                                <td>
                                    <?php if (!empty($prod['image'])): ?>
                                        <img src="../public/images/<?php echo htmlspecialchars($prod['image']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" style="width:60px;height:60px;object-fit:cover;border-radius:6px;" />
                                    <?php else: ?>
                                        <div class="product-image" style="width:60px;height:60px;display:flex;align-items:center;justify-content:center;background:#eee;border-radius:6px;">🌶️</div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(substr($prod['description'], 0, 50)) . '...'; ?></td>
                                <td>
                                    <a href="?delete=<?php echo $prod['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
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