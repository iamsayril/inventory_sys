<?php
include 'database.php';

// Get the customer ID from POST (dropdown)
if (isset($_POST['customer_id']) && !empty($_POST['customer_id'])) {
    $customer_id = intval($_POST['customer_id']);
} else {
    // Fallback: TEMP CUSTOMER ID (for testing)
    $customer_id = 1; 
}

// Ensure customer exists
$stmtCheck = $conn->prepare("SELECT customer_id FROM customers WHERE customer_id = ?");
$stmtCheck->bind_param("i", $customer_id);
$stmtCheck->execute();
$stmtCheck->store_result();

if ($stmtCheck->num_rows === 0) {
    // Insert temp customer if it doesn’t exist
    $stmtInsert = $conn->prepare("INSERT INTO customers (full_name, email, phone, created_at) VALUES (?, ?, ?, ?)");
    $name = "Temp User";
    $email = "tempuser@example.com";
    $phone = "0000000000";
    $created_at = date('Y-m-d H:i:s');
    $stmtInsert->bind_param("ssss", $name, $email, $phone, $created_at);
    $stmtInsert->execute();
    $customer_id = $stmtInsert->insert_id;
    $stmtInsert->close();
}
$stmtCheck->close();

if (isset($_POST['product_id'], $_POST['price_each'])) {
    $product_id = intval($_POST['product_id']);
    $price_each = floatval($_POST['price_each']);
    $quantity = 1; // default 1

    // 1. Check product stock
    $stmtStock = $conn->prepare("SELECT stock FROM products WHERE product_id = ?");
    $stmtStock->bind_param("i", $product_id);
    $stmtStock->execute();
    $stmtStock->bind_result($stock);
    $stmtStock->fetch();
    $stmtStock->close();

    if ($stock < 1) {
        echo "<script>
            alert('Sorry, this product is out of stock!');
            window.location.href = 'index.php';
        </script>";
        exit;
    }

    // 2. Check if order exists for today for this customer
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT order_id FROM orders WHERE customer_id = ? AND DATE(order_date) = ?");
    $stmt->bind_param("is", $customer_id, $today);
    $stmt->execute();
    $stmt->bind_result($order_id);
    $stmt->fetch();
    $stmt->close();

    // 3. Create new order if not exists
    if (!$order_id) {
        $order_date = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO orders (customer_id, total_price, order_date) VALUES (?, 0, ?)");
        $stmt->bind_param("is", $customer_id, $order_date);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();
    }

    // 4. Insert into order_items
    $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_each) VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("iiid", $order_id, $product_id, $quantity, $price_each);
    $stmt2->execute();
    $stmt2->close();

    // 5. Update total_price in orders
    $stmt3 = $conn->prepare("UPDATE orders o 
                             SET o.total_price = (SELECT SUM(quantity * price_each) FROM order_items WHERE order_id = ?) 
                             WHERE o.order_id = ?");
    $stmt3->bind_param("ii", $order_id, $order_id);
    $stmt3->execute();
    $stmt3->close();

    // 6. Reduce product stock
    $stmtStockUpdate = $conn->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
    $stmtStockUpdate->bind_param("ii", $quantity, $product_id);
    $stmtStockUpdate->execute();
    $stmtStockUpdate->close();

    echo "<script>
        alert('Product added to order for the selected customer!');
        window.location.href = 'view_orders.php';
    </script>";
    exit;
} else {
    echo "Invalid request.";
}
?>
