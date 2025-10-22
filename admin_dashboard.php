<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main>
        <h2>Manage System</h2>
        <ul>
            <li><a href="manage_books.php">Manage Books</a></li>
            <li><a href="manage_authors.php">Manage Authors</a></li>
            <li><a href="manage_categories.php">Manage Categories</a></li>
        </ul>
    </main>

    <footer>
        <p>&copy; 2025 Book Store </p>
    </footer>
</body>
</html>
