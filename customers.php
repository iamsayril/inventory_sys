<?php
include 'database.php';
session_start();

function delete_customer_and_related($conn, $customer_id) {
    try {
        $conn->begin_transaction();

        $stmt = $conn->prepare("DELETE order_items FROM order_items JOIN orders ON order_items.order_id = orders.order_id WHERE orders.customer_id = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM orders WHERE customer_id = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM customers WHERE customer_id = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        return ['ok' => true];
    } catch (Throwable $e) {
        if ($conn->in_transaction) {
            $conn->rollback();
        }
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}

if (isset($_POST['add_customer'])) {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("INSERT INTO customers (full_name, email, phone, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $name, $email, $phone);
    $stmt->execute();
    $stmt->close();

    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Customer added successfully.'];
    header("Location: customers.php");
    exit;
}

if (isset($_POST['edit_customer'])) {
    $id = intval($_POST['customer_id']);
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("UPDATE customers SET full_name=?, email=?, phone=? WHERE customer_id=?");
    $stmt->bind_param("sssi", $name, $email, $phone, $id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Customer updated successfully.'];
    header("Location: customers.php");
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'delete_customer') {
    $id = intval($_POST['customer_id']);

    $res = delete_customer_and_related($conn, $id);
    if ($res['ok']) {
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Customer and related orders deleted successfully.'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Delete failed: ' . $res['error']];
    }
    header("Location: customers.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $res = delete_customer_and_related($conn, $id);
    if ($res['ok']) {
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Customer and related orders deleted successfully.'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Delete failed: ' . $res['error']];
    }
    header("Location: customers.php");
    exit;
}

$result = $conn->query("SELECT * FROM customers ORDER BY customer_id DESC");

$edit_customer = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM customers WHERE customer_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_customer = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - CyreCafé</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="customers.css">
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
            <a href="customers.php" class="active">Customers</a>
            <a href="manage_categories.php">Manage Categories</a>
        </div>
    </nav>

    <div class="customers-container">
    <h2>Manage Customers</h2>

    <?php if (isset($_SESSION['flash'])): $f = $_SESSION['flash']; unset($_SESSION['flash']); ?>
        <div class="flash <?= $f['type'] ?>"><?= htmlspecialchars($f['message']) ?></div>
    <?php endif; ?>

    <?php if ($edit_customer): ?>
    <form method="POST">
        <h3>Edit Customer</h3>
        <input type="hidden" name="customer_id" value="<?= $edit_customer['customer_id'] ?>">
        <input type="text" name="full_name" placeholder="Full Name" required value="<?= htmlspecialchars($edit_customer['full_name']) ?>">
        <input type="email" name="email" placeholder="Email Address" required value="<?= htmlspecialchars($edit_customer['email']) ?>">
        <input type="text" name="phone" placeholder="Phone Number" required value="<?= htmlspecialchars($edit_customer['phone']) ?>">
        <button type="submit" name="edit_customer">Save Changes</button>
        <a href="customers.php" class="cancel-btn">Cancel</a>
    </form>
    <?php else: ?>
    <form method="POST">
        <h3>Add Customer</h3>
        <input type="text" name="full_name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="text" name="phone" placeholder="Phone Number" required>
        <button type="submit" name="add_customer">Add Customer</button>
    </form>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['customer_id'] ?></td>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <a href="customers.php?edit=<?= $row['customer_id'] ?>" class="edit-btn">Edit</a>
                    <form method="POST" style="display:inline-block;margin:0;padding:0;" onsubmit="return confirm('Are you sure you want to delete this customer?');">
                        <input type="hidden" name="action" value="delete_customer">
                        <input type="hidden" name="customer_id" value="<?= $row['customer_id'] ?>">
                        <button type="submit" class="delete-btn">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</div>

</body>
</html>
