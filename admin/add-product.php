<?php
  include 'includes/header.php';
  
  // Initialize variables
  $name = $description = $price = $price_xs = $price_s = $price_m = $price_l = $price_xl = '';
  $category_id = $subcategory = $image_url = $size = $color = $gender = $age_group = '';
  $stock_quantity = 0;
  $featured = 0;
  
  // Get all categories
  $categories_query = "SELECT * FROM categories";
  $categories_result = mysqli_query($conn, $categories_query);
  
  // Process form submission
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $price_xs = isset($_POST['price_xs']) ? floatval($_POST['price_xs']) : NULL;
    $price_s = isset($_POST['price_s']) ? floatval($_POST['price_s']) : NULL;
    $price_m = isset($_POST['price_m']) ? floatval($_POST['price_m']) : NULL;
    $price_l = isset($_POST['price_l']) ? floatval($_POST['price_l']) : NULL;
    $price_xl = isset($_POST['price_xl']) ? floatval($_POST['price_xl']) : NULL;
    $category_id = intval($_POST['category_id']);
    $subcategory = mysqli_real_escape_string($conn, $_POST['subcategory']);
    $size = mysqli_real_escape_string($conn, $_POST['size']);
    $color = mysqli_real_escape_string($conn, $_POST['color']);
    $gender = isset($_POST['gender']) ? mysqli_real_escape_string($conn, $_POST['gender']) : NULL;
    $age_group = isset($_POST['age_group']) ? mysqli_real_escape_string($conn, $_POST['age_group']) : NULL;
    $stock_quantity = intval($_POST['stock_quantity']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // Handle image upload
    $image_url = '';
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
      $target_dir = "../images/";
      
      // Create directory if it doesn't exist
      if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
      }
      
      // Generate a unique filename to prevent overwriting
      $file_extension = pathinfo($_FILES["product_image"]["name"], PATHINFO_EXTENSION);
      $file_name = uniqid('product_') . '.' . $file_extension;
      
      $target_file = $target_dir . $file_name;
      $image_url = "images/" . $file_name;
      
      // Move uploaded file
      if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
        // File uploaded successfully
      } else {
        $error_message = "Sorry, there was an error uploading your file.";
      }
    } else {
      $error_message = "Please select an image for the product.";
    }
    
    // Validate required fields
    if (empty($name) || empty($description) || empty($price) || empty($category_id) || 
        empty($subcategory) || empty($image_url) || empty($stock_quantity)) {
      $error_message = "Please fill all required fields.";
    } else {
      // Insert product into database
      $query = "INSERT INTO products (name, description, price, price_xs, price_s, price_m, price_l, price_xl, 
                category_id, subcategory, image_url, size, color, gender, age_group, stock_quantity, featured) 
                VALUES ('$name', '$description', $price, $price_xs, $price_s, $price_m, $price_l, $price_xl, 
                $category_id, '$subcategory', '$image_url', '$size', '$color', '$gender', '$age_group', 
                $stock_quantity, $featured)";
      
      if (mysqli_query($conn, $query)) {
        $success_message = "Product added successfully!";
        // Reset form fields
        $name = $description = $price = $price_xs = $price_s = $price_m = $price_l = $price_xl = '';
        $category_id = $subcategory = $image_url = $size = $color = $gender = $age_group = '';
        $stock_quantity = 0;
        $featured = 0;
      } else {
        $error_message = "Error: " . mysqli_error($conn);
      }
    }
  }
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h2">Add New Product</h1>
  <a href="products.php" class="btn btn-secondary">
    <i class="bi bi-arrow-left"></i> Back to Products
  </a>
</div>

