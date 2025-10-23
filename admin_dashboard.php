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
    <meta charset="UTF-8">
    <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .admin-greeting {
            animation: bounceIn 1s ease-out;
        }

        .manage-system {
            animation: bounceIn 1s ease-out, pulse 2s infinite 1s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0.3);
                opacity: 0;
            }
            50% {
                transform: scale(1.05);
            }
            70% {
                transform: scale(0.9);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
        <p class="admin-greeting">Welcome, Admin! What changes do you have in mind today?</p>
        <nav>
            <a href="index.php">Home</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main>
        <h2 class="manage-system">Manage System</h2>
        <div class="admin-buttons">
            <button onclick="window.location.href='manage_books.php'">Manage Books</button>
            <button onclick="window.location.href='manage_authors.php'">Manage Authors</button>
            <button onclick="window.location.href='manage_categories.php'">Manage Categories</button>
            <button onclick="window.location.href='manage_orders.php'">Manage Orders</button>
        </div>
    </main>

    <footer>
        <p>&copy; 2025, booksandpleased. </p>
    </footer>
</body>
</html>
