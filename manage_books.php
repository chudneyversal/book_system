<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Fetch authors and categories for dropdowns
$authors = $pdo->query("SELECT * FROM Authors")->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT * FROM Categories")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($action == 'add') {
        $stmt = $pdo->prepare("INSERT INTO Books (Title, AuthorID, CategoryID, Price, PublishedDate, Stock, Image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['title'], $_POST['author_id'], $_POST['category_id'], $_POST['price'], $_POST['published_date'], $_POST['stock'], $_POST['image']]);
    } elseif ($action == 'edit' && $id) {
        $stmt = $pdo->prepare("UPDATE Books SET Title=?, AuthorID=?, CategoryID=?, Price=?, PublishedDate=?, Stock=?, Image=? WHERE BookID=?");
        $stmt->execute([$_POST['title'], $_POST['author_id'], $_POST['category_id'], $_POST['price'], $_POST['published_date'], $_POST['stock'], $_POST['image'], $id]);
    } elseif ($action == 'delete' && $id) {
        $stmt = $pdo->prepare("DELETE FROM Books WHERE BookID=?");
        $stmt->execute([$id]);
    }
    header('Location: manage_books.php');
    exit;
}

$books = $pdo->query("SELECT b.*, a.Name AS Author, c.CategoryName FROM Books b JOIN Authors a ON b.AuthorID = a.AuthorID JOIN Categories c ON b.CategoryID = c.CategoryID ORDER BY b.BookID DESC")->fetchAll(PDO::FETCH_ASSOC);

$book = null;
if ($action == 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM Books WHERE BookID=?");
    $stmt->execute([$id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Manage Books</h1>
        <nav>
            <a href="admin_dashboard.php">Admin Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main>
        <?php if ($action == 'list'): ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $b): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($b['Title']); ?></td>
                            <td><?php echo htmlspecialchars($b['Author']); ?></td>
                            <td><?php echo htmlspecialchars($b['CategoryName']); ?></td>
                            <td>$<?php echo htmlspecialchars($b['Price']); ?></td>
                            <td><?php echo htmlspecialchars($b['Stock']); ?></td>
                            <td>
                                <button onclick="window.location.href='manage_books.php?action=edit&id=<?php echo $b['BookID']; ?>'">Edit</button>
                                <button onclick="if(confirm('Are you sure?')) window.location.href='manage_books.php?action=delete&id=<?php echo $b['BookID']; ?>'">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="manage-buttons" style="margin-top: 20px;">
                <button onclick="window.location.href='manage_books.php?action=add'">Add New Book</button>
            </div>
        <?php elseif ($action == 'add' || $action == 'edit'): ?>
            <form action="manage_books.php?action=<?php echo $action; ?><?php if ($id) echo '&id=' . $id; ?>" method="post">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="<?php echo $book['Title'] ?? ''; ?>" required>

                <label for="author_id">Author:</label>
                <select id="author_id" name="author_id" required>
                    <?php foreach ($authors as $a): ?>
                        <option value="<?php echo $a['AuthorID']; ?>" <?php if (($book['AuthorID'] ?? '') == $a['AuthorID']) echo 'selected'; ?>><?php echo htmlspecialchars($a['Name']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="category_id">Category:</label>
                <select id="category_id" name="category_id" required>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?php echo $c['CategoryID']; ?>" <?php if (($book['CategoryID'] ?? '') == $c['CategoryID']) echo 'selected'; ?>><?php echo htmlspecialchars($c['CategoryName']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="price">Price:</label>
                <input type="number" step="0.01" id="price" name="price" value="<?php echo $book['Price'] ?? ''; ?>" required>

                <label for="published_date">Published Date:</label>
                <input type="date" id="published_date" name="published_date" value="<?php echo $book['PublishedDate'] ?? ''; ?>">

                <label for="stock">Stock:</label>
                <input type="number" id="stock" name="stock" value="<?php echo $book['Stock'] ?? ''; ?>" required>

                <label for="image">Image URL:</label>
                <input type="text" id="image" name="image" value="<?php echo $book['Image'] ?? ''; ?>" placeholder="e.g., images/book1.jpg">

                <button type="submit"><?php echo ucfirst($action); ?> Book</button>
            </form>
            <div class="manage-buttons">
                <button onclick="window.location.href='manage_books.php'">Back to List</button>
            </div>
        <?php endif; ?>
    </main> 

    <footer>
        <p>&copy; 2025, booksandpleased. </p>
    </footer>
</body>
</html>
