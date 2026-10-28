<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit;
        }
    }
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="vibeshield-site-verification" content="vibeshield-site-verification=6683b97c3dd0b8d8d27fc5551a08e72bb0a748303bab559a" />
    <title>Login — Project Visit Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/jpeg" href="assets/logo.jpeg">
    <link href="assets/style.css?v=2" rel="stylesheet">
    <style>
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #00662B 0%, #00883A 100%);
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border-radius: 12px;
            overflow: hidden;
        }
        .login-logo {
            font-size: 2.5rem;
            line-height: 1;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card card shadow">
            <div class="card-body p-4 text-center">
                <img src="assets/logo.jpeg" alt="Dawat-e-Islami" class="mb-3" style="max-height:100px;">
                <h4 class="mb-1">دعوتِ اسلامی</h4>
                <p class="text-muted mb-4">Project Visit Reports — Login</p>
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3 text-start">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3 text-start">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
