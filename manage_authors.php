<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $error = '';
    if ($action == 'add') {
        $stmt = $pdo->prepare("INSERT INTO Authors (Name, Bio, BirthDate) VALUES (?, ?, ?)");
        try {
            $stmt->execute([$_POST['name'], $_POST['bio'], $_POST['birth_date']]);
        } catch (PDOException $e) {
            $error = "Error adding author: " . $e->getMessage();
        }
    } elseif ($action == 'edit' && $id) {
        $stmt = $pdo->prepare("UPDATE Authors SET Name=?, Bio=?, BirthDate=? WHERE AuthorID=?");
        try {
            $stmt->execute([$_POST['name'], $_POST['bio'], $_POST['birth_date'], $id]);
        } catch (PDOException $e) {
            $error = "Error updating author: " . $e->getMessage();
        }
    } elseif ($action == 'delete' && $id) {
        // Check if author has books
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM Books WHERE AuthorID=?");
        $stmt_check->execute([$id]);
        $count = $stmt_check->fetchColumn();
        if ($count > 0) {
            $error = "Cannot delete author because there are books associated with this author. Please delete or reassign the books first.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM Authors WHERE AuthorID=?");
            try {
                $stmt->execute([$id]);
            } catch (PDOException $e) {
                $error = "Error deleting author: " . $e->getMessage();
            }
        }
    }
    if (!$error) {
        header('Location: manage_authors.php');
        exit;
    }
}

$authors = $pdo->query("SELECT * FROM Authors")->fetchAll(PDO::FETCH_ASSOC);

$author = null;
if ($action == 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM Authors WHERE AuthorID=?");
    $stmt->execute([$id]);
    $author = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Authors</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Manage Authors</h1>
        <nav>
            <a href="admin_dashboard.php">Admin Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if ($action == 'list'): ?>
            <a href="manage_authors.php?action=add">Add New Author</a>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Bio</th>
                        <th>Birth Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($authors as $a): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($a['Name']); ?></td>
                            <td><?php echo htmlspecialchars($a['Bio']); ?></td>
                            <td><?php echo htmlspecialchars($a['BirthDate']); ?></td>
                            <td>
                                <a href="manage_authors.php?action=edit&id=<?php echo $a['AuthorID']; ?>">Edit</a>
                                <form action="manage_authors.php?action=delete&id=<?php echo $a['AuthorID']; ?>" method="post" style="display:inline;" onsubmit="return confirm('Are you sure?')">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($action == 'add' || $action == 'edit'): ?>
            <form action="manage_authors.php?action=<?php echo $action; ?><?php if ($id) echo '&id=' . $id; ?>" method="post">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo $author['Name'] ?? ''; ?>" required>

                <label for="bio">Bio:</label>
                <textarea id="bio" name="bio"><?php echo $author['Bio'] ?? ''; ?></textarea>

                <label for="birth_date">Birth Date:</label>
                <input type="date" id="birth_date" name="birth_date" value="<?php echo $author['BirthDate'] ?? ''; ?>">

                <button type="submit"><?php echo ucfirst($action); ?> Author</button>
            </form>
            <a href="manage_authors.php">Back to List</a>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2025 Book Store</p>
    </footer>
</body>
</html>
