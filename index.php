<?php
session_start();
include 'config.php';

// Fetch username if logged in
$username = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT Username FROM Users WHERE UserID = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $username = $user['Username'];
    }
}

// Fetch books with author and category
$stmt = $pdo->query("SELECT b.BookID, b.Title, b.Price, b.Stock, b.Image, a.Name AS Author, c.CategoryName FROM Books b JOIN Authors a ON b.AuthorID = a.AuthorID JOIN Categories c ON b.CategoryID = c.CategoryID");
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html  > 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Store</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successMessages = document.querySelectorAll('.success-message');
            successMessages.forEach(function(message) {
                setTimeout(function() {
                    message.style.display = 'none';
                }, 60000); // 1 minute = 60000 milliseconds
            });
        });
    </script>
</head>
<body>
    <header>
        <h1>Welcome to booksandpleased! Find your next favorite read.</h1>
        <?php if ($username): ?>
            <p class="welcome-message">Hello, <?php echo htmlspecialchars($username); ?>! You are logged in.</p>
        <?php endif; ?>
        <nav>
            <a href="index.php">Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="user_dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
                <a href="admin_login.php">Admin</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <h2>Available Books</h2>
        <div class="books">
            <?php foreach ($books as $book): ?>
                <div class="book">
                    <?php if ($book['Image']): ?>
                        <img src="images/<?php echo htmlspecialchars($book['Image']); ?>" alt="<?php echo htmlspecialchars($book['Title']); ?>" style="max-width: 200px; height: auto;">
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($book['Title']); ?></h3>
                    <p>Author: <?php echo htmlspecialchars($book['Author']); ?></p>
                    <p>Category: <?php echo htmlspecialchars($book['CategoryName']); ?></p>
                    <p>Price: $<?php echo htmlspecialchars($book['Price']); ?></p>
                    <p>Stock: <?php echo htmlspecialchars($book['Stock']); ?></p>
                    <a href="book_details.php?id=<?php echo $book['BookID']; ?>">View Details</a>
                    <form action="add_to_cart.php" method="post">
                        <input type="hidden" name="book_id" value="<?php echo $book['BookID']; ?>">
                        <input type="number" name="quantity" value="1" min="1" max="<?php echo $book['Stock']; ?>">
                        <button type="submit">Add to Cart</button>
                    </form>
                    <?php if (isset($_GET['added']) && $_GET['added'] == $book['BookID']): ?>
                        <p id="success-message-<?php echo $book['BookID']; ?>" class="success-message">This book has been added to cart successfully!</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2025, booksandpleased. </p>
    </footer>
</body>
</html>
