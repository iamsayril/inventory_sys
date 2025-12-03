<?php
include 'database.php';

// Handle product deletion
if (isset($_GET['delete'])) {
    $product_id = intval($_GET['delete']);
    $sql_delete = "DELETE FROM products WHERE product_id = $product_id";

    if ($conn->query($sql_delete)) {
        header("Location: index.php");
        exit;
    } else {
        echo "Error deleting product: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CyreCafé</title>
  <link rel="stylesheet" href="style.css">
  <!-- Font Awesome CDN -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="container">

  <!-- NAVIGATION -->
  <nav class="nav">
    <div class="nav-logo">
      <img src="cup-of-coffee.png" alt="Coffee Icon" class="nav-logo-icon">
      <h1>CyreCafé</h1>
    </div>
    <div class="nav-links">
      <a href="#coffee-section">Coffee</a>
      <a href="#">Orders</a>
      <a href="#">Manage Categories</a>
      <a href="#about-section">About</a>
      <a href="#contact-section">Contact</a>
    </div>
  </nav>

  <!-- HERO SECTION -->
  <section class="hero">
    <div class="hero-intro">
      <h1>Welcome to CyreCafé – savor the aroma of freshly brewed coffee in every cup.</h1>
      <button onclick="document.getElementById('coffee-section').scrollIntoView({ behavior: 'smooth' });">
        Buy your Coffee!
      </button>
    </div>
    <div class="hero-image">
      <img src="latte-coffee-cup.jpg" alt="Latte Coffee Cup">
    </div>
  </section>

  <!-- PRODUCTS SECTION -->
  <section class="products" id="coffee-section">

    <!-- Add Product Button -->
    <div style="width:100%; display:flex; justify-content:flex-end; margin-bottom:20px;">
      <a href="add_product.php" class="add-product-btn">Add Product</a>
    </div>

    <?php
    $sql = "SELECT * FROM products ORDER BY product_id DESC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {

            echo "<div class='product'>";

            // PRODUCT IMAGE
            if (!empty($row['prod_img']) && file_exists('uploads/' . $row['prod_img'])) {
                echo "<img src='uploads/" . htmlspecialchars($row['prod_img']) . "' 
                      alt='Product Image' 
                      style='width:100%; height:200px; object-fit:cover; border-radius:10px; margin-bottom:12px;'>";
            } else {
                echo "<div style='width:100%; height:200px; background:#ccc; border-radius:10px; 
                      display:flex; align-items:center; justify-content:center; color:#555;'>No Image</div>";
            }

            // PRODUCT NAME
            echo "<h4>" . htmlspecialchars($row['product_name']) . "</h4>";

            // PRODUCT PRICE
            echo "<p>₱" . htmlspecialchars($row['price']) . "</p>";

            // PRODUCT STOCK
            echo "<p>Stock: " . htmlspecialchars($row['stock']) . "</p>";

            // PRODUCT CATEGORY
            echo "<p>Category: " . (!empty($row['category']) ? htmlspecialchars($row['category']) : 'None') . "</p>";

            // ACTION BUTTONS
            echo "
                <a href='edit_product.php?product_id=" . $row['product_id'] . "' class='edit-btn'>Edit</a>
                <a href='index.php?delete=" . $row['product_id'] . "' class='delete-btn' 
                   onclick='return confirm(\"Are you sure you want to delete this product?\");'>Delete</a>
            ";

            echo "</div>";
        }

    } else {
        echo "<p>No products found.</p>";
    }
    ?>

  </section>

  <!-- ABOUT SECTION -->
  <section class="about" id="about-section">
    <div class="about-box">
      <h3>Our Mission</h3>
      <p>To provide the freshest coffee and high-quality inventory management for our café, ensuring every cup is perfect.</p>
    </div>
    <div class="about-box">
      <h3>Inventory System</h3>
      <p>This system allows staff to easily track products, manage stock, and update prices efficiently in real time.</p>
    </div>
    <div class="about-box">
      <h3>Why CyreCafé?</h3>
      <p>We combine the love of coffee with smart inventory solutions to enhance your café’s operational workflow.</p>
    </div>
  </section>

  <!-- CONTACT SECTION -->
  <section class="contact" id="contact-section">
    <h3>Contact Us</h3>
    <div class="contact-info">
      <p><i class="fab fa-facebook-square"></i>
        <a href="https://www.facebook.com/acadsphere.quinio" target="_blank">Mark Cyrell Quinio</a></p>

      <p><i class="fas fa-envelope"></i>
        <a href="mailto:markcyrellquinio@gmail.com">markcyrellquinio@gmail.com</a></p>
    </div>
  </section>

</div>

</body>
</html>
