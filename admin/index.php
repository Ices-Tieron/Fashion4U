<?php
  include 'includes/header.php';

  // Get summary statistics for dashboard
  
  // Total Products
  $query_products = "SELECT COUNT(*) as total FROM products";
  $result_products = mysqli_query($conn, $query_products);
  $total_products = mysqli_fetch_assoc($result_products)['total'];
  
  // Total Orders
  $query_orders = "SELECT COUNT(*) as total FROM orders";
  $result_orders = mysqli_query($conn, $query_orders);
  $total_orders = mysqli_fetch_assoc($result_orders)['total'];
  
  // Total Customers
  $query_customers = "SELECT COUNT(*) as total FROM users WHERE is_admin = 0";
  $result_customers = mysqli_query($conn, $query_customers);
  $total_customers = mysqli_fetch_assoc($result_customers)['total'];
  
  // Total Revenue
  $query_revenue = "SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'";
  $result_revenue = mysqli_query($conn, $query_revenue);
  $total_revenue = mysqli_fetch_assoc($result_revenue)['total'] ?: 0;
  
  // Recent Orders
  $query_recent = "SELECT o.*, u.username FROM orders o
                  JOIN users u ON o.user_id = u.id
                  ORDER BY o.created_at DESC LIMIT 5";
  $result_recent = mysqli_query($conn, $query_recent);
  
  // Top Selling Products
  $query_top_products = "SELECT p.*, SUM(oi.quantity) as total_sold 
                        FROM products p
                        JOIN order_items oi ON p.id = oi.product_id
                        GROUP BY p.id
                        ORDER BY total_sold DESC
                        LIMIT 5";
  $result_top_products = mysqli_query($conn, $query_top_products);
?>

<h1 class="h2 mb-4">Dashboard</h1>

<div class="row mb-4">
  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card card-dashboard card-primary shadow h-100 py-2">
      <div class="card-body">
        <div class="row no-gutters align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Products</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_products; ?></div>
          </div>
          <div class="col-auto">
            <i class="bi bi-box fa-2x text-gray-300"></i>
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
            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Orders</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_orders; ?></div>
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
            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Customers</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_customers; ?></div>
          </div>
          <div class="col-auto">
            <i class="bi bi-people fa-2x text-gray-300"></i>
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
            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Revenue</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">₵<?php echo number_format($total_revenue, 2); ?></div>
          </div>
          <div class="col-auto">
            <i class="bi bi-currency-dollar fa-2x text-gray-300"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-8">
    <div class="card shadow mb-4">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold">Recent Orders</h6>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover" width="100%" cellspacing="0">
            <thead>
              <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php while($order = mysqli_fetch_assoc($result_recent)): ?>
              <tr>
                <td>#<?php echo $order['id']; ?></td>
                <td><?php echo $order['username']; ?></td>
                <td>₵<?php echo number_format($order['total_amount'], 2); ?></td>
                <td>
                  <?php if($order['status'] == 'pending'): ?>
                    <span class="badge bg-warning text-dark">Pending</span>
                  <?php elseif($order['status'] == 'processing'): ?>
                    <span class="badge bg-info">Processing</span>
                  <?php elseif($order['status'] == 'shipped'): ?>
                    <span class="badge bg-primary">Shipped</span>
                  <?php elseif($order['status'] == 'delivered'): ?>
                    <span class="badge bg-success">Delivered</span>
                  <?php else: ?>
                    <span class="badge bg-secondary"><?php echo ucfirst($order['status']); ?></span>
                  <?php endif; ?>
                </td>
                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Top Selling Products</h6>
      </div>
      <div class="card-body">
        <ul class="list-group list-group-flush">
          <?php while($product = mysqli_fetch_assoc($result_top_products)): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <a href="../product.php?id=<?php echo $product['id']; ?>" class="text-decoration-none">
                <?php echo $product['name']; ?>
              </a>
              <span class="badge bg-primary rounded-pill"><?php echo $product['total_sold']; ?> sold</span>
            </li>
          <?php endwhile; ?>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-12">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Sales Overview</h6>
      </div>
      <div class="card-body">
        <canvas id="salesChart" width="100%" height="30"></canvas>
      </div>
    </div>
  </div>
</div>

<script>
  // Generate sample sales data for chart
  document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('salesChart').getContext('2d');
    var salesChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
        datasets: [{
          label: 'Sales (₵)',
          data: [3500, 4200, 3800, 5100, 4800, 5600, 6200],
          backgroundColor: 'rgba(78, 115, 223, 0.05)',
          borderColor: 'rgba(78, 115, 223, 1)',
          pointBackgroundColor: 'rgba(78, 115, 223, 1)',
          pointBorderColor: '#fff',
          pointHoverBackgroundColor: '#fff',
          pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
          borderWidth: 2
        }]
      },
      options: {
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: false
          }
        },
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });
  });
</script>

<?php include 'includes/footer.php'; ?>