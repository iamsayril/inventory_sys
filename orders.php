<?php
include 'database.php';
session_start();

if (isset($_POST['update_customer'])) {
    $order_id = intval($_POST['order_id']);
    $customer_id = intval($_POST['customer_id']);
    $stmt = $conn->prepare("UPDATE orders SET customer_id = ? WHERE order_id = ?");
    $stmt->bind_param("ii", $customer_id, $order_id);
    $stmt->execute();
    $stmt->close();
    // remember the last selected customer so add-to-cart can use it
    $_SESSION['active_customer'] = $customer_id;
}

if (isset($_POST['update_item_quantity'])) {
    $order_item_id = intval($_POST['order_item_id']);
    $new_quantity = intval($_POST['item_quantity']);
    $order_id = intval($_POST['order_id']);
    
    if ($new_quantity > 0) {
        $stmt = $conn->prepare("UPDATE order_items SET quantity = ? WHERE order_item_id = ?");
        $stmt->bind_param("ii", $new_quantity, $order_item_id);
        $stmt->execute();
        $stmt->close();
        
        $stmt = $conn->prepare("UPDATE orders SET total_price = (SELECT SUM(quantity * price_each) FROM order_items WHERE order_id = ?) WHERE order_id = ?");
        $stmt->bind_param("ii", $order_id, $order_id);
        $stmt->execute();
        $stmt->close();
    }
}

$sql = "SELECT o.order_id, o.customer_id, c.full_name, o.order_date, o.total_price
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        WHERE o.order_status = 'draft'
        ORDER BY o.order_id DESC";

$result = $conn->query($sql);
if (!$result) {
    die("Query Error: " . $conn->error);
}

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
    <title>Orders - CyreCafé</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="orders.css">
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
                <a href="orders.php" class="active">Orders</a>
                <a href="view_orders.php">Placed Orders</a>
                <a href="customers.php">Customers</a>
                <a href="manage_categories.php">Manage Categories</a>
            </div>
        </nav>

        <section class="orders-container">
            <div style="text-align: center; margin-bottom: 40px;">
                <h2 style="margin: 0; color: #3e2723; font-size: 32px; margin-bottom: 8px; font-weight: 700;">Manage Orders</h2>
                <p style="margin: 0; color: #999; font-size: 14px;">View and manage all customer orders</p>
            </div>

            <?php if ($result && $result->num_rows > 0): ?>
                <div class="table-wrapper">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Order Date</th>
                                <th>Ordered Coffee</th>
                                <th>Quantity</th>
                                <th>Total Price (₱)</th>
                                <th class="action-col">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><span class="order-id">#<?php echo $row['order_id']; ?></span></td>

                                    <!-- DROPDOWN FOR CUSTOMER -->
                                    <td>
                                        <form method="POST" class="customer-form">
                                            <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                            <select class="customer-dropdown" name="customer_id" onchange="this.form.submit()">
                                                <option value="<?php echo $row['customer_id']; ?>" selected><?php echo htmlspecialchars($row['full_name']); ?></option>
                                                <?php foreach ($customers as $cust_id => $cust_name): ?>
                                                    <?php if ($cust_id != $row['customer_id']): ?>
                                                        <option value="<?php echo $cust_id; ?>"><?php echo htmlspecialchars($cust_name); ?></option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="hidden" name="update_customer" value="1">
                                        </form>
                                    </td>

                                    <td><span class="order-date"><?php echo date('M d, Y', strtotime($row['order_date'])); ?></span></td>
                                    
                                    <!-- ORDERED COFFEE NAMES -->
                                    <td style="max-width: 300px;">
                                        <span style="color: #5d4037; font-size: 14px; word-wrap: break-word;">
                                            <?php 
                                            $coffee_sql = "SELECT p.product_name, oi.quantity 
                                                          FROM order_items oi 
                                                          JOIN products p ON oi.product_id = p.product_id 
                                                          WHERE oi.order_id = " . intval($row['order_id']);
                                            $coffee_result = $conn->query($coffee_sql);
                                            
                                            if ($coffee_result && $coffee_result->num_rows > 0) {
                                                $coffees = [];
                                                while ($coffee = $coffee_result->fetch_assoc()) {
                                                    $coffees[] = htmlspecialchars($coffee['product_name']);
                                                }
                                                echo implode(', ', $coffees);
                                            } else {
                                                echo 'No items';
                                            }
                                            ?>
                                        </span>
                                    </td>

                                    <!-- QUANTITY INPUT WITH ARROWS FOR EACH ITEM -->
                                    <td>
                                        <?php 
                                        $items_sql = "SELECT oi.order_item_id, p.product_name, oi.quantity 
                                                      FROM order_items oi 
                                                      JOIN products p ON oi.product_id = p.product_id 
                                                      WHERE oi.order_id = " . intval($row['order_id']);
                                        $items_result = $conn->query($items_sql);
                                        
                                        if ($items_result && $items_result->num_rows > 0) {
                                            while ($item = $items_result->fetch_assoc()): 
                                            ?>
                                                <form method="POST" class="quantity-form" style="margin-bottom: 8px;">
                                                    <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                                    <input type="hidden" name="order_item_id" value="<?php echo $item['order_item_id']; ?>">
                                                    <div style="display: flex; gap: 4px; align-items: center;">
                                                        <input type="number" name="item_quantity" value="<?php echo $item['quantity']; ?>" min="1" style="width: 60px; text-align: center; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                                        <button type="submit" name="update_item_quantity" style="padding: 8px 12px; background: #6d4c41; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 12px;">✓</button>
                                                    </div>
                                                </form>
                                            <?php 
                                            endwhile;
                                        }
                                        ?>
                                    </td>

                                    <td><strong style="color: #26a69a; font-size: 15px;">₱<?php echo number_format($row['total_price'], 2); ?></strong></td>

                                    <td class="action-cell">
                                        <a class="edit-btn" href="view_order_details.php?order_id=<?php echo $row['order_id']; ?>" title="View Details">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            <?php else: ?>
                <div style="text-align: center; padding: 60px 20px; background: #fff8f0; border-radius: 10px;">
                    <p style="color: #999; font-size: 16px; margin: 0;">📭 No orders found.</p>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <script>
        document.querySelectorAll('.customer-dropdown').forEach(dropdown => {
            dropdown.addEventListener('change', function() {
                this.closest('.customer-form').submit();
            });
        });
    </script>
</body>
</html>
