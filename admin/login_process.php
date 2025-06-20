<?php
// Session security settings - ต้องตั้งค่าก่อนเริ่ม session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    session_set_cookie_params([
        'lifetime' => 7200, // 2 hours
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
}

session_start();
require_once '../config/database.php';

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
    } else {
        try {
            // Get user from database
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? AND is_active = 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_name'] = $user['full_name'];
                $_SESSION['admin_email'] = $user['email'];
                
                // Log successful login
                try {
                    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, table_name, ip_address, user_agent) VALUES (?, 'login', 'admin_users', ?, ?)");
                    $stmt->execute([
                        $user['id'],
                        $_SERVER['REMOTE_ADDR'] ?? '',
                        $_SERVER['HTTP_USER_AGENT'] ?? ''
                    ]);
                } catch (PDOException $e) {
                    // Log error but don't stop login process
                    error_log('Login logging failed: ' . $e->getMessage());
                }
                
                // Update last login time
                try {
                    $stmt = $pdo->prepare("UPDATE admin_users SET updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$user['id']]);
                } catch (PDOException $e) {
                    error_log('Last login update failed: ' . $e->getMessage());
                }
                
                // Redirect to admin dashboard
                header('Location: index.php');
                exit();
            } else {
                $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
                
                // Log failed login attempt
                try {
                    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, table_name, ip_address, user_agent) VALUES (NULL, 'failed_login', 'admin_users', ?, ?)");
                    $stmt->execute([
                        $_SERVER['REMOTE_ADDR'] ?? '',
                        $_SERVER['HTTP_USER_AGENT'] ?? ''
                    ]);
                } catch (PDOException $e) {
                    error_log('Failed login logging failed: ' . $e->getMessage());
                }
            }
        } catch (PDOException $e) {
            $error = 'เกิดข้อผิดพลาดในระบบ กรุณาลองใหม่อีกครั้ง';
            error_log('Login database error: ' . $e->getMessage());
        }
    }
}

// If there's an error, redirect back to login page with error
if (!empty($error)) {
    $_SESSION['login_error'] = $error;
    header('Location: login.php');
    exit();
}

// If accessed directly without POST, redirect to login
header('Location: login.php');
exit();
?>
