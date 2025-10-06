<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'];

try {
    $pdo->beginTransaction();

    // Calculate total
    $placeholders = str_repeat('?,', count($cart) - 1) . '?';
    $stmt = $pdo->prepare("SELECT BookID, Price FROM Books WHERE BookID IN ($placeholders)");
    $stmt->execute(array_keys($cart));
    $books = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // BookID => Price

    $total = 0;
    foreach ($cart as $book_id => $quantity) {
        $total += $books[$book_id] * $quantity;
    }

    // Insert order
    $stmt_order = $pdo->prepare("INSERT INTO Orders (UserID, TotalAmount) VALUES (?, ?)");
    $stmt_order->execute([$user_id, $total]);
    $order_id = $pdo->lastInsertId();

    // Insert order items
    $stmt_item = $pdo->prepare("INSERT INTO OrderItems (OrderID, BookID, Quantity, Price) VALUES (?, ?, ?, ?)");
    foreach ($cart as $book_id => $quantity) {
        $stmt_item->execute([$order_id, $book_id, $quantity, $books[$book_id]]);
    }

    $pdo->commit();

    // Clear cart
    unset($_SESSION['cart']);

    header('Location: user_dashboard.php?order=success');
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Checkout failed: " . $e->getMessage();
}
?>