<?php if (isset($success_message)): ?>
  <div class="alert alert-success"><?php echo $success_message; ?></div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
  <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<div class="card shadow mb-4">
  <div class="card-body">
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
      <div class="row mb-3">
        <div class="col-md-6">
          <div class="mb-3">
            <label for="name" class="form-label">Product Name *</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>" required>
          </div>
          
          <div class="mb-3">
            <label for="description" class="form-label">Description *</label>
            <textarea class="form-control" id="description" name="description" rows="3" required><?php echo $description; ?></textarea>
          </div>
          
          <div class="mb-3">
            <label for="category_id" class="form-label">Category *</label>
            <select class="form-select" id="category_id" name="category_id" required>
              <option value="">Select Category</option>
              <?php while($category = mysqli_fetch_assoc($categories_result)): ?>
                <option value="<?php echo $category['id']; ?>" <?php echo ($category_id == $category['id']) ? 'selected' : ''; ?>>
                  <?php echo $category['name']; ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          
          <div class="mb-3">
            <label for="subcategory" class="form-label">Subcategory *</label>
            <input type="text" class="form-control" id="subcategory" name="subcategory" value="<?php echo $subcategory; ?>" required>
          </div>
          
          <div class="mb-3">
            <label for="price" class="form-label">Base Price (₵) *</label>
            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?php echo $price; ?>" required>
          </div>
        </div>
        
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">Size-specific Pricing</label>
            <div class="row g-2">
              <div class="col-4">
                <div class="input-group mb-2">
                  <span class="input-group-text">XS ₵</span>
                  <input type="number" class="form-control" name="price_xs" step="0.01" min="0" value="<?php echo $price_xs; ?>">
                </div>
              </div>
              <div class="col-4">
                <div class="input-group mb-2">
                  <span class="input-group-text">S ₵</span>
                  <input type="number" class="form-control" name="price_s" step="0.01" min="0" value="<?php echo $price_s; ?>">
                </div>
              </div>
              <div class="col-4">
                <div class="input-group mb-2">
                  <span class="input-group-text">M ₵</span>
                  <input type="number" class="form-control" name="price_m" step="0.01" min="0" value="<?php echo $price_m; ?>">
                </div>
              </div>
              <div class="col-4">
                <div class="input-group mb-2">
                  <span class="input-group-text">L ₵</span>
                  <input type="number" class="form-control" name="price_l" step="0.01" min="0" value="<?php echo $price_l; ?>">
                </div>
              </div>
              <div class="col-4">
                <div class="input-group mb-2">
                  <span class="input-group-text">XL ₵</span>
                  <input type="number" class="form-control" name="price_xl" step="0.01" min="0" value="<?php echo $price_xl; ?>">
                </div>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="size" class="form-label">Default Size</label>
                <select class="form-select" id="size" name="size">
                  <option value="">Select Size</option>
                  <option value="xs" <?php echo ($size == 'xs') ? 'selected' : ''; ?>>XS</option>
                  <option value="s" <?php echo ($size == 's') ? 'selected' : ''; ?>>S</option>
                  <option value="m" <?php echo ($size == 'm') ? 'selected' : ''; ?>>M</option>
                  <option value="l" <?php echo ($size == 'l') ? 'selected' : ''; ?>>L</option>
                  <option value="xl" <?php echo ($size == 'xl') ? 'selected' : ''; ?>>XL</option>
                  <option value="one-size" <?php echo ($size == 'one-size') ? 'selected' : ''; ?>>One Size</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="color" class="form-label">Color</label>
                <input type="text" class="form-control" id="color" name="color" value="<?php echo $color; ?>">
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="gender" class="form-label">Gender</label>
                <select class="form-select" id="gender" name="gender">
                  <option value="">Select Gender</option>
                  <option value="men" <?php echo ($gender == 'men') ? 'selected' : ''; ?>>Men</option>
                  <option value="women" <?php echo ($gender == 'women') ? 'selected' : ''; ?>>Women</option>
                  <option value="boys" <?php echo ($gender == 'boys') ? 'selected' : ''; ?>>Boys</option>
                  <option value="girls" <?php echo ($gender == 'girls') ? 'selected' : ''; ?>>Girls</option>
                  <option value="unisex" <?php echo ($gender == 'unisex') ? 'selected' : ''; ?>>Unisex</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="age_group" class="form-label">Age Group</label>
                <select class="form-select" id="age_group" name="age_group">
                  <option value="">Select Age Group</option>
                  <option value="adult" <?php echo ($age_group == 'adult') ? 'selected' : ''; ?>>Adult</option>
                  <option value="teen" <?php echo ($age_group == 'teen') ? 'selected' : ''; ?>>Teen</option>
                  <option value="kid" <?php echo ($age_group == 'kid') ? 'selected' : ''; ?>>Kid</option>
                  <option value="baby" <?php echo ($age_group == 'baby') ? 'selected' : ''; ?>>Baby</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="stock_quantity" class="form-label">Stock Quantity *</label>
            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" value="<?php echo $stock_quantity; ?>" required>
          </div>
          
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="featured" name="featured" <?php echo ($featured == 1) ? 'checked' : ''; ?>>
              <label class="form-check-label" for="featured">
                Featured Product
              </label>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="product_image" class="form-label">Product Image *</label>
            <input type="file" class="form-control" id="product_image" name="product_image" accept="image/*" required>
          </div>
        </div>
      </div>
      
      <div class="text-end">
        <button type="submit" class="btn btn-primary">Add Product</button>
      </div>
    </form>
  </div>
</div>

<?php include 'includes/footer.php'; ?>