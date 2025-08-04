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

// Get error message from session
$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

// Get success message
$success = '';
if (isset($_GET['logged_out'])) {
    $success = 'ออกจากระบบเรียบร้อยแล้ว';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - สมุดโทรศัพท์ภายในองค์กร</title>
    
    <!-- Custom CSS -->
    <link href="../assets/css/style.bundle.css" rel="stylesheet">
    <link href="../assets/css/fonts.css" rel="stylesheet">
    <link href="../assets/css/fontawesome.css" rel="stylesheet">
    <link href="../assets/css/custom.css" rel="stylesheet">
    
    <style>
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 0;
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .login-body {
            padding: 2rem;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        
        .password-toggle:hover {
            color: #667eea;
        }
        
        .back-to-home {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .back-to-home:hover {
            color: #f8f9fa;
            transform: translateX(-5px);
        }
        
        @media (max-width: 576px) {
            .login-container {
                margin: 20px;
                border-radius: 15px;
            }
            
            .login-header, .login-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <a href="../index.php" class="back-to-home">
        <i class="fas fa-arrow-left me-2"></i>
        กลับหน้าแรก
    </a>

    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-shield-alt fa-3x mb-3"></i>
            <h4 class="mb-0">เข้าสู่ระบบจัดการ</h4>
            <p class="mb-0 mt-2 opacity-75">สมุดโทรศัพท์ภายในองค์กร</p>
        </div>
        
        <div class="login-body">
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="login_process.php" method="POST" id="loginForm">
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="fas fa-user me-2"></i>
                        ชื่อผู้ใช้
                    </label>
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="กรอกชื่อผู้ใช้" required autocomplete="username">
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-2"></i>
                        รหัสผ่าน
                    </label>
                    <div class="position-relative">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="กรอกรหัสผ่าน" required autocomplete="current-password">
                        <span class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-login" id="loginBtn">
                        <span class="btn-text">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            เข้าสู่ระบบ
                        </span>
                        <span class="btn-loading d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                            กำลังเข้าสู่ระบบ...
                        </span>
                    </button>
                </div>
            </form>
            
            <hr class="my-4">
            
            <div class="text-center">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    จัดทำโดย : งานเทคโนโลยีสารสนเทศ
                </small>
            </div>
        </div>
    </div>

    <script src="../assets/js/scripts.bundle.js"></script>
    
    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'fas fa-eye';
            }
        }
        
        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function() {
            const loginBtn = document.getElementById('loginBtn');
            const btnText = loginBtn.querySelector('.btn-text');
            const btnLoading = loginBtn.querySelector('.btn-loading');
            
            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');
            loginBtn.disabled = true;
        });
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Focus on username field when page loads
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
    </script>
</body>
</html>
