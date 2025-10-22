<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT UserID, PasswordHash FROM Users WHERE Username = ? OR Email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['PasswordHash'])) {
        // Check if user is admin (UserID == 1 or role == 'admin')
        $role = ($user['UserID'] == 1) ? 'admin' : 'user';
        if ($role == 'admin') {
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['role'] = $role;
            header('Location: admin_dashboard.php');
            exit;
        } else {
            $error = "Access denied. Admin privileges required.";
        }
    } else {
        $error = "Invalid credentials";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Admin Login</h1>
        <nav>
            <a href="index.php">Home</a>
        </nav>
    </header>

    <main>
        <form action="admin_login.php" method="post">
            <label for="username">Username or Email:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login as Admin</button>
            <?php if (isset($error)) echo "<p>$error</p>"; ?>
        </form>
    </main>

    <footer>
        <p>&copy; 2025 Book Store</p>
    </footer>
</body>
</html>
