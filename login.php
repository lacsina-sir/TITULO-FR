<?php
// config.php - Database configuration
$host = 'localhost';
$dbname = 'titulo'; // Change this to your database name
$username = 'root';        // Default XAMPP username
$password = '';            // Default XAMPP password (empty)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session
session_start();

// Handle form submission
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {

        // USER LOGIN HANDLER
        if ($_POST['action'] == 'login') {
            $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
            $password = trim($_POST['password']);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error_message = "Please enter a valid email address.";
            } elseif (empty($password)) {
                $error_message = "Please enter your password.";
            } else {
                try {
                    $stmt = $pdo->prepare("SELECT id, email, password, first_name, last_name, status FROM users WHERE email = ? AND status = 'active'");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch();

                    if ($user && password_verify($password, $user['password'])) {
                        // Update last login
                        $update_stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                        $update_stmt->execute([$user['id']]);

                        // Set session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                        $_SESSION['user_type'] = 'user';

                        // Redirect to user dashboard | change to client_dashboard.php wala naman kasing file ng dashboard.ph
                        header("Location: client_dashboard.php");
                        exit();
                    } else {
                        $error_message = "Invalid email or password.";
                    }
                } catch (PDOException $e) {
                    $error_message = "An error occurred. Please try again.";
                }
            }
        }

        // ADMIN LOGIN HANDLER
        if ($_POST['action'] == 'admin_login') {
            $username = trim($_POST['admin_username']);
            $password = trim($_POST['admin_password']);

            if (empty($username)) {
                $error_message = "Please enter your admin username.";
            } elseif (empty($password)) {
                $error_message = "Please enter your admin password.";
            } else {
                try {
                    $stmt = $pdo->prepare("SELECT id, username, password, full_name, role, status FROM admins WHERE username = ? AND status = 'active'");
                    $stmt->execute([$username]);
                    $admin = $stmt->fetch();

                    if ($admin && password_verify($password, $admin['password'])) {
                        // Update last login
                        $update_stmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
                        $update_stmt->execute([$admin['id']]);

                        // Set session variables
                        $_SESSION['admin_id'] = $admin['id'];
                        $_SESSION['admin_username'] = $admin['username'];
                        $_SESSION['admin_name'] = $admin['full_name'];
                        $_SESSION['admin_role'] = $admin['role'];
                        $_SESSION['user_type'] = 'admin';

                        // Redirect to admin dashboard
                        header("Location: admin_dashboard.php");
                        exit();
                    } else {
                        $error_message = "Invalid admin credentials.";
                    }
                } catch (PDOException $e) {
                    $error_message = "An error occurred. Please try again.";
                }
            }
        }

        // REGISTRATION HANDLER
        if ($_POST['action'] == 'register') {
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
            $password = trim($_POST['password']);
            $confirm_password = trim($_POST['confirm_password']);

            // Validation
            if (empty($first_name) || empty($last_name)) {
                $error_message = "Please enter your first and last name.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error_message = "Please enter a valid email address.";
            } elseif (strlen($password) < 6) {
                $error_message = "Password must be at least 6 characters long.";
            } elseif ($password !== $confirm_password) {
                $error_message = "Passwords do not match.";
            } else {
                try {
                    // Check if email already exists
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->execute([$email]);

                    if ($stmt->fetch()) {
                        $error_message = "An account with this email already exists.";
                    } else {
                        // Hash password and insert user
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$first_name, $last_name, $email, $hashed_password]);

                        $success_message = "Account created successfully! You can now log in.";
                    }
                } catch (PDOException $e) {
                    $error_message = "An error occurred. Please try again.";
                }
            }
        }
    }
}

// Check if user is already logged in
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'user') {
    //change file name
    header("Location: client_dashboard.php");
    exit();
}

