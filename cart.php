<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$cart = $_SESSION['cart'] ?? [];
$cart_items = [];

if ($cart) {
    $placeholders = str_repeat('?,', count($cart) - 1) . '?';
    $stmt = $pdo->prepare("SELECT BookID, Title, Price FROM Books WHERE BookID IN ($placeholders)");
    $stmt->execute(array_keys($cart));
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($books as $book) {
        $book_id = $book['BookID'];
        $cart_items[] = [
            'id' => $book_id,
            'title' => $book['Title'],
            'price' => $book['Price'],
            'quantity' => $cart[$book_id],
            'total' => $book['Price'] * $cart[$book_id]
        ];
    }
}

$total = array_sum(array_column($cart_items, 'total'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Shopping Cart</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="user_dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main>
        <?php if ($cart_items): ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['title']); ?></td>
                            <td>$<?php echo htmlspecialchars($item['price']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td>$<?php echo htmlspecialchars($item['total']); ?></td>
                            <td>
                                <form action="update_cart.php" method="post" style="display:inline;">
                                    <input type="hidden" name="book_id" value="<?php echo $item['id']; ?>">
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="0">
                                    <button type="submit">Update</button>
                                </form>
                                <form action="remove_from_cart.php" method="post" style="display:inline;">
                                    <input type="hidden" name="book_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p>Total: $<?php echo $total; ?></p>
            <a href="checkout.php">Proceed to Checkout</a>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2023 Book Store</p>
    </footer>
</body>
</html>
