<?php

class Auth {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function registerUser($username, $password, $email) {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Prepare SQL statement
        $stmt = $this->db->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashedPassword, $email);
        
        // Execute and return result
        return $stmt->execute();
    }

    public function loginUser($username, $password) {
        // Prepare SQL statement
        $stmt = $this->db->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($hashedPassword);
            $stmt->fetch();
            
            // Verify password
            if (password_verify($password, $hashedPassword)) {
                // Start session and set user session variable
                session_start();
                $_SESSION['username'] = $username;
                return true;
            }
        }
        return false;
    }

    public function logoutUser() {
        // Destroy session
        session_start();
        session_unset();
        session_destroy();
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public function isAdmin() {
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            return true;
        }
        return false;
    }

    /**
     * Default admin credentials
     * username: Admin
     * password: admin123
     */
    public function isDefaultAdminCredentials($username, $password) {
        return ($username === 'Admin' && $password === 'admin123');
    }

    public function login($usernameOrEmail, $password) {
        try {
            // Special check for default admin credentials
            if ($this->isDefaultAdminCredentials($usernameOrEmail, $password)) {
                $_SESSION['user_id'] = 1;
                $_SESSION['username'] = 'Admin';
                $_SESSION['user_role'] = 'admin';
                header('Location: admin/dashboard.php');
                exit();
            }

            // Allow login with username OR email
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: index.php');
                }
                exit();
            }
            return false;
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}