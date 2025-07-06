<?php
  include 'includes/header.php';
  
  // Handle bulk order status update
  if (isset($_POST['bulk_update']) && isset($_POST['order_ids']) && isset($_POST['bulk_status'])) {
    $order_ids = $_POST['order_ids'];
    $status = mysqli_real_escape_string($conn, $_POST['bulk_status']);
    
    if (!empty($order_ids)) {
      // Create comma-separated list of order IDs for SQL query
      $order_ids_str = implode(',', array_map('intval', $order_ids));
      
      $query = "UPDATE orders SET status = '$status' WHERE id IN ($order_ids_str)";
      if (mysqli_query($conn, $query)) {
        $success_message = count($order_ids) . " orders updated successfully.";
      } else {
        $error_message = "Error updating orders: " . mysqli_error($conn);
      }
    }
  }
  
  // Handle single order status update
  if (isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $query = "UPDATE orders SET status = '$status' WHERE id = $order_id";
    if (mysqli_query($conn, $query)) {
      $success_message = "Order status updated successfully.";
    } else {
      $error_message = "Error updating order status: " . mysqli_error($conn);
    }
  }
  
  // Set up filtering
  $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
  $payment_filter = isset($_GET['payment']) ? $_GET['payment'] : '';
  $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
  $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
  
  // Build query with filters
  $query = "SELECT o.*, u.username, u.email 
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE 1=1";
  
  if (!empty($status_filter)) {
    $query .= " AND o.status = '$status_filter'";
  }
  
  if (!empty($payment_filter)) {
    $query .= " AND o.payment_status = '$payment_filter'";
  }
  
  if (!empty($date_from)) {
    $query .= " AND DATE(o.created_at) >= '$date_from'";
  }
  
  if (!empty($date_to)) {
    $query .= " AND DATE(o.created_at) <= '$date_to'";
  }
  
  $query .= " ORDER BY o.created_at DESC";
  $result = mysqli_query($conn, $query);
  
  // Handle query errors
  if (!$result) {
    echo '<div class="alert alert-danger">Error executing query: ' . mysqli_error($conn) . '</div>';
  }
  
  // Get order statistics
  $stats_query = "SELECT 
                  COUNT(*) as total_orders,
                  SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                  SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
                  SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
                  SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
                  SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                  SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_orders,
                  SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as unpaid_orders
                FROM orders";
  $stats_result = mysqli_query($conn, $stats_query);
  $stats = mysqli_fetch_assoc($stats_result);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h2">Orders Management</h1>
  <a href="index.php" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left"></i> Back to Dashboard
  </a>
</div>

<?php if (isset($success_message)): ?>
  <div class="alert alert-success"><?php echo $success_message; ?></div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
  <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<!-- Order Statistics -->
<div class="row mb-4">
  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card card-dashboard card-primary shadow h-100 py-2">
      <div class="card-body">
        <div class="row no-gutters align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Orders</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_orders']; ?></div>
          </div>
          <div class="col-auto">
            <i class="bi bi-cart fa-2x text-gray-300"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card card-dashboard card-warning shadow h-100 py-2">
      <div class="card-body">
        <div class="row no-gutters align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending/Processing</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">
              <?php echo $stats['pending_orders'] + $stats['processing_orders']; ?>
            </div>
          </div>
          <div class="col-auto">
            <i class="bi bi-hourglass-split fa-2x text-gray-300"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card card-dashboard card-success shadow h-100 py-2">
      <div class="card-body">
        <div class="row no-gutters align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Delivered</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['delivered_orders']; ?></div>
          </div>
          <div class="col-auto">
            <i class="bi bi-check-circle fa-2x text-gray-300"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card card-dashboard card-danger shadow h-100 py-2">
      <div class="card-body">
        <div class="row no-gutters align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Awaiting Payment</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['unpaid_orders']; ?></div>
          </div>
          <div class="col-auto">
            <i class="bi bi-wallet2 fa-2x text-gray-300"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Filters -->
