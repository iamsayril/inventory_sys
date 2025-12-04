<?php
include 'database.php';

if (!isset($_GET['product_id'])) { echo "No product selected."; exit; }
$product_id = $_GET['product_id'];

$stmt = $conn->prepare("SELECT * FROM products WHERE product_id=?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) { echo "Product not found."; exit; }
$product = $result->fetch_assoc();

$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");

if (isset($_POST['update'])) {
    $name = $_POST['product_name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = intval($_POST['category_id']);

    $img_name = $product['prod_img'];
    if (isset($_FILES['prod_img']) && $_FILES['prod_img']['error']==0) {
        $target_dir = "uploads/";
        $tmp_name = $_FILES['prod_img']['tmp_name'];
        $filename = basename($_FILES['prod_img']['name']);
        if (move_uploaded_file($tmp_name, $target_dir.$filename)) { $img_name=$filename; }
    }

    $update_stmt = $conn->prepare("UPDATE products SET product_name=?, price=?, stock=?, prod_img=?, category_id=? WHERE product_id=?");
    $update_stmt->bind_param("sdisii", $name, $price, $stock, $img_name, $category_id, $product_id);

    if ($update_stmt->execute()) {
        echo "<script>alert('Product updated successfully!'); window.location.href='index.php';</script>";
        exit;
    } else {
        echo "<p style='color:red; text-align:center;'>Error updating product: ".$conn->error."</p>";
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
    <link rel="stylesheet" href="edit_product.css">
</head>
<body>

<div class="edit-form">
    <h2>Edit Product</h2>
    <form method="POST" enctype="multipart/form-data">
        <label for="product_name">Product Name</label>
        <input type="text" name="product_name" id="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>

        <label for="price">Price</label>
        <input type="number" step="0.01" name="price" id="price" value="<?php echo $product['price']; ?>" required>

        <label for="stock">Stock</label>
        <input type="number" name="stock" id="stock" value="<?php echo $product['stock']; ?>" required>

        <label for="category_id">Category</label>
        <select name="category_id" id="category_id" required>
            <option value="">-- Select Category --</option>
            <?php while($cat = $categories->fetch_assoc()): ?>
                <option value="<?php echo $cat['category_id']; ?>" <?php echo ($product['category_id']==$cat['category_id'])?'selected':''; ?>>
                    <?php echo htmlspecialchars($cat['category_name']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="prod_img">Product Image</label>
        <?php if(!empty($product['prod_img']) && file_exists('uploads/'.$product['prod_img'])): ?>
            <div style="margin-bottom:10px;">
                <img src="uploads/<?php echo htmlspecialchars($product['prod_img']); ?>" alt="Product Image" style="width:150px;height:150px;object-fit:cover;border-radius:10px;">
            </div>
        <?php endif; ?>
        <input type="file" name="prod_img" id="prod_img" accept="image/*">

        <button type="submit" name="update">Update Product</button>
    </form>
</div>

</body>
</html>
