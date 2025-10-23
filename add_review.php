<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $book_id = $_POST['book_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    $user_id = $_SESSION['user_id'];

    // Check if user already has a review for this book
    $check_stmt = $pdo->prepare("SELECT ReviewID FROM Reviews WHERE BookID = ? AND UserID = ?");
    $check_stmt->execute([$book_id, $user_id]);
    $existing_review = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_review) {
        // Update existing review
        $stmt = $pdo->prepare("UPDATE Reviews SET Rating = ?, Comment = ?, ReviewDate = CURRENT_TIMESTAMP WHERE ReviewID = ?");
        $stmt->execute([$rating, $comment, $existing_review['ReviewID']]);
    } else {
        // Insert new review
        $stmt = $pdo->prepare("INSERT INTO Reviews (BookID, UserID, Rating, Comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$book_id, $user_id, $rating, $comment]);
    }

    header('Location: book_details.php?id=' . $book_id);
    exit;
}
?>
