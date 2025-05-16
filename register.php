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
    $users = json_decode(file_get_contents('users.json'), true) ?: [];
}

// Handle registration
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi
    if (empty($username)) {
        $error = "Username tidak boleh kosong!";
    } elseif (strlen($username) < 4) {
        $error = "Username minimal 4 karakter!";
    } elseif (preg_match('/\s/', $username)) {
        $error = "Username tidak boleh mengandung spasi!";
    } elseif (empty($password)) {
        $error = "Password tidak boleh kosong!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif ($password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok!";
    } elseif (isset($users[$username])) {
        $error = "Username sudah digunakan!";
    } else {
        // Registrasi user baru
        $users[$username] = [
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT));
        $success = "Registrasi berhasil! Silakan login.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caffè Fiorentino - Register</title>
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
        
        .register-container {
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
        
        .register-form .form-group {
            margin-bottom: 1.5rem;
        }
        
        .register-form label {
            display: block;
            margin-bottom: 0.5rem;
            color: #4e342e;
            font-weight: 600;
        }
        
        .register-form input {
            width: 100%;
            padding: 12px;
            border: 2px solid #d7ccc8;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .register-form input:focus {
            outline: none;
            border-color: #6d4c41;
        }
        
        .register-btn {
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
        
        .register-btn:hover {
            background: #8d6e63;
        }
        
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #4e342e;
        }
        
        .login-link a {
            color: #bf360c;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
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
        
        .success-message {
            color: #2e7d32;
            background: #e8f5e9;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            border: 1px solid #a5d6a7;
        }
        
        .password-hint {
            font-size: 0.8rem;
            color: #6d4c41;
            margin-top: 0.3rem;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <h1>Caffè Fiorentino</h1>
        </div>

        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form class="register-form" method="POST">
            <input type="hidden" name="action" value="register">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required
                    placeholder="Minimal 4 karakter, tanpa spasi"
                    value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    placeholder="Minimal 6 karakter"
                >
                <p class="password-hint">Password harus minimal 6 karakter</p>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    required
                    placeholder="Ketik ulang password"
                >
            </div>
            
            <button type="submit" class="register-btn">Daftar</button>
            
            <div class="login-link">
                Sudah punya akun? <a href="login.php">Login disini</a>
            </div>
        </form>
    </div>
</body>
</html>