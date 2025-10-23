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
    <script>
        function changeQuantity(bookId, change) {
            const quantityInput = document.getElementById('quantity-' + bookId);
            let currentValue = parseInt(quantityInput.value);
            let newValue = currentValue + change;

            if (newValue < 0) {
                newValue = 0;
            }

            quantityInput.value = newValue;
        }
    </script>
</head>
<body>
    <header>
        <h1>Shopping Cart</h1>
        <nav>
            <a href="index.php">&larr; Back to Home</a>
            <a href="user_dashboard.php">Back to Dashboard</a>
        </nav>
    </header>

    <main>
        <?php if ($cart_items): ?>
            <form action="checkout.php" method="post">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>New Quantity</th>
                            <th>Total Amount</th>
                            <th>Payment Method</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['title']); ?></td>
                                <td>$<?php echo htmlspecialchars($item['price']); ?></td>
                                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                <td>
                                    <div class="quantity-controls">
                                        <button type="button" class="quantity-btn" onclick="changeQuantity(<?php echo $item['id']; ?>, -1)">-</button>
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="0" id="quantity-<?php echo $item['id']; ?>" form="update-form-<?php echo $item['id']; ?>" readonly>
                                        <button type="button" class="quantity-btn" onclick="changeQuantity(<?php echo $item['id']; ?>, 1)">+</button>
                                    </div>
                                </td>
                                <td>$<?php echo htmlspecialchars($item['total']); ?></td>
                                <td>
                                    <select name="payment_method[<?php echo $item['id']; ?>]" required>
                                        <option value="">Select Payment Method</option>
                                        <option value="credit_card" <?php echo (isset($_SESSION['payment_methods'][$item['id']]) && $_SESSION['payment_methods'][$item['id']] == 'credit_card') ? 'selected' : ''; ?>>Credit Card</option>
                                        <option value="debit_card" <?php echo (isset($_SESSION['payment_methods'][$item['id']]) && $_SESSION['payment_methods'][$item['id']] == 'debit_card') ? 'selected' : ''; ?>>Debit Card</option>
                                        <option value="paypal" <?php echo (isset($_SESSION['payment_methods'][$item['id']]) && $_SESSION['payment_methods'][$item['id']] == 'paypal') ? 'selected' : ''; ?>>PayPal</option>
                                        <option value="bank_transfer" <?php echo (isset($_SESSION['payment_methods'][$item['id']]) && $_SESSION['payment_methods'][$item['id']] == 'bank_transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
                                    </select>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 5px; justify-content: center;">
                                        <form id="update-form-<?php echo $item['id']; ?>" action="update_cart.php" method="post" style="display:inline;">
                                            <input type="hidden" name="book_id" value="<?php echo $item['id']; ?>">
                                            <input type="hidden" name="payment_method" value="<?php echo $_SESSION['payment_methods'][$item['id']] ?? ''; ?>">
                                            <button type="submit" style="width: 80px;">Update</button>
                                        </form>
                                        <form action="remove_from_cart.php" method="post" style="display:inline;">
                                            <input type="hidden" name="book_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" style="width: 80px;">Remove</button>
                                        </form>
                                    </div>
                                </td>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" class="checkout-btn">
                        <i class="fas fa-credit-card"></i> Proceed to Checkout
                    </button>
                </div>
            </form>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2025, booksandpleased. </p>
    </footer>
</body>
</html>
