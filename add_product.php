<?php
include 'database.php'; // Include your DB connection

// Check if form is submitted
if (isset($_POST['submit'])) {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $description = $_POST['description'];

    // Simple validation
    if (!empty($product_name) && !empty($price) && !empty($quantity) && !empty($description)) {
        $sql = "INSERT INTO products (product_name, price, quantity, description) 
                VALUES ('$product_name', '$price', '$quantity', '$description')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Product added successfully!'); window.location='index.php#coffee-section';</script>";
        } else {
            echo "<p>Error: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:red;'>Please fill in all fields.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Product - CyreCafé</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
  <h2 style="text-align:center; margin: 30px 0;">Add New Product</h2>

  <form class="edit-form" method="POST" action="">
    <label for="product_name">Product Name:</label>
    <input type="text" name="product_name" id="product_name" required>

    <label for="price">Price (₱):</label>
    <input type="number" name="price" id="price" step="0.01" required>

    <label for="quantity">Quantity:</label>
    <input type="number" name="quantity" id="quantity" required>

    <label for="description">Description:</label>
    <textarea name="description" id="description" rows="4" required></textarea>

    <button type="submit" name="submit">Add Product</button>
  </form>

</div>

</body>
</html>
