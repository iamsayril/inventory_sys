<?php
include 'database.php';

if (!isset($_POST['customer_id']) || empty($_POST['customer_id'])) {
    echo "<script>
        alert('Please select a customer first.');
        window.location.href = 'index.php';
    </script>";
    exit;
}

$customer_id = intval($_POST['customer_id']);

if (isset($_POST['product_id'], $_POST['price_each'])) {

    $product_id = intval($_POST['product_id']);
    $price_each = floatval($_POST['price_each']);
    $quantity = 1;

    $stmtCheck = $conn->prepare("SELECT customer_id FROM customers WHERE customer_id = ?");
    $stmtCheck->bind_param("i", $customer_id);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows === 0) {
        echo "<script>
            alert('Selected customer does not exist.');
            window.location.href = 'index.php';
        </script>";
        exit;
    }
    $stmtCheck->close();

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

    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT order_id FROM orders WHERE customer_id = ? AND DATE(order_date) = ?");
    $stmt->bind_param("is", $customer_id, $today);
    $stmt->execute();
    $stmt->bind_result($order_id);
    $stmt->fetch();
    $stmt->close();

    if (!$order_id) {
        $order_date = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO orders (customer_id, total_price, order_date) VALUES (?, 0, ?)");
        $stmt->bind_param("is", $customer_id, $order_date);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();
    }

    $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_each) VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("iiid", $order_id, $product_id, $quantity, $price_each);
    $stmt2->execute();
    $stmt2->close();

    $stmt3 = $conn->prepare("UPDATE orders 
                             SET total_price = (SELECT SUM(quantity * price_each) FROM order_items WHERE order_id = ?)
                             WHERE order_id = ?");
    $stmt3->bind_param("ii", $order_id, $order_id);
    $stmt3->execute();
    $stmt3->close();

    $stmtUpdateStock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
    $stmtUpdateStock->bind_param("ii", $quantity, $product_id);
    $stmtUpdateStock->execute();
    $stmtUpdateStock->close();

    echo "<script>
        alert('Product added to order!');
        window.location.href = 'index.php';
    </script>";
    exit;

} else {
    echo "Invalid request.";
}
?>
