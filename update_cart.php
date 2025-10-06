<?php
session_start();

$book_id = $_POST['book_id'] ?? null;
$quantity = $_POST['quantity'] ?? 0;

if ($book_id && isset($_SESSION['cart'][$book_id])) {
    if ($quantity > 0) {
        $_SESSION['cart'][$book_id] = $quantity;
    } else {
        unset($_SESSION['cart'][$book_id]);
    }
}

header('Location: cart.php');
exit;
?>
