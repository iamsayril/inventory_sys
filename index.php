<?php
session_start();
include 'database.php';

if (isset($_GET['delete'])) {
    $product_id = intval($_GET['delete']);
    
    try {
        $stmt1 = $conn->prepare("DELETE FROM order_items WHERE product_id = ?");
        $stmt1->bind_param("i", $product_id);
        $stmt1->execute();
        $stmt1->close();
        
        $stmt2 = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt2->bind_param("i", $product_id);
        $stmt2->execute();
        $stmt2->close();
        
        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        echo "Error deleting product: " . $e->getMessage();
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
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="container">

  <nav class="nav">
    <div class="nav-logo">
      <img src="uploads/cup-of-coffee.png" alt="Coffee Icon" class="nav-logo-icon">
      <h1>CyreCafé</h1>
    </div>
    <div class="nav-links">
      <a href="#coffee-section">Coffee</a>
      <a href="orders.php">Orders</a>
      <a href="customers.php">Customers</a>
      <a href="manage_categories.php">Manage Categories</a>
      <a href="#about-section">About</a>
      <a href="#contact-section">Contact</a>
    </div>
  </nav>
  
  

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

  <section class="products" id="coffee-section">

    <div class="products-header-section">
      <h2 class="products-title">Our Coffee</h2>
      <a href="add_product.php" class="add-product-btn">Add Product</a>
    </div>

    <?php
    $sql = "SELECT p.*, c.category_name 
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            ORDER BY p.product_id DESC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

            echo "<div class='product'>";

            if (!empty($row['prod_img']) && file_exists('uploads/' . $row['prod_img'])) {
                echo "<img src='uploads/" . htmlspecialchars($row['prod_img']) . "' 
                      alt='Product Image'>";
            } else {
                echo "<div style='width:100%; height:200px; background:#e8d7d0; border-radius:12px 12px 0 0; 
                      display:flex; align-items:center; justify-content:center; color:#999; font-style: italic;'>No Image</div>";
            }

            echo "<div class='product-info'>";
            echo "<h4>" . htmlspecialchars($row['product_name']) . "</h4>";
            
            echo "<div class='product-meta'>";
            echo "<span class='product-price'>₱" . htmlspecialchars($row['price']) . "</span>";
            echo "<span class='product-category'>" . (!empty($row['category_name']) ? htmlspecialchars($row['category_name']) : 'Uncategorized') . "</span>";
            echo "</div>";

            $stock = intval($row['stock']);
            if ($stock > 10) {
                $stock_class = 'stock-in';
                $stock_text = '✓ In Stock';
            } elseif ($stock > 0) {
                $stock_class = 'stock-low';
                $stock_text = '⚠ Low Stock (' . $stock . ')';
            } else {
                $stock_class = 'stock-out';
                $stock_text = '✕ Out of Stock';
            }
            echo "<span class='stock-badge $stock_class'>$stock_text</span>";

            echo "</div>";

            echo "<div class='product-actions'>";
            
            $is_out_of_stock = $stock <= 0;
            echo "
              <form method='POST' action='add_to_order.php' style='display:inline-block; width:100%;'>
                <input type='hidden' name='product_id' value='" . $row['product_id'] . "'>
                <input type='hidden' name='price_each' value='" . $row['price'] . "'>
                <button type='submit' class='add-cart-btn' title='Add to Cart' " . ($is_out_of_stock ? "disabled style='opacity:0.6; cursor:not-allowed;'" : "") . ">
                  Add to Cart
                </button>
              </form>
            ";
            echo "
                <a href='edit_product.php?product_id=" . $row['product_id'] . "' class='edit-btn' title='Edit Product'>Edit</a>
                <a href='index.php?delete=" . $row['product_id'] . "' class='delete-btn' onclick=\"return confirm('Are you sure you want to delete this product?');\" title='Delete Product'>Delete</a>
            ";

            echo "</div>";
            echo "</div>";
        }
    } else {
        echo "<p style='grid-column: 1/-1; text-align: center; color: #999; padding: 40px;'>No products found. Create your first product to get started!</p>";
    }
    ?>

  </section>


  <section class="about" id="about-section">
    <div class="about-header">
      <h2>About CyreCafé</h2>
      <p class="about-subtitle">Excellence in Coffee & Inventory Management</p>
    </div>
    
    <div class="about-container">
      <div class="about-box">
        <div class="about-icon">
          <i class="fas fa-bullseye"></i>
        </div>
        <h3>Our Mission</h3>
        <p>To deliver premium quality coffee paired with intelligent inventory management, ensuring operational excellence and customer satisfaction at every touchpoint.</p>
      </div>
      
      <div class="about-box">
        <div class="about-icon">
          <i class="fas fa-cogs"></i>
        </div>
        <h3>Smart Inventory System</h3>
        <p>Our integrated system empowers staff to track products in real-time, manage stock levels effortlessly, and optimize pricing strategies for maximum efficiency and profitability.</p>
      </div>
      
      <div class="about-box">
        <div class="about-icon">
          <i class="fas fa-heart"></i>
        </div>
        <h3>Why Choose Us</h3>
        <p>We blend passion for exceptional coffee with cutting-edge technology, transforming café operations into a seamless, data-driven experience that delights both staff and customers.</p>
      </div>
    </div>

    <div class="about-features">
      <h3 style="text-align: center; margin-bottom: 30px; color: #3e2723; font-size: 24px; font-weight: 700;">Key Features</h3>
      <div class="features-grid">
        <div class="feature-item">
          <i class="fas fa-check-circle"></i>
          <span>Real-time Inventory Tracking</span>
        </div>
        <div class="feature-item">
          <i class="fas fa-check-circle"></i>
          <span>Automated Stock Alerts</span>
        </div>
        <div class="feature-item">
          <i class="fas fa-check-circle"></i>
          <span>Order Management</span>
        </div>
        <div class="feature-item">
          <i class="fas fa-check-circle"></i>
          <span>Customer Database</span>
        </div>
        <div class="feature-item">
          <i class="fas fa-check-circle"></i>
          <span>Category Organization</span>
        </div>
        <div class="feature-item">
          <i class="fas fa-check-circle"></i>
          <span>Price Management</span>
        </div>
      </div>
    </div>
  </section>

  <section class="contact" id="contact-section">
    <div class="contact-header">
      <h2>Get In Touch</h2>
      <p class="contact-subtitle">We'd love to hear from you. Reach out with any questions or inquiries.</p>
    </div>

    <div class="contact-container">
      <div class="contact-card">
        <div class="contact-icon">
          <i class="fas fa-envelope"></i>
        </div>
        <h3>Email Us</h3>
        <p class="contact-description">Send us your inquiries and we'll respond promptly.</p>
        <a href="mailto:markcyrellquinio@gmail.com" class="contact-link">
          <i class="fas fa-arrow-right"></i> markcyrellquinio@gmail.com
        </a>
      </div>

      <div class="contact-card">
        <div class="contact-icon">
          <i class="fab fa-facebook"></i>
        </div>
        <h3>Follow Us</h3>
        <p class="contact-description">Connect with us on social media for updates.</p>
        <a href="https://www.facebook.com/acadsphere.quinio" target="_blank" class="contact-link">
          <i class="fas fa-arrow-right"></i> Mark Cyrell Quinio
        </a>
      </div>

      <div class="contact-card">
        <div class="contact-icon">
          <i class="fas fa-coffee"></i>
        </div>
        <h3>About CyreCafé</h3>
        <p class="contact-description">Visit us for premium coffee and excellent service.</p>
        <p style="color: #999; font-size: 14px; margin-top: 15px;">
          <i class="fas fa-map-marker-alt" style="color: #26a69a; margin-right: 5px;"></i>
          Your Coffee Destination
        </p>
      </div>
    </div>
  </section>

</div>


</body>
</html>
