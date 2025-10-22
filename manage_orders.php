<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE Orders SET Status = ? WHERE OrderID = ?");
    $stmt->execute([$status, $order_id]);

    header('Location: manage_orders.php?updated=1');
    exit;
}

// Fetch all orders with user details
$stmt = $pdo->prepare("
    SELECT o.OrderID, o.OrderDate, o.Status, o.TotalAmount, u.Username, u.Email
    FROM Orders o
    JOIN Users u ON o.UserID = u.UserID
    ORDER BY o.OrderDate DESC
");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Manage Orders</h1>
        <nav>
            <a href="admin_dashboard.php">Back to Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main>
        <h2>Order Management</h2>

        <?php if (isset($_GET['updated'])): ?>
            <p class="success-message">Order status updated successfully!</p>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Order Date</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['OrderID']); ?></td>
                        <td><?php echo htmlspecialchars($order['Username']); ?></td>
                        <td><?php echo htmlspecialchars($order['Email']); ?></td>
                        <td><?php echo htmlspecialchars($order['OrderDate']); ?></td>
                        <td>$<?php echo number_format($order['TotalAmount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($order['Status']); ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $order['OrderID']; ?>">
                                <button type="submit" name="status" value="Approved">Approve</button>
                                <button type="submit" name="status" value="Pending">Set Pending</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>

    <footer>
        <p>&copy; 2025 Book Store</p>
    </footer>
</body>
</html>
