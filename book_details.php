<?php
session_start();
include 'config.php';

$book_id = $_GET['id'] ?? null;
if (!$book_id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT b.*, a.Name AS Author, c.CategoryName FROM Books b JOIN Authors a ON b.AuthorID = a.AuthorID JOIN Categories c ON b.CategoryID = c.CategoryID WHERE b.BookID = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    echo "Book not found";
    exit;
}

// Fetch reviews
$stmt_reviews = $pdo->prepare("SELECT r.*, u.Username FROM Reviews r JOIN Users u ON r.UserID = u.UserID WHERE r.BookID = ? ORDER BY r.ReviewDate DESC");
$stmt_reviews->execute([$book_id]);
$reviews = $stmt_reviews->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['Title']); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php">Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="user_dashboard.php">Dashboard</a>
                <a href="cart.php">Cart</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="book-detail-main">
        <div class="book-details">
            <?php if ($book['Image']): ?>
                <img src="images/<?php echo htmlspecialchars($book['Image']); ?>" alt="<?php echo htmlspecialchars($book['Title']); ?>" style="max-width: 250px; height: auto; margin-bottom: 15px;">
            <?php endif; ?>
            <h2><?php echo htmlspecialchars($book['Title']); ?></h2>
            <p>Author: <?php echo htmlspecialchars($book['Author']); ?></p>
            <p>Category: <?php echo htmlspecialchars($book['CategoryName']); ?></p>
            <p>Price: $<?php echo htmlspecialchars($book['Price']); ?></p>
            <p>Published: <?php echo htmlspecialchars($book['PublishedDate']); ?></p>
            <p>Stock: <?php echo htmlspecialchars($book['Stock']); ?></p>
        </div>

        <div class="reviews">
            <h3>Reviews</h3>
            <?php if (isset($_SESSION['user_id'])): ?>
                <form action="add_review.php" method="post">
                    <input type="hidden" name="book_id" value="<?php echo $book['BookID']; ?>">
                    <label for="rating">Rating:</label>
                    <select id="rating" name="rating" required>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                    <label for="comment">How's your review? Feel free to add your feedbacks here:</label>
                    <textarea id="comment" name="comment"></textarea>
                    <button type="submit">Submit Review</button>
                </form>
            <?php endif; ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review">
                    <p><strong><?php echo htmlspecialchars($review['Username']); ?></strong> (<?php echo $review['Rating']; ?>/5)</p>
                    <p><?php echo htmlspecialchars($review['Comment']); ?></p>
                    <p><?php echo $review['ReviewDate']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2025, booksandpleased. </p>
    </footer>
</body>
</html>
