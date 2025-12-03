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

    // IMAGE UPLOAD
    $img_name = $product['prod_img']; // default to existing image
    if (isset($_FILES['prod_img']) && $_FILES['prod_img']['error'] == 0) {
        $target_dir = "uploads/";
        $tmp_name = $_FILES['prod_img']['tmp_name'];
        $filename = basename($_FILES['prod_img']['name']);
        $target_file = $target_dir . $filename;

        // Move uploaded file
        if (move_uploaded_file($tmp_name, $target_file)) {
            $img_name = $filename;
        } else {
            echo "<p style='color:red;'>Failed to upload image.</p>";
        }
    }

    // Update query
    $update_sql = "UPDATE products SET product_name=?, price=?, quantity=?, prod_img=? WHERE product_id=?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sdssi", $name, $price, $quantity, $img_name, $product_id);

    if ($update_stmt->execute()) {
        echo "<p style='color:green; text-align:center;'>Product updated successfully! <a href='index.php'>Go Back</a></p>";
        $product['product_name'] = $name;
        $product['price'] = $price;
        $product['quantity'] = $quantity;
        $product['prod_img'] = $img_name;
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
    <form method="POST" enctype="multipart/form-data">
        <label for="product_name">Product Name</label>
        <input type="text" name="product_name" id="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>

        <label for="price">Price</label>
        <input type="number" step="0.01" name="price" id="price" value="<?php echo $product['price']; ?>" required>

        <label for="quantity">Quantity</label>
        <input type="number" name="quantity" id="quantity" value="<?php echo $product['quantity']; ?>" required>

        <label for="prod_img">Product Image</label>
        <?php if (!empty($product['prod_img']) && file_exists('uploads/' . $product['prod_img'])): ?>
            <div style="margin-bottom:10px;">
                <img src="uploads/<?php echo htmlspecialchars($product['prod_img']); ?>" alt="Product Image" style="width:150px; height:150px; object-fit:cover; border-radius:10px;">
            </div>
        <?php endif; ?>
        <input type="file" name="prod_img" id="prod_img" accept="image/*">

        <button type="submit" name="update">Update Product</button>
    </form>
</div>

</body>
</html>
