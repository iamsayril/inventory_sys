<?php
include 'database.php';

if (isset($_POST['add_customer'])) {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("INSERT INTO customers (full_name, email, phone, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $name, $email, $phone);
    $stmt->execute();
    $stmt->close();

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

    header("Location: customers.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM customers WHERE customer_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

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
<link rel="stylesheet" href="customers.css">
</head>
<body>

<div class="customers-container">

    <h2>Customer Management</h2>

    <div class="form-box">
        <?php if ($edit_customer): ?>
            <h3>Edit Customer</h3>
            <form method="POST">
                <input type="hidden" name="customer_id" value="<?= $edit_customer['customer_id'] ?>">
                <input type="text" name="full_name" value="<?= htmlspecialchars($edit_customer['full_name']) ?>" required>
                <input type="email" name="email" value="<?= htmlspecialchars($edit_customer['email']) ?>" required>
                <input type="text" name="phone" value="<?= htmlspecialchars($edit_customer['phone']) ?>" required>
                <button type="submit" name="edit_customer">Update Customer</button>
                <a href="customers.php" class="cancel-btn">Cancel</a>
            </form>
        <?php else: ?>
            <h3>Add New Customer</h3>
            <form method="POST">
                <input type="text" name="full_name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="text" name="phone" placeholder="Phone Number" required>
                <button type="submit" name="add_customer">Add Customer</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="table-box">
        <h3>Existing Customers</h3>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Created At</th>
                    <th>Action</th>
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
                        <a href="customers.php?delete=<?= $row['customer_id'] ?>" 
                           class="delete-btn" 
                           onclick="return confirm('Delete this customer?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

    </div>
</div>

</body>
</html>
