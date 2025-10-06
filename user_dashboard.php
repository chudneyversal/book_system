<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch orders
$stmt = $pdo->prepare("SELECT * FROM Orders WHERE UserID = ? ORDER BY OrderDate DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>User Dashboard</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="cart.php">Cart</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main>
        <h2>Your Orders</h2>
        <?php if (isset($_GET['order']) && $_GET['order'] == 'success'): ?>
            <p>Order placed successfully!</p>
        <?php endif; ?>
        <?php if ($orders): ?>
            <?php foreach ($orders as $order): ?>
                <div class="order">
                    <h3>Order #<?php echo $order['OrderID']; ?> - <?php echo $order['OrderDate']; ?> - Status: <?php echo $order['Status']; ?> - Total: $<?php echo $order['TotalAmount']; ?></h3>
                    <?php
                    $stmt_items = $pdo->prepare("SELECT oi.*, b.Title FROM OrderItems oi JOIN Books b ON oi.BookID = b.BookID WHERE oi.OrderID = ?");
                    $stmt_items->execute([$order['OrderID']]);
                    $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <ul>
                        <?php foreach ($items as $item): ?>
                            <li><?php echo htmlspecialchars($item['Title']); ?> - Quantity: <?php echo $item['Quantity']; ?> - Price: $<?php echo $item['Price']; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No orders yet.</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2023 Book Store</p>
    </footer>
</body>
</html>
