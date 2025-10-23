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

// Fetch reviews, excluding the current user's review if logged in
$user_id = $_SESSION['user_id'] ?? null;
$query = "SELECT r.*, u.Username FROM Reviews r JOIN Users u ON r.UserID = u.UserID WHERE r.BookID = ?";
$params = [$book_id];
if ($user_id) {
    $query .= " AND r.UserID != ?";
    $params[] = $user_id;
}
$query .= " ORDER BY r.ReviewDate DESC";
$stmt_reviews = $pdo->prepare($query);
$stmt_reviews->execute($params);
$reviews = $stmt_reviews->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['Title']); ?></title>
    <link rel="stylesheet" href="styles.css">
    <style>
        #imageModal {
            animation: modalFadeIn 0.5s ease-out;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        #modalImage {
            animation: imageZoomIn 0.5s ease-out;
        }

        @keyframes imageZoomIn {
            from {
                transform: translate(-50%, -50%) scale(0.8);
                opacity: 0;
            }
            to {
                transform: translate(-50%, -50%) scale(1);
                opacity: 1;
            }
        }

        .book-details img:hover {
            transform: scale(1.05);
            transition: transform 0.3s ease;
        }

        .book-details img:active {
            transform: scale(0.95);
            transition: transform 0.1s ease;
        }

        .book-details {
            animation: slideInLeft 1s ease-out;
        }

        .reviews {
            animation: slideInRight 1s ease-out 0.5s both;
        }

        @keyframes slideInLeft {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="index.php">Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="user_dashboard.php">Dashboard</a>
                <a href="cart.php">Cart</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="book-detail-main">
        <div class="book-details">
            <h3>Book Details</h3>
            <?php if ($book['Image']): ?>
                <img src="images/<?php echo htmlspecialchars($book['Image']); ?>" alt="<?php echo htmlspecialchars($book['Title']); ?>" style="max-width: 250px; height: auto; margin-bottom: 15px; cursor: pointer;" onclick="openModal('images/<?php echo htmlspecialchars($book['Image']); ?>')">
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
                <?php
                // Check if user already has a review for this book
                $user_review_stmt = $pdo->prepare("SELECT * FROM Reviews WHERE BookID = ? AND UserID = ?");
                $user_review_stmt->execute([$book_id, $_SESSION['user_id']]);
                $user_review = $user_review_stmt->fetch(PDO::FETCH_ASSOC);
                ?>
                <form action="add_review.php" method="post">
                    <input type="hidden" name="book_id" value="<?php echo $book['BookID']; ?>">
                    <label for="rating">Rating:</label>
                    <select id="rating" name="rating" required>
                        <option value="1" <?php echo ($user_review && $user_review['Rating'] == 1) ? 'selected' : ''; ?>>1</option>
                        <option value="2" <?php echo ($user_review && $user_review['Rating'] == 2) ? 'selected' : ''; ?>>2</option>
                        <option value="3" <?php echo ($user_review && $user_review['Rating'] == 3) ? 'selected' : ''; ?>>3</option>
                        <option value="4" <?php echo ($user_review && $user_review['Rating'] == 4) ? 'selected' : ''; ?>>4</option>
                        <option value="5" <?php echo ($user_review && $user_review['Rating'] == 5) ? 'selected' : ''; ?>>5</option>
                    </select>
                    <label for="comment">How's your review? Feel free to add your feedbacks here:</label>
                    <textarea id="comment" name="comment"><?php echo $user_review ? htmlspecialchars($user_review['Comment']) : ''; ?></textarea>
                    <button type="submit">Update Review</button>
                </form>
                <?php if ($user_review): ?>
                    <div class="your-review">
                        <h4>Your Review</h4>
                        <div class="review">
                            <p><strong>Rating:</strong> <?php echo $user_review['Rating']; ?>/5</p>
                            <p><strong>Honest Review/Feedback:</strong> <?php echo htmlspecialchars($user_review['Comment']); ?></p>
                            <p><strong>Date:</strong> <?php echo date('Y-m-d', strtotime($user_review['ReviewDate'])); ?></p>
                            <p><strong>Time:</strong> <?php echo date('H:i:s', strtotime($user_review['ReviewDate'])); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p style="color: #E0AA3E; font-style: italic;">Login to add your review and share your thoughts!</p>
            <?php endif; ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review">
                    <p><strong>Reviewed by:</strong> <?php echo htmlspecialchars($review['Username']); ?></p>
                    <p><strong>Rating:</strong> <?php echo $review['Rating']; ?>/5</p>
                    <p><strong>Honest Review/Feedback:</strong> <?php echo htmlspecialchars($review['Comment']); ?></p>
                    <p><strong>Date:</strong> <?php echo date('Y-m-d', strtotime($review['ReviewDate'])); ?></p>
                    <p><strong>Time:</strong> <?php echo date('H:i:s', strtotime($review['ReviewDate'])); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Modal for image enlargement -->
    <div id="imageModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8);">
        <span style="position: absolute; top: 15px; right: 35px; color: #aaa; font-size: 40px; font-weight: bold; cursor: pointer;" onclick="closeModal()">&times;</span>
        <img id="modalImage" style="margin: auto; display: block; max-width: 80%; max-height: 80%; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
    </div>

    <script>
        function openModal(src) {
            document.getElementById('imageModal').style.display = 'block';
            document.getElementById('modalImage').src = src;
        }

        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        // Close modal when clicking outside the image
        document.getElementById('imageModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeModal();
            }
        });
    </script>

    <footer>
        <p>&copy; 2025, booksandpleased. </p>
    </footer>
</body>
</html>
