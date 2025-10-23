<?php
session_start();

$book_id = $_POST['book_id'] ?? null;
$quantity = $_POST['quantity'] ?? 0;
$payment_method = $_POST['payment_method'] ?? null;

if ($book_id && isset($_SESSION['cart'][$book_id])) {
    if ($quantity > 0) {
        $_SESSION['cart'][$book_id] = $quantity;
    } else {
        unset($_SESSION['cart'][$book_id]);
    }
}

// Store payment method in session if provided
if ($payment_method) {
    $_SESSION['payment_methods'][$book_id] = $payment_method;
}

header('Location: cart.php');
exit;
?>
