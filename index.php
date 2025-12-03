<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CyreCafé</title>
  <link rel="stylesheet" href="style.css">
  <!-- Font Awesome CDN for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
      <a href="#about-section">About</a>
      <a href="#contact-section">Contact</a>
    </div>
    <div class="nav-search">
      <input type="text" placeholder="Search">
      <button>Search</button>
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
  <!-- ADD PRODUCT BUTTON -->
  <div style="width:100%; text-align:center; margin-bottom:20px;">
    <a href="add_product.php" class="edit-btn" style="padding:12px 20px; font-size:16px;">Add Product</a>
  </div>

  <?php
  include 'database.php';

  $sql = "SELECT * FROM products";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
          echo "<div class='product'>
                  <h4>Name: " . $row["product_name"] . "</h4>
                  <p>Price: ₱" . $row["price"] . "</p>
                  <p>Quantity: " . $row["quantity"] . "</p>
                  <p>Description: " . $row["description"] . "</p>
                  <a href='edit_product.php?product_id=" . $row["product_id"] . "' class='edit-btn'>Edit</a>
                </div>";
      }
  } else {
      echo "<p>No products found.</p>";
  }
  ?>
</section>

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
      <p><i class="fab fa-facebook-square"></i> <a href="https://www.facebook.com/acadsphere.quinio" target="_blank">Mark Cyrell Quinio</a></p>
      <p><i class="fas fa-envelope"></i> <a href="mailto:markcyrellquinio@gmail.com">markcyrellquinio@gmail.com</a></p>
    </div>
  </section>

</div>

</body>
</html>
