<?php
  include 'includes/header.php';
  
  // Get total sales by product - adding error handling and debug info
  $query_product_sales = "SELECT p.id, p.name, p.image_url, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price) as total_revenue
                         FROM products p
                         LEFT JOIN order_items oi ON p.id = oi.product_id
                         GROUP BY p.id
                         ORDER BY total_sold DESC";
  $result_product_sales = mysqli_query($conn, $query_product_sales);
  
  if (!$result_product_sales) {
    echo '<div class="alert alert-danger">Error executing query: ' . mysqli_error($conn) . '</div>';
  }
  
  // Get top categories
  $query_category_sales = "SELECT c.name, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price) as total_revenue
                          FROM categories c
                          JOIN products p ON c.id = p.category_id
                          JOIN order_items oi ON p.id = oi.product_id
                          GROUP BY c.id
                          ORDER BY total_sold DESC";
  $result_category_sales = mysqli_query($conn, $query_category_sales);
  
  // Get monthly sales data for chart
  $query_monthly_sales = "SELECT 
                            DATE_FORMAT(o.created_at, '%Y-%m') as month,
                            SUM(o.total_amount) as total_revenue,
                            COUNT(DISTINCT o.id) as order_count
                          FROM orders o
                          WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                          GROUP BY DATE_FORMAT(o.created_at, '%Y-%m')
                          ORDER BY month ASC";
  $result_monthly_sales = mysqli_query($conn, $query_monthly_sales);
  
  // Process monthly data for charts
  $months = [];
  $monthly_revenue = [];
  $monthly_orders = [];
  
  while ($row = mysqli_fetch_assoc($result_monthly_sales)) {
    $months[] = date('M Y', strtotime($row['month'] . '-01'));
    $monthly_revenue[] = round($row['total_revenue'], 2);
    $monthly_orders[] = intval($row['order_count']);
  }
  
  // Get products with low sales
  $query_low_sales = "SELECT p.id, p.name, p.image_url, p.stock_quantity, 
                      COALESCE(SUM(oi.quantity), 0) as total_sold
                      FROM products p
                      LEFT JOIN order_items oi ON p.id = oi.product_id
                      GROUP BY p.id
                      HAVING total_sold < 5 OR total_sold IS NULL
                      ORDER BY total_sold ASC, p.stock_quantity DESC
                      LIMIT 10";
  $result_low_sales = mysqli_query($conn, $query_low_sales);
?>

<h1 class="h2 mb-4">Analytics</h1>

<div class="row mb-4">
  <div class="col-lg-12">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Sales Overview (Last 12 Months)</h6>
      </div>
      <div class="card-body">
        <canvas id="salesChart" width="100%" height="30"></canvas>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-6">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Top Selling Products</h6>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover" width="100%" cellspacing="0">
            <thead>
              <tr>
                <th>Product</th>
                <th>Units Sold</th>
                <th>Revenue</th>
              </tr>
            </thead>
            <tbody>
              <?php while($product = mysqli_fetch_assoc($result_product_sales)): ?>
                <?php if($product['total_sold'] > 0): ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <img src="../<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;">
                      <?php echo $product['name']; ?>
                    </div>
                  </td>
                  <td><?php echo $product['total_sold'] ? $product['total_sold'] : 0; ?></td>
                  <td>₵<?php echo $product['total_revenue'] ? number_format($product['total_revenue'], 2) : '0.00'; ?></td>
                </tr>
                <?php endif; ?>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-lg-6">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Category Performance</h6>
      </div>
      <div class="card-body">
        <canvas id="categoryChart" width="100%" height="300"></canvas>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-12">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Products With Low Sales</h6>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover" width="100%" cellspacing="0">
            <thead>
              <tr>
                <th>Product</th>
                <th>Units Sold</th>
                <th>In Stock</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while($product = mysqli_fetch_assoc($result_low_sales)): ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <img src="../<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;">
                      <?php echo $product['name']; ?>
                    </div>
                  </td>
                  <td><?php echo $product['total_sold'] ? $product['total_sold'] : 0; ?></td>
                  <td><?php echo $product['stock_quantity']; ?></td>
                  <td>
                    <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-info">
                      <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="#" class="btn btn-sm btn-warning">
                      <i class="bi bi-tag"></i> Discount
                    </a>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Monthly Sales Chart
    var salesCtx = document.getElementById('salesChart').getContext('2d');
    var salesChart = new Chart(salesCtx, {
      type: 'line',
      data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [
          {
            label: 'Revenue (₵)',
            data: <?php echo json_encode($monthly_revenue); ?>,
            backgroundColor: 'rgba(78, 115, 223, 0.05)',
            borderColor: 'rgba(78, 115, 223, 1)',
            pointBackgroundColor: 'rgba(78, 115, 223, 1)',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
            borderWidth: 2,
            yAxisID: 'y'
          },
          {
            label: 'Orders',
            data: <?php echo json_encode($monthly_orders); ?>,
            backgroundColor: 'rgba(28, 200, 138, 0.05)',
            borderColor: 'rgba(28, 200, 138, 1)',
            pointBackgroundColor: 'rgba(28, 200, 138, 1)',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: 'rgba(28, 200, 138, 1)',
            borderWidth: 2,
            yAxisID: 'y1'
          }
        ]
      },
      options: {
        maintainAspectRatio: false,
        scales: {
          y: {
            position: 'left',
            beginAtZero: true,
            title: {
              display: true,
              text: 'Revenue (₵)'
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
              text: 'Number of Orders'
            }
          }
        }
      }
    });
    
    // Category Chart
    var categoryData = [];
    var categoryLabels = [];
    var categoryColors = [
      'rgba(255, 99, 132, 0.7)',
      'rgba(54, 162, 235, 0.7)',
      'rgba(255, 206, 86, 0.7)',
      'rgba(75, 192, 192, 0.7)',
      'rgba(153, 102, 255, 0.7)'
    ];
    
    <?php 
      mysqli_data_seek($result_category_sales, 0);
      $count = 0;
      while($category = mysqli_fetch_assoc($result_category_sales)):
        if($count < 5):
    ?>
      categoryLabels.push('<?php echo $category['name']; ?>');
      categoryData.push(<?php echo $category['total_sold']; ?>);
    <?php
        endif;
        $count++;
      endwhile; 
    ?>
    
    var categoryCtx = document.getElementById('categoryChart').getContext('2d');
    var categoryChart = new Chart(categoryCtx, {
      type: 'pie',
      data: {
        labels: categoryLabels,
        datasets: [{
          data: categoryData,
          backgroundColor: categoryColors,
          borderColor: '#ffffff',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'right',
          },
          title: {
            display: true,
            text: 'Sales by Category'
          }
        }
      }
    });
  });
</script>

<?php include 'includes/footer.php'; ?>