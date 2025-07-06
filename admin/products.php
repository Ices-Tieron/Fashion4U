<?php
  include 'includes/header.php';
  
  // Handle delete product request
  if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $product_id = mysqli_real_escape_string($conn, $_GET['delete']);
    $force_delete = isset($_GET['force']) && $_GET['force'] == 1;
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
      // Delete from cart first
      $query_cart = "DELETE FROM cart WHERE product_id = $product_id";
      mysqli_query($conn, $query_cart);
      
      // Delete from reviews
      $query_reviews = "DELETE FROM reviews WHERE product_id = $product_id";
      mysqli_query($conn, $query_reviews);
      
      // Check if product is in any orders
      $query_check_orders = "SELECT COUNT(*) as count FROM order_items WHERE product_id = $product_id";
      $result_check = mysqli_query($conn, $query_check_orders);
      $order_count = mysqli_fetch_assoc($result_check)['count'];
      
      if ($order_count > 0) {
        if ($force_delete) {
          // Force delete from order_items if requested
          $query_order_items = "DELETE FROM order_items WHERE product_id = $product_id";
          if (!mysqli_query($conn, $query_order_items)) {
            throw new Exception(mysqli_error($conn));
          }
        } else {
          // Show an error with a link to force delete
          throw new Exception("Product is part of order history. <a href='products.php?delete=$product_id&force=1' class='alert-link'>Click here</a> to force delete (warning: this will affect order history).");
        }
      }
      
      // Delete the product
      $query = "DELETE FROM products WHERE id = $product_id";
      if (!mysqli_query($conn, $query)) {
        throw new Exception(mysqli_error($conn));
      }
      
      // Commit transaction
      mysqli_commit($conn);
      $success_message = "Product deleted successfully.";
      
    } catch (Exception $e) {
      // Rollback transaction on error
      mysqli_rollback($conn);
      $error_message = "Error deleting product: " . $e->getMessage();
    }
  }
  
  // Get all products with category name
  $query = "SELECT p.*, c.name as category_name 
            FROM products p
            JOIN categories c ON p.category_id = c.id
            ORDER BY p.id DESC";
  $result = mysqli_query($conn, $query);
  
  // Handle query errors
  if (!$result) {
    echo '<div class="alert alert-danger">Error executing query: ' . mysqli_error($conn) . '</div>';
  }
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h2">Products</h1>
  <a href="add-product.php" class="btn btn-primary">
    <i class="bi bi-plus-circle"></i> Add New Product
  </a>
</div>

<?php if (isset($success_message)): ?>
  <div class="alert alert-success"><?php echo $success_message; ?></div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
  <div class="alert alert-danger"><?php echo html_entity_decode($error_message); ?></div>
<?php endif; ?>

<div class="card shadow mb-4">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered table-hover" width="100%" cellspacing="0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Sales</th>
            <th>Featured</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while($product = mysqli_fetch_assoc($result)): ?>
              <tr>
                <td><?php echo $product['id']; ?></td>
                <td>
                  <?php if (file_exists('../' . $product['image_url'])): ?>
                    <img src="../<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" style="width: 50px; height: 50px; object-fit: cover;">
                  <?php else: ?>
                    <div class="bg-light text-center p-2" style="width: 50px; height: 50px;"><small>No Image</small></div>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="../product.php?id=<?php echo $product['id']; ?>" class="text-decoration-none">
                    <?php echo $product['name']; ?>
                  </a>
                </td>
                <td><?php echo $product['category_name']; ?></td>
                <td>₵<?php echo number_format($product['price'], 2); ?></td>
                <td><?php echo $product['stock_quantity']; ?></td>
                <td><?php echo $product['sales_count']; ?></td>
                <td>
                  <?php if($product['featured'] == 1): ?>
                    <span class="badge bg-success"><i class="bi bi-check-lg"></i></span>
                  <?php else: ?>
                    <span class="badge bg-secondary"><i class="bi bi-x-lg"></i></span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-info">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <a href="products.php?delete=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">
                    <i class="bi bi-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" class="text-center">No products found</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>