<?php

class Order {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function createOrder($userId, $cartItems, $totalAmount) {
        // Logic to create an order in the database
        $query = "INSERT INTO orders (user_id, total_amount) VALUES (:user_id, :total_amount)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':total_amount', $totalAmount);
        $stmt->execute();
        
        $orderId = $this->db->lastInsertId();

        foreach ($cartItems as $item) {
            $this->addOrderItem($orderId, $item['product_id'], $item['quantity']);
        }

        return $orderId;
    }

    private function addOrderItem($orderId, $productId, $quantity) {
        // Logic to add an item to the order
        $query = "INSERT INTO order_items (order_id, product_id, quantity) VALUES (:order_id, :product_id, :quantity)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':product_id', $productId);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->execute();
    }

    public function getUserOrders($userId) {
        // Logic to retrieve orders for a specific user
        $query = "SELECT * FROM orders WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalOrders() {
        $query = "SELECT COUNT(*) as total FROM orders";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    public function getRecentOrders($limit = 5) {
        $limit = (int)$limit;
        if ($limit <= 0) {
            $limit = 5;
        }
        $query = "SELECT o.id, u.name as customer_name, o.total_amount, o.status, o.created_at
                  FROM orders o
                  LEFT JOIN users u ON u.id = o.user_id
                  ORDER BY o.created_at DESC
                  LIMIT $limit";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllOrders() {
        $query = "SELECT o.id, o.user_id, u.name as customer_name, o.total_amount, o.status, o.created_at
                  FROM orders o
                  LEFT JOIN users u ON u.id = o.user_id
                  ORDER BY o.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateOrderStatus($orderId, $status) {
        $allowed = ['pending','processing','shipped','completed','cancelled'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }
        $query = "UPDATE orders SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $orderId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}