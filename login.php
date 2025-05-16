<?php
session_start();

// Redirect jika sudah login
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Load user data
$users = [];
if (file_exists('users.json')) {
    $users = json_decode(file_get_contents('users.json'), true);
}

// Handle login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi!";
    } elseif (!isset($users[$username]) || !password_verify($password, $users[$username]['password'])) {
        $error = "Username atau password salah!";
    } else {
        $_SESSION['user'] = $username;
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caffè Fiorentino - Login</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #d7ccc8 0%, #8d6e63 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            padding: 2rem;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo h1 {
            color: #3e2723;
            font-size: 2.2rem;
            letter-spacing: 2px;
        }
        
        .login-form .form-group {
            margin-bottom: 1.5rem;
        }
        
        .login-form label {
            display: block;
            margin-bottom: 0.5rem;
            color: #4e342e;
            font-weight: 600;
        }
        
        .login-form input {
            width: 100%;
            padding: 12px;
            border: 2px solid #d7ccc8;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .login-form input:focus {
            outline: none;
            border-color: #6d4c41;
        }
        
        .login-btn {
            width: 100%;
            padding: 12px;
            background: #6d4c41;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .login-btn:hover {
            background: #8d6e63;
        }
        
        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #4e342e;
        }
        
        .register-link a {
            color: #bf360c;
            text-decoration: none;
            font-weight: 600;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .error-message {
            color: #c62828;
            background: #ffebee;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            border: 1px solid #ef9a9a;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>Caffè Fiorentino</h1>
        </div>

        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form class="login-form" method="POST">
            <input type="hidden" name="action" value="login">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required
                    placeholder="Masukkan username"
                >
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    placeholder="Masukkan password"
                >
            </div>
            
            <button type="submit" class="login-btn">Masuk</button>
            
            <div class="register-link">
                Belum punya akun? <a href="register.php">Daftar disini</a>
            </div>
        </form>
    </div>
</body>
</html>