<?php
include 'database.php';

if (isset($_GET['delete'])) {
    $order_id = intval($_GET['delete']);
    
    try {
        $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->close();
        
        $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->close();
        
        header("Location: view_orders.php");
        exit;
    } catch (Exception $e) {
        $error_message = "Error deleting order: " . $e->getMessage();
    }
}

$result = $conn->query("
    SELECT o.order_id, o.customer_id, c.full_name, o.order_date, o.total_price
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.customer_id
    WHERE o.order_status = 'placed'
    ORDER BY o.order_date DESC
");

function get_order_items($conn, $order_id) {
    $sql = "SELECT p.product_name, oi.quantity FROM order_items oi 
            JOIN products p ON oi.product_id = p.product_id 
            WHERE oi.order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $items = [];
    while ($r = $res->fetch_assoc()) {
        $items[] = $r;
    }
    $stmt->close();
    return $items;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placed Orders - CyreCafé</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="view_orders.css">
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
            <a href="view_orders.php" class="active">Placed Orders</a>
            <a href="customers.php">Customers</a>
            <a href="manage_categories.php">Manage Categories</a>
        </div>
    </nav>

    <div class="orders-list-container">
        <h2>Placed Orders</h2>
        <p class="subtitle">Orders placed by customers — review details, adjust quantities, or remove orders below.</p>

        <?php if ($result && $result->num_rows > 0): ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Placed Date</th>
                        <th>Items</th>
                        <th>Total (₱)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#<?php echo $row['order_id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($row['full_name'] ?? 'Unknown'); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($row['order_date'])); ?></td>
                            <td>
                                <?php
                                $items = get_order_items($conn, $row['order_id']);
                                if (count($items) > 0) {
                                    foreach ($items as $it) {
                                        echo htmlspecialchars($it['product_name']) . ' ×' . intval($it['quantity']) . '<br>';
                                    }
                                } else {
                                    echo '<span class="no-items">No items</span>';
                                }
                                ?>
                            </td>
                            <td><strong style="color:#26a69a;">₱<?php echo number_format($row['total_price'], 2); ?></strong></td>
                            <td class="action-cell">
                                <a href="view_order_details.php?order_id=<?php echo $row['order_id']; ?>" class="action-btn action-btn-view">View</a>
                                <a href="view_orders.php?delete=<?php echo $row['order_id']; ?>" class="action-btn action-btn-delete" onclick="return confirm('Are you sure you want to delete order #<?php echo $row['order_id']; ?>?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-orders-state">
                <p>No placed orders yet.</p>
                <p><a href="index.php">Create an order from the Coffee page.</a></p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
