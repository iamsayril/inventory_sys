<?php
include 'database.php'; // your DB connection

// --- Helper: show friendly errors in dev (remove/disable in production) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Upload directory
$upload_dir = __DIR__ . '/uploads/';

// Ensure uploads dir exists
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        die("Failed to create uploads directory. Please create '{$upload_dir}' and make it writable.");
    }
}

$errors = [];

if (isset($_POST['submit'])) {
    $product_name = trim($_POST['product_name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');

    // Basic validation
    if ($product_name === '' || $price === '' || $quantity === '') {
        $errors[] = "Please fill in all required fields (name, price, quantity).";
    }

    // Validate file upload exists
    if (!isset($_FILES['prod_img'])) {
        $errors[] = "No file upload detected. Make sure your form has enctype='multipart/form-data' and input name='prod_img'.";
    } else {
        $file = $_FILES['prod_img'];

        // Check PHP upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errors[] = "Uploaded file is too large. Check upload_max_filesize / post_max_size in php.ini.";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errors[] = "File was only partially uploaded.";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errors[] = "No file was uploaded.";
                    break;
                default:
                    $errors[] = "File upload error code: " . $file['error'];
            }
        } else {
            // Accept only images (jpg, png, gif)
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
            if (!array_key_exists($mime, $allowed)) {
                $errors[] = "Only JPG, PNG, GIF images are allowed. Detected type: {$mime}";
            }
        }
    }

    // If no validation errors, proceed to move and insert
    if (empty($errors)) {
        // Build a safe filename
        $ext = $allowed[$mime];
        $safe_name = time() . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', basename($file['name']));
        // ensure extension
        if (!preg_match("/\.$ext$/i", $safe_name)) {
            $safe_name .= '.' . $ext;
        }
        $target_path = $upload_dir . $safe_name;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $target_path)) {
            $errors[] = "Failed to move uploaded file to target directory. Check directory permissions for '{$upload_dir}'.";
        } else {
            // Insert into DB using prepared statement (prod_img stores filename)
            $stmt = $conn->prepare("INSERT INTO products (product_name, price, quantity, prod_img) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                $errors[] = "DB prepare failed: " . $conn->error;
                // Optionally unlink uploaded file on DB failure:
                @unlink($target_path);
            } else {
                $stmt->bind_param("sdis", $product_name, $price, $quantity, $safe_name);
                if ($stmt->execute()) {
                    // Success: redirect back to index
                    header("Location: index.php#coffee-section");
                    exit;
                } else {
                    $errors[] = "DB execute failed: " . $stmt->error;
                    @unlink($target_path);
                }
                $stmt->close();
            }
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
</head>
<body>
  <div class="container">
    <h2 style="text-align:center; margin:30px 0;">Add New Product</h2>

    <?php if (!empty($errors)): ?>
      <div style="max-width:700px;margin:0 auto 20px;padding:12px;background:#ffe6e6;color:#900;border-radius:8px;">
        <ul style="margin:0 0 0 18px;">
          <?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form class="edit-form" method="POST" action="" enctype="multipart/form-data">
      <label for="product_name">Product Name:</label>
      <input type="text" name="product_name" id="product_name" required value="<?php echo htmlspecialchars($_POST['product_name'] ?? ''); ?>">

      <label for="price">Price (₱):</label>
      <input type="number" name="price" id="price" step="0.01" required value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>">

      <label for="quantity">Quantity:</label>
      <input type="number" name="quantity" id="quantity" required value="<?php echo htmlspecialchars($_POST['quantity'] ?? ''); ?>">

      <label for="prod_img">Upload Image:</label>
      <input type="file" name="prod_img" id="prod_img" accept="image/*" required>

      <button type="submit" name="submit">Add Product</button>
    </form>
  </div>
</body>
</html>
