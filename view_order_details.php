<?php
include 'database.php';

if (!isset($_GET['order_id'])) {
    echo "Order ID not specified.";
    exit;
}

$order_id = intval($_GET['order_id']);

if (isset($_POST['update_customer'])) {
    $new_customer_id = intval($_POST['customer_id']);
    $stmt = $conn->prepare("UPDATE orders SET customer_id = ? WHERE order_id = ?");
    $stmt->bind_param("ii", $new_customer_id, $order_id);
    $stmt->execute();
    $stmt->close();
    header("Location: view_order_details.php?order_id=" . $order_id);
    exit;
}

if (isset($_POST['update_quantity'])) {
    $order_item_id = intval($_POST['order_item_id']);
    $new_quantity = intval($_POST['quantity']);
    
    if ($new_quantity > 0) {
        $stmt = $conn->prepare("SELECT price_each FROM order_items WHERE order_item_id = ?");
        $stmt->bind_param("i", $order_item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        $price_each = $item['price_each'];
        $stmt->close();
        
        $stmt = $conn->prepare("UPDATE order_items SET quantity = ? WHERE order_item_id = ?");
        $stmt->bind_param("ii", $new_quantity, $order_item_id);
        $stmt->execute();
        $stmt->close();
        
        $stmt = $conn->prepare("UPDATE orders SET total_price = (SELECT SUM(quantity * price_each) FROM order_items WHERE order_id = ?) WHERE order_id = ?");
        $stmt->bind_param("ii", $order_id, $order_id);
        $stmt->execute();
        $stmt->close();
    }
    
    header("Location: view_order_details.php?order_id=" . $order_id);
    exit;
}

$stmtOrder = $conn->prepare("
    SELECT o.order_id, o.customer_id, o.order_date, o.total_price, c.full_name, c.email, c.phone
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
    SELECT oi.order_item_id, oi.quantity, oi.price_each, p.product_name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
");
$stmtItems->bind_param("i", $order_id);
$stmtItems->execute();
$itemsResult = $stmtItems->get_result();
$stmtItems->close();

$customers_sql = "SELECT customer_id, full_name FROM customers ORDER BY full_name ASC";
$customers_result = $conn->query($customers_sql);
$customers = [];
if ($customers_result && $customers_result->num_rows > 0) {
    while ($customer = $customers_result->fetch_assoc()) {
        $customers[$customer['customer_id']] = $customer['full_name'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - CyreCafé</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="view_order_details.css">
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

    <section class="order-details-section">
        <div class="order-details-header">
            <h2>Order #<?= $order['order_id'] ?></h2>
            <p>Placed on <?= date('F d, Y - h:i A', strtotime($order['order_date'])) ?></p>
        </div>

        <div class="order-info-grid">
            <div class="info-box">
                <label>Order ID</label>
                <p><?= $order['order_id'] ?></p>
            </div>

            <div class="info-box">
                <label>Order Date</label>
                <p><?= date('F d, Y', strtotime($order['order_date'])) ?></p>
            </div>

            <div class="info-box">
                <label>Total Price</label>
                <p style="color: #26a69a; font-weight: bold; font-size: 16px;">₱<?= number_format($order['total_price'], 2) ?></p>
            </div>
        </div>

        <div class="customer-info-box">
            <label style="font-weight: 600; color: #6d4c41; display: block; margin-bottom: 10px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Change Customer</label>
            <form method="POST" class="customer-selector">
                <select name="customer_id" required>
                    <option value="<?= $order['customer_id'] ?>" selected><?= htmlspecialchars($order['full_name']) ?></option>
                    <?php foreach ($customers as $cust_id => $cust_name): ?>
                        <?php if ($cust_id != $order['customer_id']): ?>
                            <option value="<?= $cust_id ?>"><?= htmlspecialchars($cust_name) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="update_customer">Update</button>
            </form>

            <div class="customer-details">
                <p><strong>Name:</strong> <?= htmlspecialchars($order['full_name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></p>
            </div>
        </div>

        <div class="order-items-section">
            <h3>Order Items</h3>
            
            <?php if ($itemsResult->num_rows > 0): ?>
                <table class="items-table">
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
                            <td>
                                <form method="POST" class="quantity-input-row">
                                    <input type="hidden" name="order_item_id" value="<?= $row['order_item_id'] ?>">
                                    <input type="number" name="quantity" value="<?= $row['quantity'] ?>" min="1" required>
                                    <button type="submit" name="update_quantity">Update</button>
                                </form>
                            </td>
                            <td><?= number_format($row['price_each'], 2) ?></td>
                            <td><?= number_format($subtotal, 2) ?></td>
                        </tr>
                        <?php
                            $counter++;
                        endwhile;
                        ?>
                        <tr class="total-row">
                            <td colspan="4" style="text-align: right;">Total Price:</td>
                            <td class="total-amount">₱<?= number_format($order['total_price'], 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-items">No items in this order.</div>
            <?php endif; ?>
        </div>

        <div class="action-buttons">
            <a href="orders.php" class="btn-back">← Back to Orders</a>
            <a href="view_orders.php" class="btn-place-order">✓ Place Order</a>
        </div>
    </section>
</div>

</body>
</html>
