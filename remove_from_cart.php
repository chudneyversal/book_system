<?php
session_start();

$book_id = $_POST['book_id'] ?? null;

if ($book_id && isset($_SESSION['cart'][$book_id])) {
    unset($_SESSION['cart'][$book_id]);
}

header('Location: cart.php');
exit;
?>
