<?php
  include 'includes/header.php';
  
  // Handle payment status update
  if (isset($_POST['update_status']) && isset($_POST['payment_id']) && isset($_POST['status'])) {
    $payment_id = mysqli_real_escape_string($conn, $_POST['payment_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $query = "UPDATE payments SET status = '$status' WHERE id = $payment_id";
    if (mysqli_query($conn, $query)) {
      // If payment is marked as completed, also update the order payment_status
      if ($status == 'completed') {
        $get_order_query = "SELECT order_id FROM payments WHERE id = $payment_id";
        $order_result = mysqli_query($conn, $get_order_query);
        if ($order_row = mysqli_fetch_assoc($order_result)) {
          $order_id = $order_row['order_id'];
          $update_order_query = "UPDATE orders SET payment_status = 'paid' WHERE id = $order_id";
          mysqli_query($conn, $update_order_query);
        }
      }
      $success_message = "Payment status updated successfully.";
    } else {
      $error_message = "Error updating payment status: " . mysqli_error($conn);
    }
  }
  
  // Set up filtering
  $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
  $method_filter = isset($_GET['method']) ? $_GET['method'] : '';
  $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
  $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
  
  // Build query with filters
  $query = "SELECT p.*, o.id as order_id, o.total_amount as order_amount, u.username, u.email 
            FROM payments p
            JOIN orders o ON p.order_id = o.id
            JOIN users u ON p.user_id = u.id
            WHERE 1=1";
  
  if (!empty($status_filter)) {
    $query .= " AND p.status = '$status_filter'";
  }
  
  if (!empty($method_filter)) {
    $query .= " AND p.payment_method = '$method_filter'";
  }
  
  if (!empty($date_from)) {
    $query .= " AND DATE(p.created_at) >= '$date_from'";
  }
  
  if (!empty($date_to)) {
    $query .= " AND DATE(p.created_at) <= '$date_to'";
  }
  
  $query .= " ORDER BY p.created_at DESC";
  $result = mysqli_query($conn, $query);
  
  // Get available payment methods
  $methods_query = "SELECT DISTINCT payment_method FROM payments";
  $methods_result = mysqli_query($conn, $methods_query);
  $payment_methods = [];
  while ($method = mysqli_fetch_assoc($methods_result)) {
    $payment_methods[] = $method['payment_method'];
  }
  
  // Get payment statistics
  $stats_query = "SELECT 
                    COUNT(*) as total_payments,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_payments,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_payments,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_payments,
                    SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_revenue
                  FROM payments";
  $stats_result = mysqli_query($conn, $stats_query);
  $stats = mysqli_fetch_assoc($stats_result);
  
  // Get payment method distribution
  $methods_stats_query = "SELECT 
                          payment_method,
                          COUNT(*) as count,
                          SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as revenue
                        FROM payments
                        GROUP BY payment_method";
  $methods_stats_result = mysqli_query($conn, $methods_stats_query);
  $methods_stats = [];
  while ($method_stat = mysqli_fetch_assoc($methods_stats_result)) {
    $methods_stats[$method_stat['payment_method']] = $method_stat;
  }
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h2">Payment Management</h1>
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

<!-- Payment Statistics -->
<div class="row mb-4">
  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card card-dashboard card-primary shadow h-100 py-2">
      <div class="card-body">
        <div class="row no-gutters align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Payments</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_payments']; ?></div>
          </div>
          <div class="col-auto">
            <i class="bi bi-credit-card fa-2x text-gray-300"></i>
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
            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">
              <?php echo $stats['completed_payments']; ?> 
              <span class="small text-muted">(<?php echo ($stats['total_payments'] > 0) ? round(($stats['completed_payments'] / $stats['total_payments']) * 100) : 0; ?>%)</span>
            </div>
          </div>
          <div class="col-auto">
            <i class="bi bi-check-circle fa-2x text-gray-300"></i>
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
            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">
              <?php echo $stats['pending_payments']; ?>
              <span class="small text-muted">(<?php echo ($stats['total_payments'] > 0) ? round(($stats['pending_payments'] / $stats['total_payments']) * 100) : 0; ?>%)</span>
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
    <div class="card card-dashboard card-danger shadow h-100 py-2">
      <div class="card-body">
        <div class="row no-gutters align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Revenue</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">₵<?php echo number_format($stats['total_revenue'], 2); ?></div>
          </div>
          <div class="col-auto">
            <i class="bi bi-currency-dollar fa-2x text-gray-300"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Payment Method Distribution Chart -->
<div class="row mb-4">
  <div class="col-lg-8">
    <div class="card shadow mb-4">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold">Payment Methods</h6>
      </div>
      <div class="card-body">
        <div style="height: 300px;">
          <canvas id="paymentMethodsChart"></canvas>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-lg-4">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Method Distribution</h6>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm">
            <thead>
              <tr>
                <th>Payment Method</th>
                <th>Count</th>
                <th>Revenue</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($methods_stats as $method => $stat): ?>
                <tr>
                  <td><?php echo $method; ?></td>
                  <td><?php echo $stat['count']; ?></td>
                  <td>₵<?php echo number_format($stat['revenue'], 2); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Filters -->
<div class="card shadow mb-4">
  <div class="card-header py-3">
    <h6 class="m-0 font-weight-bold">Filter Payments</h6>
  </div>
  <div class="card-body">
    <form method="GET" action="payments.php" class="row g-3">
      <div class="col-md-3">
        <label for="status" class="form-label">Payment Status</label>
        <select class="form-select" id="status" name="status">
          <option value="">All Statuses</option>
          <option value="completed" <?php echo ($status_filter == 'completed') ? 'selected' : ''; ?>>Completed</option>
          <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending</option>
          <option value="failed" <?php echo ($status_filter == 'failed') ? 'selected' : ''; ?>>Failed</option>
        </select>
      </div>
      
      <div class="col-md-3">
        <label for="method" class="form-label">Payment Method</label>
        <select class="form-select" id="method" name="method">
          <option value="">All Methods</option>
          <?php foreach($payment_methods as $method): ?>
            <option value="<?php echo $method; ?>" <?php echo ($method_filter == $method) ? 'selected' : ''; ?>>
              <?php echo $method; ?>
            </option>
          <?php endforeach; ?>
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
        <a href="payments.php" class="btn btn-secondary">Clear Filters</a>
      </div>
    </form>
  </div>
</div>

<!-- Payments Table -->
<div class="card shadow mb-4">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered table-hover" id="paymentsTable" width="100%" cellspacing="0">
        <thead>
          <tr>
            <th>Payment ID</th>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Transaction ID</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while($payment = mysqli_fetch_assoc($result)): ?>
            <tr>
              <td><?php echo $payment['id']; ?></td>
              <td><a href="order-details.php?id=<?php echo $payment['order_id']; ?>">#<?php echo $payment['order_id']; ?></a></td>
              <td><?php echo $payment['username']; ?> (<?php echo $payment['email']; ?>)</td>
              <td>₵<?php echo number_format($payment['amount'], 2); ?></td>
              <td><?php echo $payment['payment_method']; ?></td>
              <td>
                <?php echo $payment['transaction_id'] ? $payment['transaction_id'] : '<span class="text-muted">Not available</span>'; ?>
              </td>
              <td>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="d-inline">
                  <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                  <input type="hidden" name="update_status" value="1">
                  <select class="form-select form-select-sm status-select" name="status" onchange="this.form.submit()">
                    <option value="pending" <?php echo ($payment['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="completed" <?php echo ($payment['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                    <option value="failed" <?php echo ($payment['status'] == 'failed') ? 'selected' : ''; ?>>Failed</option>
                  </select>
                </form>
              </td>
              <td><?php echo date('M d, Y H:i', strtotime($payment['created_at'])); ?></td>
              <td>
                <a href="order-details.php?id=<?php echo $payment['order_id']; ?>" class="btn btn-sm btn-info">
                  <i class="bi bi-eye"></i> View Order
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable for better search and pagination
    $('#paymentsTable').DataTable({
      order: [[7, 'desc']], // Sort by date column (index 7) in descending order
    });
    
    // Payment Methods Chart
    var ctx = document.getElementById('paymentMethodsChart').getContext('2d');
    var methodLabels = [];
    var methodCounts = [];
    var methodRevenue = [];
    var backgroundColors = [
      'rgba(78, 115, 223, 0.8)',
      'rgba(28, 200, 138, 0.8)',
      'rgba(246, 194, 62, 0.8)',
      'rgba(231, 74, 59, 0.8)',
      'rgba(54, 185, 204, 0.8)'
    ];
    
    <?php foreach ($methods_stats as $method => $stat): ?>
      methodLabels.push('<?php echo $method; ?>');
      methodCounts.push(<?php echo $stat['count']; ?>);
      methodRevenue.push(<?php echo $stat['revenue']; ?>);
    <?php endforeach; ?>
    
    var paymentMethodsChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: methodLabels,
        datasets: [
          {
            label: 'Transaction Count',
            data: methodCounts,
            backgroundColor: backgroundColors,
            borderColor: backgroundColors.map(color => color.replace('0.8', '1')),
            borderWidth: 1,
            yAxisID: 'y'
          },
          {
            label: 'Revenue (₵)',
            data: methodRevenue,
            type: 'line',
            fill: false,
            borderColor: '#e74a3b',
            tension: 0.1,
            yAxisID: 'y1'
          }
        ]
      },
      options: {
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Transaction Count'
            }
          },
          y1: {
            position: 'right',
            beginAtZero: true,
            grid: {
              drawOnChartArea: false,
            },
            title: {
              display: true,
              text: 'Revenue (₵)'
            }
          }
        }
      }
    });
  });
</script>

<?php include 'includes/footer.php'; ?>