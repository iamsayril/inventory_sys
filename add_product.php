<?php
include 'database.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

$errors = [];

$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");

if (isset($_POST['submit'])) {
    $product_name = trim($_POST['product_name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $stock = trim($_POST['stock'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);

    if ($product_name === '' || $price === '' || $stock === '' || $category_id === 0) {
        $errors[] = "Please fill in all required fields.";
    }

    if (!isset($_FILES['prod_img'])) {
        $errors[] = "Please upload a product image.";
    } else {
        $file = $_FILES['prod_img'];
        if ($file['error'] !== UPLOAD_ERR_OK) $errors[] = "File upload error: " . $file['error'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif'];
        if (!array_key_exists($mime, $allowed)) $errors[] = "Only JPG, PNG, GIF images are allowed.";
    }

    if (empty($errors)) {
        $ext = $allowed[$mime];
        $safe_name = time() . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', basename($file['name']));
        if (!preg_match("/\.$ext$/i", $safe_name)) $safe_name .= '.' . $ext;
        $target_path = $upload_dir . $safe_name;

        if (!move_uploaded_file($file['tmp_name'], $target_path)) {
            $errors[] = "Failed to move uploaded file.";
        } else {
            $stmt = $conn->prepare("INSERT INTO products (product_name, price, stock, prod_img, category_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sdisi", $product_name, $price, $stock, $safe_name, $category_id);
            if ($stmt->execute()) {
                echo "<script>alert('Product added successfully!'); window.location.href='index.php#coffee-section';</script>";
                exit;
            } else {
                $errors[] = "DB error: " . $stmt->error;
                @unlink($target_path);
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Add Product - CyreCafé</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="add_product.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="container">
    <nav class="nav">
      <div class="nav-logo">
        <img src="uploads/cup-of-coffee.png" alt="Coffee Icon" class="nav-logo-icon">
        <h1>CyreCafé</h1>
      </div>
      <div class="nav-links">
        <a href="index.php">Coffee</a>
        <a href="orders.php">Orders</a>
        <a href="view_orders.php">Placed Orders</a>
        <a href="customers.php">Customers</a>
        <a href="manage_categories.php">Manage Categories</a>
      </div>
    </nav>

    <div style="padding-top: 40px;">
      <h2 style="text-align:center; margin:0 0 30px 0;">Add New Product</h2>

    <?php if (!empty($errors)): ?>
      <div style="max-width:700px;margin:0 auto 20px;padding:12px;background:#ffe6e6;color:#900;border-radius:8px;">
        <ul>
          <?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form class="edit-form" method="POST" action="" enctype="multipart/form-data">
      <label for="product_name">Product Name:</label>
      <input type="text" name="product_name" id="product_name" required value="<?php echo htmlspecialchars($_POST['product_name'] ?? ''); ?>">

      <label for="price">Price (₱):</label>
      <input type="number" name="price" id="price" step="0.01" required value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>">

      <label for="stock">Stock:</label>
      <input type="number" name="stock" id="stock" required value="<?php echo htmlspecialchars($_POST['stock'] ?? ''); ?>">

      <label for="category_id">Category:</label>
      <select name="category_id" id="category_id" required>
        <option value="">-- Select Category --</option>
        <?php while($cat = $categories->fetch_assoc()): ?>
          <option value="<?php echo $cat['category_id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id']==$cat['category_id'])?'selected':''; ?>>
            <?php echo htmlspecialchars($cat['category_name']); ?>
          </option>
        <?php endwhile; ?>
      </select>

      <label for="prod_img">Upload Image:</label>
      <input type="file" name="prod_img" id="prod_img" accept="image/*" required>

      <button type="submit" name="submit">Add Product</button>
    </form>
    </div>
  </div>
</body>
</html>
