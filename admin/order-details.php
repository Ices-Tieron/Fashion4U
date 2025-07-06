<?php
  include 'includes/header.php';
  
  // Check if order ID is provided
  if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: orders.php");
    exit();
  }
  
  $order_id = mysqli_real_escape_string($conn, $_GET['id']);
  
  // Get order details
  $query = "SELECT o.*, u.username, u.email, u.first_name, u.last_name, u.phone
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = $order_id";
  $result = mysqli_query($conn, $query);
  
  if (mysqli_num_rows($result) == 0) {
    header("Location: orders.php");
    exit();
  }
  
  $order = mysqli_fetch_assoc($result);
  
  // Get order items
  $query_items = "SELECT oi.*, p.name, p.image_url
                  FROM order_items oi
                  JOIN products p ON oi.product_id = p.id
                  WHERE oi.order_id = $order_id";
  $result_items = mysqli_query($conn, $query_items);
  
  // Get payment details
  $query_payment = "SELECT * FROM payments WHERE order_id = $order_id";
  $result_payment = mysqli_query($conn, $query_payment);
  $payment = mysqli_fetch_assoc($result_payment);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h2">Order #<?php echo $order_id; ?> Details</h1>
  <a href="orders.php" class="btn btn-secondary">
    <i class="bi bi-arrow-left"></i> Back to Orders
  </a>
</div>

<div class="row mb-4">
  <div class="col-md-6">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Order Information</h6>
      </div>
      <div class="card-body">
        <table class="table table-borderless">
          <tr>
            <th width="35%">Order ID:</th>
            <td>#<?php echo $order['id']; ?></td>
          </tr>
          <tr>
            <th>Date:</th>
            <td><?php echo date('F d, Y h:i A', strtotime($order['created_at'])); ?></td>
          </tr>
          <tr>
            <th>Status:</th>
            <td>
              <form method="POST" action="orders.php">
                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                <input type="hidden" name="update_status" value="1">
                <select class="form-select" name="status" onchange="this.form.submit()">
                  <option value="pending" <?php echo ($order['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                  <option value="processing" <?php echo ($order['status'] == 'processing') ? 'selected' : ''; ?>>Processing</option>
                  <option value="shipped" <?php echo ($order['status'] == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                  <option value="delivered" <?php echo ($order['status'] == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                  <option value="cancelled" <?php echo ($order['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                </select>
              </form>
            </td>
          </tr>
          <tr>
            <th>Total Amount:</th>
            <td>₵<?php echo number_format($order['total_amount'], 2); ?></td>
          </tr>
          <tr>
            <th>Payment Method:</th>
            <td><?php echo $order['payment_method']; ?></td>
          </tr>
          <tr>
            <th>Payment Status:</th>
            <td>
              <span class="badge <?php echo ($order['payment_status'] == 'paid') ? 'bg-success' : 'bg-warning text-dark'; ?>">
                <?php echo ucfirst($order['payment_status']); ?>
              </span>
            </td>
          </tr>
          <?php if($order['tracking_number']): ?>
          <tr>
            <th>Tracking Number:</th>
            <td><?php echo $order['tracking_number']; ?></td>
          </tr>
          <?php endif; ?>
          <?php if($order['estimated_delivery']): ?>
          <tr>
            <th>Estimated Delivery:</th>
            <td><?php echo date('F d, Y', strtotime($order['estimated_delivery'])); ?></td>
          </tr>
          <?php endif; ?>
        </table>
      </div>
    </div>
  </div>
  
  <div class="col-md-6">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Customer Information</h6>
      </div>
      <div class="card-body">
        <table class="table table-borderless">
          <tr>
            <th width="35%">Name:</th>
            <td><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></td>
          </tr>
          <tr>
            <th>Username:</th>
            <td><?php echo $order['username']; ?></td>
          </tr>
          <tr>
            <th>Email:</th>
            <td><?php echo $order['email']; ?></td>
          </tr>
          <?php if($order['phone']): ?>
          <tr>
            <th>Phone:</th>
            <td><?php echo $order['phone']; ?></td>
          </tr>
          <?php endif; ?>
        </table>
      </div>
    </div>
    
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Shipping Address</h6>
      </div>
      <div class="card-body">
        <p><?php echo nl2br($order['shipping_address']); ?></p>
      </div>
    </div>
  </div>
</div>

<div class="card shadow mb-4">
  <div class="card-header py-3">
    <h6 class="m-0 font-weight-bold">Order Items</h6>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <?php 
            $subtotal = 0;
            while($item = mysqli_fetch_assoc($result_items)): 
            $item_total = $item['price'] * $item['quantity'];
            $subtotal += $item_total;
          ?>
            <tr>
              <td>
                <div class="d-flex align-items-center">
                  <img src="../<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                  <div>
                    <?php echo $item['name']; ?>
                  </div>
                </div>
              </td>
              <td>₵<?php echo number_format($item['price'], 2); ?></td>
              <td><?php echo $item['quantity']; ?></td>
              <td>₵<?php echo number_format($item_total, 2); ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="3" class="text-end">Subtotal:</th>
            <td>₵<?php echo number_format($subtotal, 2); ?></td>
          </tr>
          <tr>
            <th colspan="3" class="text-end">Shipping:</th>
            <td>₵<?php echo number_format($order['total_amount'] - $subtotal, 2); ?></td>
          </tr>
          <tr>
            <th colspan="3" class="text-end">Total:</th>
            <td>₵<?php echo number_format($order['total_amount'], 2); ?></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

<?php if($payment): ?>
<div class="card shadow mb-4">
  <div class="card-header py-3">
    <h6 class="m-0 font-weight-bold">Payment Information</h6>
  </div>
  <div class="card-body">
    <table class="table table-borderless">
      <tr>
        <th width="25%">Transaction ID:</th>
        <td><?php echo $payment['transaction_id'] ?? 'N/A'; ?></td>
      </tr>
      <tr>
        <th>Payment Method:</th>
        <td><?php echo $payment['payment_method']; ?></td>
      </tr>
      <tr>
        <th>Amount:</th>
        <td>₵<?php echo number_format($payment['amount'], 2); ?></td>
      </tr>
      <tr>
        <th>Status:</th>
        <td>
          <span class="badge <?php echo ($payment['status'] == 'completed') ? 'bg-success' : 'bg-warning text-dark'; ?>">
            <?php echo ucfirst($payment['status']); ?>
          </span>
        </td>
      </tr>
      <tr>
        <th>Date:</th>
        <td><?php echo date('F d, Y h:i A', strtotime($payment['created_at'])); ?></td>
      </tr>
    </table>
  </div>
</div>
<?php endif; ?>

<div class="row mb-4">
  <div class="col-md-6">
    <a href="#" class="btn btn-primary" onclick="window.print();">
      <i class="bi bi-printer"></i> Print Order
    </a>
  </div>
</div>

<?php include 'includes/footer.php'; ?>