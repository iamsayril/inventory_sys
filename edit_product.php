<?php
include 'database.php';

if (!isset($_GET['product_id'])) {
    echo "No product selected.";
    exit;
}

$product_id = $_GET['product_id'];

// Fetch product info
$sql = "SELECT * FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Product not found.";
    exit;
}

$product = $result->fetch_assoc();

// Handle form submission
if (isset($_POST['update'])) {
    $name = $_POST['product_name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $description = $_POST['description'];

    $update_sql = "UPDATE products SET product_name=?, price=?, quantity=?, description=? WHERE product_id=?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sdisi", $name, $price, $quantity, $description, $product_id);

    if ($update_stmt->execute()) {
        echo "<p style='color:green; text-align:center;'>Product updated successfully! <a href='index.php'>Go Back</a></p>";
        // Update local product array for form values
        $product['product_name'] = $name;
        $product['price'] = $price;
        $product['quantity'] = $quantity;
        $product['description'] = $description;
    } else {
        echo "<p style='color:red; text-align:center;'>Error updating product: " . $conn->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - CyreCafé</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="edit-form">
    <h2>Edit Product</h2>
    <form method="POST">
        <label for="product_name">Product Name</label>
        <input type="text" name="product_name" id="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>

        <label for="price">Price</label>
        <input type="number" step="0.01" name="price" id="price" value="<?php echo $product['price']; ?>" required>

        <label for="quantity">Quantity</label>
        <input type="number" name="quantity" id="quantity" value="<?php echo $product['quantity']; ?>" required>

        <label for="description">Description</label>
        <textarea name="description" id="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>

        <button type="submit" name="update">Update Product</button>
    </form>
</div>

</body>
</html>
