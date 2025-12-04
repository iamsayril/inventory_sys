<?php
include 'database.php';

$errors = [];
if (isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($category_name === '') {
        $errors[] = "Category name is required.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO categories (category_name, description, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $category_name, $description);
        if ($stmt->execute()) {
            header("Location: manage_categories.php");
            exit;
        } else {
            $errors[] = "Error adding category: " . $stmt->error;
        }
        $stmt->close();
    }
}

if (isset($_GET['delete'])) {
    $category_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_categories.php");
    exit;
}

$result = $conn->query("SELECT * FROM categories ORDER BY category_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - CyreCafé</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="manage_categories.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body>

    <div class="container">
        <nav class="nav">
            <div class="nav-logo">
                <img src="uploads/cup-of-coffee.png" alt="Coffee Icon" class="nav-logo-icon">
                <h1>CyreCafé</h1>
            </div>
            <div class="nav-links">
                <a href="index.php">Coffee</a>
                <a href="orders.php">Orders</a>
                <a href="view_orders.php">Placed Orders</a>
                <a href="customers.php">Customers</a>
                <a href="manage_categories.php" class="active">Manage Categories</a>
            </div>
        </nav>

        <div class="category-container">
            <h2>Manage Categories</h2>

            <?php if (!empty($errors)): ?>
                <div style="background:#ffe6e6; color:#900; padding:12px; border-radius:8px; margin-bottom:15px;">
                    <ul>
                        <?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="category_name" placeholder="Category Name" required>
                <textarea name="description" placeholder="Description (optional)" rows="3"></textarea>
                <button type="submit" name="add_category">Add Category</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Description</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['category_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                        <td>
                            <a href="manage_categories.php?delete=<?php echo $row['category_id']; ?>" 
                               class="delete-btn" 
                               onclick="return confirm('Are you sure you want to delete this category?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    </body>
    </html>
