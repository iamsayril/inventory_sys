<?php
session_start();
include 'database.php';

// Initialize cart if empty
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle remove item
if (isset($_POST['remove'])) {
    $prod_id = intval($_POST['remove']);
    unset($_SESSION['cart'][$prod_id]);
    header("Location: view_cart.php");
    exit;
}

// Handle update quantities
if (isset($_POST['update_qty'])) {
    foreach ($_POST['qty'] as $prod_id => $qty) {
        $qty = intval($qty);
        if ($qty <= 0) {
            unset($_SESSION['cart'][$prod_id]);
        } else {
            $_SESSION['cart'][$prod_id] = $qty;
        }
    }
    header("Location: view_cart.php");
    exit;
}

// Handle checkout
if (isset($_POST['checkout']) && !empty($_SESSION['cart'])) {
    $customer_id = 1; // default, or you can select customer dynamically
    $total_price = 0;

    // Calculate total price
    foreach ($_SESSION['cart'] as $prod_id => $qty) {
        $res = $conn->query("SELECT price FROM products WHERE product_id=$prod_id");
        if ($res && $row = $res->fetch_assoc()) {
            $total_price += $row['price'] * $qty;
        }
    }

    // Insert into orders
    $stmt = $conn->prepare("INSERT INTO orders (customer_id, total_price, order_date) VALUES (?, ?, NOW())");
    $stmt->bind_param("id", $customer_id, $total_price);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // Insert into order_items
    foreach ($_SESSION['cart'] as $prod_id => $qty) {
        $res = $conn->query("SELECT price FROM products WHERE product_id=$prod_id");
        if ($res && $row = $res->fetch_assoc()) {
            $price_each = $row['price'];
            $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_each) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("iiid", $order_id, $prod_id, $qty, $price_each);
            $stmt2->execute();
        }
    }

    // Clear cart
    $_SESSION['cart'] = [];
    echo "<script>alert('Order placed successfully!'); window.location.href='index.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cart - CyreCafé</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="edit-form">
    <h2>Your Cart</h2>

    <?php if (empty($_SESSION['cart'])): ?>
        <p style="text-align:center;">Your cart is empty.</p>
        <div style="text-align:center; margin-top:20px;">
            <a href="index.php#coffee-section" class="add-product-btn">Back to Products</a>
        </div>
    <?php else: ?>
        <form method="POST">
            <table style="width:100%; border-collapse:collapse; margin-bottom:20px;">
                <tr style="background:#6d4c41; color:#fff8f0;">
                    <th style="padding:8px;">Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
                <?php
                $grand_total = 0;
                foreach ($_SESSION['cart'] as $prod_id => $qty):
                    $res = $conn->query("SELECT product_name, price FROM products WHERE product_id=$prod_id");
                    if ($res && $row = $res->fetch_assoc()):
                        $subtotal = $row['price'] * $qty;
                        $grand_total += $subtotal;
                ?>
                <tr>
                    <td style="padding:8px;"><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td>₱<?php echo number_format($row['price'],2); ?></td>
                    <td>
                        <input type="number" name="qty[<?php echo $prod_id; ?>]" value="<?php echo $qty; ?>" min="1" style="width:60px;">
                    </td>
                    <td>₱<?php echo number_format($subtotal,2); ?></td>
                    <td>
                        <button type="submit" name="remove" value="<?php echo $prod_id; ?>" style="background:#d32f2f; color:#fff; border:none; padding:6px 10px; border-radius:6px;">Remove</button>
                    </td>
                </tr>
                <?php endif; endforeach; ?>
                <tr>
                    <td colspan="3" style="text-align:right; font-weight:bold;">Total:</td>
                    <td colspan="2" style="font-weight:bold;">₱<?php echo number_format($grand_total,2); ?></td>
                </tr>
            </table>

            <div style="display:flex; justify-content:space-between;">
                <button type="submit" name="update_qty" class="add-cart-btn">Update Cart</button>
                <button type="submit" name="checkout" class="add-product-btn">Checkout</button>
            </div>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
