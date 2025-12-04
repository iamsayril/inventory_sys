<?php
include 'database.php';

if (!isset($_GET['order_id'])) {
    echo "Order ID not specified.";
    exit;
}

$order_id = intval($_GET['order_id']);

$stmtOrder = $conn->prepare("
    SELECT o.order_id, o.order_date, o.total_price, c.full_name, c.email, c.phone
    FROM orders o
    JOIN customers c ON o.customer_id = c.customer_id
    WHERE o.order_id = ?
");
$stmtOrder->bind_param("i", $order_id);
$stmtOrder->execute();
$orderResult = $stmtOrder->get_result();

if ($orderResult->num_rows === 0) {
    echo "Order not found.";
    exit;
}

$order = $orderResult->fetch_assoc();
$stmtOrder->close();

$stmtItems = $conn->prepare("
    SELECT oi.quantity, oi.price_each, p.product_name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
");
$stmtItems->bind_param("i", $order_id);
$stmtItems->execute();
$itemsResult = $stmtItems->get_result();
$stmtItems->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - CyreCafé</title>
    <link rel="stylesheet" href="customers.css">
</head>
<body>

<div class="customers-container" style="max-width: 800px;">
    <h2>Order Details</h2>

    <div class="form-box" style="padding:20px 25px;">
        <p><strong>Order ID:</strong> <?= $order['order_id'] ?></p>
        <p><strong>Customer:</strong> <?= htmlspecialchars($order['full_name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></p>
        <p><strong>Order Date:</strong> <?= $order['order_date'] ?></p>
    </div>

    <div class="table-box">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price Each (₱)</th>
                    <th>Subtotal (₱)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $counter = 1;
                while ($row = $itemsResult->fetch_assoc()):
                    $subtotal = $row['quantity'] * $row['price_each'];
                ?>
                <tr>
                    <td><?= $counter ?></td>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td><?= number_format($row['price_each'], 2) ?></td>
                    <td><?= number_format($subtotal, 2) ?></td>
                </tr>
                <?php
                    $counter++;
                endwhile;
                ?>
                <tr>
                    <td colspan="4" style="text-align:right; font-weight:bold;">Total Price:</td>
                    <td style="font-weight:bold; color:#26a69a;">₱<?= number_format($order['total_price'], 2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="text-align:center; margin-top:20px;">
        <a href="view_orders.php" class="edit-btn">Back to Orders</a>
    </div>
</div>

</body>
</html>
