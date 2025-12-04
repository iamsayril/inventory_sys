<?php
include 'database.php';

if (isset($_GET['delete'])) {
    $order_id = intval($_GET['delete']);

    $stmtGet = $conn->prepare("SELECT customer_id FROM orders WHERE order_id = ?");
    $stmtGet->bind_param("i", $order_id);
    $stmtGet->execute();
    $stmtGet->bind_result($customer_id);
    $stmtGet->fetch();
    $stmtGet->close();

    $stmt1 = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
    $stmt1->bind_param("i", $order_id);
    $stmt1->execute();
    $stmt1->close();

    $stmt2 = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
    $stmt2->bind_param("i", $order_id);
    $stmt2->execute();
    $stmt2->close();

    $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = ?");
    $stmtCheck->bind_param("i", $customer_id);
    $stmtCheck->execute();
    $stmtCheck->bind_result($order_count);
    $stmtCheck->fetch();
    $stmtCheck->close();

    if ($order_count == 0) {
        $stmtDelCust = $conn->prepare("DELETE FROM customers WHERE customer_id = ?");
        $stmtDelCust->bind_param("i", $customer_id);
        $stmtDelCust->execute();
        $stmtDelCust->close();
    }

    header("Location: view_orders.php");
    exit;
}

$sql = "
    SELECT o.order_id, o.customer_id, c.full_name, o.order_date, o.total_price, 
           COUNT(oi.order_item_id) AS total_items
    FROM orders o
    JOIN customers c ON o.customer_id = c.customer_id
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    GROUP BY o.order_id
    ORDER BY o.order_id DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - CyreCafé</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="orders-container">
        <h2>All Orders</h2>

        <?php if ($result && $result->num_rows > 0): ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Order Date</th>
                        <th>Total Items</th>
                        <th>Total Price (₱)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><?php echo $row['order_date']; ?></td>
                            <td><?php echo $row['total_items']; ?></td>
                            <td><?php echo number_format($row['total_price'], 2); ?></td>
                            <td>
                                <a class="view-order-btn" href="view_order_details.php?order_id=<?php echo $row['order_id']; ?>">View Details</a>
                                <a class="delete-order-btn" href="view_orders.php?delete=<?php echo $row['order_id']; ?>" 
                                   onclick="return confirm('Are you sure you want to delete this order and its customer?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-orders">No orders found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
