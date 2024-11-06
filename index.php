<?php
session_start();

// Database configuration (replace with your actual database details)
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'ecommerce_db';

// Connect to database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    $sql = "SELECT id, username, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            echo json_encode(['success' => true]);
            exit;
        }
    }
    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mr. Deny - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        :root {
            --primary-color: #1a1a2e;
            --secondary-color: #16213e;
            --accent-color: #0f3460;
            --background-color: #e94560;
            --text-color: #ffffff;
	    --input-bg: rgba(255, 255, 255, 0.1);
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 2rem;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            background: rgba(26, 26, 46, 0.8);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-color);
            text-transform: uppercase;
            letter-spacing: 2px;
	    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .logo-subtitle {
            font-size: 0.9rem;
            color: var(--highlight-color);
            margin-top: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-weight: 500;
            letter-spacing: 1px;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem;
	        background: var(--input-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
	        color: var(--text-color);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            border-color: var(--highlight-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(233, 69, 96, 0.2);
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 2.5rem;
            cursor: pointer;
            color: #666;
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .additional-links {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
        }

        .additional-links a {
            color: var(--highlight-color);
            text-decoration: none;
            margin: 0 0.5rem;
            transition: color 0.3s ease;
        }

        .additional-links a:hover {
            color: #d63d57;
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            display: none;
        }

        .alert-error {
            background-color: rgba(233, 69, 96, 0.2);
            border: 1px solid var(--highlight-color);
            color: var(--text-color);
        }

        .alert-success {
            bbackground-color: rgba(39, 174, 96, 0.2);
            border: 1px solid #27ae60;
            color: var(--text-color);
        }

        .loading {
            display: none;
            justify-content: center;
            align-items: center;
            margin-top: 1rem;
        }

        .loading-spinner {
            width: 25px;
            height: 25px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid var(--highlight-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-container {
            animation: fadeIn 0.5s ease forwards;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <div class="logo">DENY CITY</div>
            <div class="logo-subtitle">Welcome Back</div>
        </div>

        <div class="alert alert-error" id="errorAlert"></div>
        <div class="alert alert-success" id="successAlert"></div>

        <form id="loginForm">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <span class="password-toggle" id="passwordToggle">👁️</span>
            </div>

            <button type="submit" class="submit-btn">Sign In</button>
            
            <div class="loading" id="loading">
                <div class="loading-spinner"></div>
            </div>
        </form>

        <div class="additional-links">
            <a href="forget.php">Forgot Password?</a>
            <span>|</span>
            <a href="createacc.php">Create Account</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const errorAlert = document.getElementById('errorAlert');
            const successAlert = document.getElementById('successAlert');
            const loading = document.getElementById('loading');
            const passwordToggle = document.getElementById('passwordToggle');
            const passwordInput = document.getElementById('password');

            // Toggle password visibility
            passwordToggle.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                passwordToggle.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
            });

            // Handle form submission
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Hide any existing alerts
                errorAlert.style.display = 'none';
                successAlert.style.display = 'none';
                
                // Show loading spinner
                loading.style.display = 'flex';
                
                // Disable submit button
                const submitButton = loginForm.querySelector('button[type="submit"]');
                submitButton.disabled = true;

                // Get form data
                const formData = new FormData(loginForm);

                // Send login request
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        successAlert.textContent = 'Login successful! Redirecting...';
                        successAlert.style.display = 'block';
                        // Redirect to dashboard or home page
                        setTimeout(() => {
                            window.location.href = 'dashboard.php';
                        }, 1500);
                    } else {
                        errorAlert.textContent = data.message || 'Login failed. Please try again.';
                        errorAlert.style.display = 'block';
                    }
                })
                .catch(error => {
                    errorAlert.textContent = 'An error occurred. Please try again.';
                    errorAlert.style.display = 'block';
                })
                .finally(() => {
                    loading.style.display = 'none';
                    submitButton.disabled = false;
                });
            });
        });
    </script>
</body>
</html>