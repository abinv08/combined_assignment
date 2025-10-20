<?php

class Product {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function getProductDetails($productId) {
        $query = "SELECT * FROM products WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addProduct($name, $description, $price, $quantity, $imagePath = null) {
        $query = "INSERT INTO products (name, description, price, quantity, image) VALUES (:name, :description, :price, :quantity, :image)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':image', $imagePath);
        return $stmt->execute();
    }

    public function updateProduct($productId, $name, $description, $price, $quantity, $imagePath = null) {
        if ($imagePath) {
            $query = "UPDATE products SET name = :name, description = :description, price = :price, quantity = :quantity, image = :image WHERE id = :id";
        } else {
            $query = "UPDATE products SET name = :name, description = :description, price = :price, quantity = :quantity WHERE id = :id";
        }
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        if ($imagePath) {
            $stmt->bindParam(':image', $imagePath);
        }
        return $stmt->execute();
    }

    public function deleteProduct($productId) {
        $query = "DELETE FROM products WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getTotalProducts() {
        $query = "SELECT COUNT(*) as total FROM products";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    public function getAllProducts() {
        try {
            $query = "SELECT * FROM products ORDER BY created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function getFeaturedProducts($limit = 6) {
        try {
            $limit = (int)$limit;
            if ($limit <= 0) {
                $limit = 6;
            }
            // MySQL/MariaDB do not support binding LIMIT properly when emulation is disabled
            $query = "SELECT * FROM products ORDER BY created_at DESC LIMIT $limit";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }
}