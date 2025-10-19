<?php

class Cart {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function addToCart($userId, $productId, $quantity = 1) {
        try {
            // Check if item already exists in cart
            $checkQuery = "SELECT id, quantity FROM cart WHERE user_id = :user_id AND product_id = :product_id";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':user_id', $userId);
            $checkStmt->bindParam(':product_id', $productId);
            $checkStmt->execute();
            $existingItem = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingItem) {
                // Update quantity
                $newQuantity = $existingItem['quantity'] + $quantity;
                $updateQuery = "UPDATE cart SET quantity = :quantity WHERE id = :id";
                $updateStmt = $this->db->prepare($updateQuery);
                $updateStmt->bindParam(':quantity', $newQuantity);
                $updateStmt->bindParam(':id', $existingItem['id']);
                return $updateStmt->execute();
            } else {
                // Add new item
                $insertQuery = "INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)";
                $insertStmt = $this->db->prepare($insertQuery);
                $insertStmt->bindParam(':user_id', $userId);
                $insertStmt->bindParam(':product_id', $productId);
                $insertStmt->bindParam(':quantity', $quantity);
                return $insertStmt->execute();
            }
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getCartItems($userId) {
        try {
            $query = "SELECT c.*, p.name, p.price, p.description 
                     FROM cart c 
                     JOIN products p ON c.product_id = p.id 
                     WHERE c.user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function updateCartItem($userId, $productId, $quantity) {
        try {
            if ($quantity <= 0) {
                return $this->removeFromCart($userId, $productId);
            }
            
            $query = "UPDATE cart SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':product_id', $productId);
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function removeFromCart($userId, $productId) {
        try {
            $query = "DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':product_id', $productId);
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function clearCart($userId) {
        try {
            $query = "DELETE FROM cart WHERE user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getCartTotal($userId) {
        try {
            $query = "SELECT SUM(c.quantity * p.price) as total 
                     FROM cart c 
                     JOIN products p ON c.product_id = p.id 
                     WHERE c.user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return 0;
        }
    }
}