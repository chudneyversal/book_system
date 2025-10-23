<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = $_POST['order_id'] ?? null;

if (!$order_id) {
    header('Location: user_dashboard.php');
    exit;
}

// Check if the order belongs to the user and is cancellable
$stmt = $pdo->prepare("SELECT Status FROM Orders WHERE OrderID = ? AND UserID = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "Order not found or not authorized.";
    exit;
}

if ($order['Status'] == 'Cancelled' || $order['Status'] == 'Shipped') {
    echo "Order cannot be cancelled.";
    exit;
}

// Delete order items first
$stmt_delete_items = $pdo->prepare("DELETE FROM OrderItems WHERE OrderID = ?");
$stmt_delete_items->execute([$order_id]);

// Delete the order
$stmt_delete_order = $pdo->prepare("DELETE FROM Orders WHERE OrderID = ?");
$stmt_delete_order->execute([$order_id]);

// Reset auto-increment to 1 if no orders left
$stmt = $pdo->query("SELECT COUNT(*) AS count FROM Orders");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result['count'] == 0) {
    $pdo->exec("ALTER TABLE Orders AUTO_INCREMENT = 1");
}

header('Location: user_dashboard.php');
exit;
?>