<div class="card shadow mb-4">
  <div class="card-header py-3">
    <h6 class="m-0 font-weight-bold">Filter Orders</h6>
  </div>
  <div class="card-body">
    <form method="GET" action="orders.php" class="row g-3">
      <div class="col-md-3">
        <label for="status" class="form-label">Order Status</label>
        <select class="form-select" id="status" name="status">
          <option value="">All Statuses</option>
          <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending</option>
          <option value="processing" <?php echo ($status_filter == 'processing') ? 'selected' : ''; ?>>Processing</option>
          <option value="shipped" <?php echo ($status_filter == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
          <option value="delivered" <?php echo ($status_filter == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
          <option value="cancelled" <?php echo ($status_filter == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
        </select>
      </div>
      
      <div class="col-md-3">
        <label for="payment" class="form-label">Payment Status</label>
        <select class="form-select" id="payment" name="payment">
          <option value="">All Payments</option>
          <option value="paid" <?php echo ($payment_filter == 'paid') ? 'selected' : ''; ?>>Paid</option>
          <option value="pending" <?php echo ($payment_filter == 'pending') ? 'selected' : ''; ?>>Pending</option>
        </select>
      </div>
      
      <div class="col-md-3">
        <label for="date_from" class="form-label">Date From</label>
        <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
      </div>
      
      <div class="col-md-3">
        <label for="date_to" class="form-label">Date To</label>
        <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
      </div>
      
      <div class="col-12 mt-4">
        <button type="submit" class="btn btn-primary">Apply Filters</button>
        <a href="orders.php" class="btn btn-secondary">Clear Filters</a>
      </div>
    </form>
  </div>
</div>

<!-- Bulk Actions Form -->
<form id="bulkActionForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
  <input type="hidden" name="bulk_update" value="1">

  <!-- Orders Table -->
  <div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
      <h6 class="m-0 font-weight-bold">Order List</h6>
      <div class="bulk-actions" style="display: none;">
        <div class="d-flex align-items-center">
          <span class="me-2"><span id="selectedCount">0</span> orders selected</span>
          <select class="form-select form-select-sm me-2" name="bulk_status" id="bulkStatus">
            <option value="">Change Status</option>
            <option value="pending">Pending</option>
            <option value="processing">Processing</option>
            <option value="shipped">Shipped</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
          </select>
          <button type="submit" class="btn btn-sm btn-primary" id="applyBulkAction">Apply</button>
          <button type="button" class="btn btn-sm btn-secondary ms-2" id="clearSelection">Clear</button>
        </div>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover" id="ordersTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="selectAll">
                </div>
              </th>
              <th>Order ID</th>
              <th>Customer</th>
              <th>Email</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Payment</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while($order = mysqli_fetch_assoc($result)): ?>
              <tr>
                <td>
                  <div class="form-check">
                    <input class="form-check-input order-checkbox" type="checkbox" name="order_ids[]" value="<?php echo $order['id']; ?>">
                  </div>
                </td>
                <td>#<?php echo $order['id']; ?></td>
                <td><?php echo $order['username']; ?></td>
                <td><?php echo $order['email']; ?></td>
                <td>₵<?php echo number_format($order['total_amount'], 2); ?></td>
                <td>
                  <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="d-inline">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <input type="hidden" name="update_status" value="1">
                    <select class="form-select form-select-sm status-select" name="status" onchange="this.form.submit()">
                      <option value="pending" <?php echo ($order['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                      <option value="processing" <?php echo ($order['status'] == 'processing') ? 'selected' : ''; ?>>Processing</option>
                      <option value="shipped" <?php echo ($order['status'] == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                      <option value="delivered" <?php echo ($order['status'] == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                      <option value="cancelled" <?php echo ($order['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                  </form>
                </td>
                <td>
                  <span class="badge <?php echo ($order['payment_status'] == 'paid') ? 'bg-success' : 'bg-warning text-dark'; ?>">
                    <?php echo ucfirst($order['payment_status']); ?>
                  </span>
                </td>
                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                <td>
                  <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">
                    <i class="bi bi-eye"></i> View
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</form>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable for better search and pagination
    var table = $('#ordersTable').DataTable({
      order: [[7, 'desc']], // Sort by date column (index 7) in descending order
      columnDefs: [
        { orderable: false, targets: [0, 8] } // Disable sorting for checkbox and actions columns
      ]
    });
    
    // Handle "Select All" checkbox
    $('#selectAll').change(function() {
      $('.order-checkbox').prop('checked', $(this).prop('checked'));
      updateBulkActionDisplay();
    });
    
    // Handle individual checkboxes
    $('.order-checkbox').change(function() {
      updateBulkActionDisplay();
      
      // If any checkbox is unchecked, uncheck "Select All"
      if (!$(this).prop('checked')) {
        $('#selectAll').prop('checked', false);
      }
      
      // If all checkboxes are checked, check "Select All"
      if ($('.order-checkbox:checked').length === $('.order-checkbox').length) {
        $('#selectAll').prop('checked', true);
      }
    });
    
    // Clear selection button
    $('#clearSelection').click(function() {
      $('.order-checkbox, #selectAll').prop('checked', false);
      updateBulkActionDisplay();
    });
    
    // Function to update bulk action display
    function updateBulkActionDisplay() {
      var selectedCount = $('.order-checkbox:checked').length;
      $('#selectedCount').text(selectedCount);
      
      if (selectedCount > 0) {
        $('.bulk-actions').show();
      } else {
        $('.bulk-actions').hide();
      }
    }
    
    // Validate bulk action form submission
    $('#bulkActionForm').submit(function(e) {
      var selectedStatus = $('#bulkStatus').val();
      var selectedOrders = $('.order-checkbox:checked').length;
      
      if (selectedStatus === '' || selectedOrders === 0) {
        e.preventDefault();
        alert('Please select both orders and a status to apply.');
      }
    });
  });
</script>

<?php include 'includes/footer.php'; ?>