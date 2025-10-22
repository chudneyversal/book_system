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
        $stmt = $pdo->prepare("INSERT INTO Categories (CategoryName) VALUES (?)");
        try {
            $stmt->execute([$_POST['category_name']]);
        } catch (PDOException $e) {
            $error = "Error adding category: " . $e->getMessage();
        }
    } elseif ($action == 'edit' && $id) {
        $stmt = $pdo->prepare("UPDATE Categories SET CategoryName=? WHERE CategoryID=?");
        try {
            $stmt->execute([$_POST['category_name'], $id]);
        } catch (PDOException $e) {
            $error = "Error updating category: " . $e->getMessage();
        }
    } elseif ($action == 'delete' && $id) {
        // Check if category has books
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM Books WHERE CategoryID=?");
        $stmt_check->execute([$id]);
        $count = $stmt_check->fetchColumn();
        if ($count > 0) {
            $error = "Cannot delete category because there are books associated with this category. Please delete or reassign the books first.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM Categories WHERE CategoryID=?");
            try {
                $stmt->execute([$id]);
            } catch (PDOException $e) {
                $error = "Error deleting category: " . $e->getMessage();
            }
        }
    }
    if (!$error) {
        header('Location: manage_categories.php');
        exit;
    }
}

$categories = $pdo->query("SELECT * FROM Categories")->fetchAll(PDO::FETCH_ASSOC);

$category = null;
if ($action == 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM Categories WHERE CategoryID=?");
    $stmt->execute([$id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Manage Categories</h1>
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
            <table>
                <thead>
                    <tr>
                        <th>Category Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $c): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($c['CategoryName']); ?></td>
                            <td>
                                <button onclick="window.location.href='manage_categories.php?action=edit&id=<?php echo $c['CategoryID']; ?>'">Edit</button>
                                <form action="manage_categories.php?action=delete&id=<?php echo $c['CategoryID']; ?>" method="post" style="display:inline;" onsubmit="return confirm('Are you sure?')">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="manage-buttons" style="margin-top: 20px;">
                <button onclick="window.location.href='manage_categories.php?action=add'">Add New Category</button>
            </div>
        <?php elseif ($action == 'add' || $action == 'edit'): ?>
            <form action="manage_categories.php?action=<?php echo $action; ?><?php if ($id) echo '&id=' . $id; ?>" method="post">
                <label for="category_name">Category Name:</label>
                <input type="text" id="category_name" name="category_name" value="<?php echo $category['CategoryName'] ?? ''; ?>" required>

                <button type="submit"><?php echo ucfirst($action); ?> Category</button>
            </form>
            <div class="manage-buttons">
                <button onclick="window.location.href='manage_categories.php'">Back to List</button>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2025 Book Store</p>
    </footer>
</body>
</html>