// Check if admin is already logged in
if (isset($_SESSION['admin_id']) && $_SESSION['user_type'] == 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compass North Land Surveying Services</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #2c3e50 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            background: rgba(0, 0, 0, 0.3);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            width: 50px;
            height: 50px;
            background: linear-gradient(45deg, #00ff88, #00cc6a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 24px;
            color: white;
            box-shadow: 0 4px 15px rgba(0, 255, 136, 0.3);
        }

        .company-name {
            color: #ecf0f1;
            font-size: 18px;
            font-weight: 300;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .nav-pills {
            display: flex;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px;
            border-radius: 25px;
            backdrop-filter: blur(10px);
        }

        .nav-pill {
            padding: 10px 18px;
            color: #bdc3c7;
            text-decoration: none;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 20px;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            background: none;
        }

        .nav-pill:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ecf0f1;
        }

        .nav-pill.active {
            background: linear-gradient(45deg, #00ff88, #00cc6a);
            color: white;
        }

        .nav-pill.admin {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
        }

        .nav-pill.admin:hover {
            background: linear-gradient(45deg, #c0392b, #a93226);
        }

        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            gap: 80px;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .welcome-section {
            flex: 1;
            max-width: 600px;
            text-align: center;
        }

        .welcome-title {
            font-size: 4.5rem;
            font-weight: 300;
            color: #ecf0f1;
            margin-bottom: 30px;
            line-height: 1.2;
        }

        .welcome-title .highlight {
            background: linear-gradient(45deg, #00ff88, #00cc6a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 500;
        }

        .welcome-description {
            font-size: 1.3rem;
            color: #bdc3c7;
            line-height: 1.6;
            margin-bottom: 40px;
        }

        .play-button {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #00ff88, #00cc6a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 30px rgba(0, 255, 136, 0.3);
        }

        .play-button:hover {
            transform: scale(1.1);
            box-shadow: 0 12px 40px rgba(0, 255, 136, 0.4);
        }

        .play-button::after {
            content: '';
            width: 0;
            height: 0;
            border-left: 20px solid white;
            border-top: 12px solid transparent;
            border-bottom: 12px solid transparent;
            margin-left: 4px;
        }

        .login-section {
            flex: 0 0 450px;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 50px 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .form-title {
            font-size: 2.5rem;
            color: #ecf0f1;
            text-align: center;
            margin-bottom: 40px;
            font-weight: 300;
        }

        .form-title.admin {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-input {
            width: 100%;
            padding: 18px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: #ecf0f1;
            font-size: 16px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .form-input::placeholder {
            color: #7f8c8d;
        }

        .form-input:focus {
            outline: none;
            border-color: #00ff88;
            box-shadow: 0 0 20px rgba(0, 255, 136, 0.2);
        }

        .form-input.admin:focus {
            border-color: #e74c3c;
            box-shadow: 0 0 20px rgba(231, 76, 60, 0.2);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #bdc3c7;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #00ff88;
        }

        .forgot-password {
            color: #00ff88;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: #00cc6a;
        }

        .submit-button {
            width: 100%;
            padding: 18px;
            background: linear-gradient(45deg, #00ff88, #00cc6a);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 25px;
        }

        .submit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 255, 136, 0.4);
        }

        .submit-button.admin {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
        }

        .submit-button.admin:hover {
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.4);
        }

        .form-switch {
            text-align: center;
            color: #bdc3c7;
            font-size: 14px;
        }

        .form-switch button {
            color: #00ff88;
            background: none;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
        }

        .form-switch button:hover {
            color: #00cc6a;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.3);
        }

        .alert-success {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            border: 1px solid rgba(46, 204, 113, 0.3);
        }

        .footer {
            text-align: center;
            padding: 30px;
            color: #7f8c8d;
            font-size: 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .admin-notice {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.3);
            color: #e74c3c;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            text-align: center;
        }

        @media (max-width: 1024px) {
            .main-content {
                flex-direction: column;
                gap: 50px;
            }

            .welcome-title {
                font-size: 3.5rem;
            }

            .login-section {
                flex: none;
                width: 100%;
                max-width: 450px;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 20px;
                padding: 20px;
            }

            .nav-pills {
                order: -1;
            }

            .welcome-title {
                font-size: 2.8rem;
            }

            .welcome-description {
                font-size: 1.1rem;
            }

            .form-container {
                padding: 40px 30px;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="logo-section">
            <div class="logo">C</div>
            <div class="company-name">Compass North Land Surveying Services</div>
        </div>
        <nav class="nav-pills">
            <a href="#" class="nav-pill">Home</a>
            <a href="#" class="nav-pill">About</a>
            <a href="#" class="nav-pill">Projects</a>
            <a href="#" class="nav-pill">Services</a>
            <a href="#" class="nav-pill">Tips</a>
            <a href="#" class="nav-pill">Contact</a>
            <button type="button" class="nav-pill active" onclick="showLogin()">User Login</button>
            <button type="button" class="nav-pill admin" onclick="showAdminLogin()">Admin</button>
        </nav>
    </header>

    <main class="main-content">
        <section class="welcome-section">
            <h1 class="welcome-title">
                Welcome to<br>
                <span class="highlight">Compass North!</span>
            </h1>
            <p class="welcome-description">
                Your trusted partner in land surveying and property services.<br><br>
                Log in to access project updates, secure your property, and explore valuable resources for your peace of
                mind.
            </p>
            <div class="play-button"></div>
        </section>

        <section class="login-section">
            <div class="form-container">
                <?php if ($error_message): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>

                <!-- User Login Form -->
                <form id="loginForm" method="POST" style="display: block;">
                    <input type="hidden" name="action" value="login">
                    <h2 class="form-title">User Login</h2>

                    <div class="form-group">
                        <input type="email" name="email" class="form-input" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" class="form-input" placeholder="Password" required>
                    </div>
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember">
                            <span>Remember Me</span>
                        </label>
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>
                    <button type="submit" class="submit-button">Login</button>
                    <div class="form-switch">
                        Don't have an account? <button type="button" onclick="showRegister()">Register</button>
                    </div>
                </form>

                <!-- Admin Login Form -->
                <form id="adminLoginForm" method="POST" style="display: none;">
                    <input type="hidden" name="action" value="admin_login">
                    <h2 class="form-title admin">Admin Login</h2>

                    <div class="admin-notice">
                        ⚠️ Authorized Personnel Only
                    </div>

                    <div class="form-group">
                        <input type="text" name="admin_username" class="form-input admin" placeholder="Admin Username"
                            required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="admin_password" class="form-input admin"
                            placeholder="Admin Password" required>
                    </div>
                    <button type="submit" class="submit-button admin">Admin Login</button>
                    <div class="form-switch">
                        <button type="button" onclick="showLogin()">← Back to User Login</button>
                    </div>
                </form>

                <!-- Registration Form -->
                <form id="registerForm" method="POST" style="display: none;">
                    <input type="hidden" name="action" value="register">
                    <h2 class="form-title">Register</h2>

                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" name="first_name" class="form-input" placeholder="First Name" required>
                        </div>
                        <div class="form-group">
                            <input type="text" name="last_name" class="form-input" placeholder="Last Name" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" class="form-input" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" class="form-input" placeholder="Password" minlength="6"
                            required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="confirm_password" class="form-input" placeholder="Confirm Password"
                            required>
                    </div>
                    <button type="submit" class="submit-button">Register</button>
                    <div class="form-switch">
                        Already have an account? <button type="button" onclick="showLogin()">Login</button>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <footer class="footer">
        Copyright 2025 TITULO
    </footer>

    <script>
        function showLogin() {
            document.getElementById('loginForm').style.display = 'block';
            document.getElementById('adminLoginForm').style.display = 'none';
            document.getElementById('registerForm').style.display = 'none';

            // Update nav pill active states
            document.querySelectorAll('.nav-pill').forEach(pill => pill.classList.remove('active'));
            document.querySelector('.nav-pill:nth-last-child(2)').classList.add('active');
        }

        function showAdminLogin() {
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('adminLoginForm').style.display = 'block';
            document.getElementById('registerForm').style.display = 'none';

            // Update nav pill active states
            document.querySelectorAll('.nav-pill').forEach(pill => pill.classList.remove('active'));
            document.querySelector('.nav-pill:last-child').classList.add('active');
        }

        function showRegister() {
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('adminLoginForm').style.display = 'none';
            document.getElementById('registerForm').style.display = 'block';
        }

        // Password confirmation validation
        const registerForm = document.getElementById('registerForm');
        registerForm.addEventListener('submit', function (e) {
            const password = registerForm.querySelector('input[name="password"]').value;
            const confirmPassword = registerForm.querySelector('input[name="confirm_password"]').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });

    </script>
</body>

</html>