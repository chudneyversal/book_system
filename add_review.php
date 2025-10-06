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

    $stmt = $pdo->prepare("INSERT INTO Reviews (BookID, UserID, Rating, Comment) VALUES (?, ?, ?, ?)");
    $stmt->execute([$book_id, $user_id, $rating, $comment]);

    header('Location: book_details.php?id=' . $book_id);
    exit;
}
?>
