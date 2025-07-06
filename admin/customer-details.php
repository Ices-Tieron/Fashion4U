<?php
  include 'includes/header.php';
  
  // Check if customer ID is provided
  if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: customers.php");
    exit();
  }
  
  $user_id = mysqli_real_escape_string($conn, $_GET['id']);
  
  // Get customer details
  $query = "SELECT * FROM users WHERE id = $user_id AND is_admin = 0";
  $result = mysqli_query($conn, $query);
  
  if (mysqli_num_rows($result) == 0) {
    header("Location: customers.php");
    exit();
  }
  
  $customer = mysqli_fetch_assoc($result);
  
  // Get customer orders
  $query_orders = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
  $result_orders = mysqli_query($conn, $query_orders);
  
  // Get total spent
  $query_spent = "SELECT SUM(total_amount) as total_spent FROM orders WHERE user_id = $user_id AND payment_status = 'paid'";
  $result_spent = mysqli_query($conn, $query_spent);
  $total_spent = mysqli_fetch_assoc($result_spent)['total_spent'];
  
  // Get customer addresses
  $query_addresses = "SELECT * FROM user_addresses WHERE user_id = $user_id ORDER BY is_default DESC";
  $result_addresses = mysqli_query($conn, $query_addresses);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h2">Customer Details</h1>
  <a href="customers.php" class="btn btn-secondary">
    <i class="bi bi-arrow-left"></i> Back to Customers
  </a>
</div>

<div class="row mb-4">
  <div class="col-lg-4">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Customer Information</h6>
      </div>
      <div class="card-body">
        <div class="text-center mb-4">
          <div class="display-1 text-gray-300 mb-2">
            <i class="bi bi-person-circle"></i>
          </div>
          <h5 class="font-weight-bold"><?php echo $customer['username']; ?></h5>
          <p class="text-muted"><?php echo $customer['email']; ?></p>
        </div>
        
        <table class="table table-borderless">
          <tr>
            <th width="40%">Full Name:</th>
            <td><?php echo $customer['first_name'] . ' ' . $customer['last_name']; ?></td>
          </tr>
          <tr>
            <th>Email:</th>
            <td><?php echo $customer['email']; ?></td>
          </tr>
          <tr>
            <th>Phone:</th>
            <td><?php echo $customer['phone'] ? $customer['phone'] : 'N/A'; ?></td>
          </tr>
          <tr>
            <th>Address:</th>
            <td><?php echo $customer['address'] ? $customer['address'] : 'N/A'; ?></td>
          </tr>
          <tr>
            <th>City:</th>
            <td><?php echo $customer['city'] ? $customer['city'] : 'N/A'; ?></td>
          </tr>
          <tr>
            <th>State:</th>
            <td><?php echo $customer['state'] ? $customer['state'] : 'N/A'; ?></td>
          </tr>
          <tr>
            <th>ZIP:</th>
            <td><?php echo $customer['zip_code'] ? $customer['zip_code'] : 'N/A'; ?></td>
          </tr>
          <tr>
            <th>Member Since:</th>
            <td><?php echo date('F d, Y', strtotime($customer['created_at'])); ?></td>
          </tr>
        </table>
      </div>
    </div>
  </div>
  
  <div class="col-lg-8">
    <div class="row">
      <div class="col-md-6">
        <div class="card card-dashboard card-primary shadow mb-4">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Orders</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo mysqli_num_rows($result_orders); ?></div>
              </div>
              <div class="col-auto">
                <i class="bi bi-cart fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-6">
        <div class="card card-dashboard card-success shadow mb-4">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Spent</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">₵<?php echo number_format($total_spent, 2); ?></div>
              </div>
              <div class="col-auto">
                <i class="bi bi-currency-dollar fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Order History</h6>
      </div>
      <div class="card-body">
        <?php if (mysqli_num_rows($result_orders) > 0): ?>
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Date</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php while($order = mysqli_fetch_assoc($result_orders)): ?>
                  <tr>
                    <td>#<?php echo $order['id']; ?></td>
                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
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
        <?php else: ?>
          <p class="text-center">This customer has not placed any orders yet.</p>
        <?php endif; ?>
      </div>
    </div>
    
    <?php if (mysqli_num_rows($result_addresses) > 0): ?>
      <div class="card shadow mb-4">
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold">Saved Addresses</h6>
        </div>
        <div class="card-body">
          <div class="row">
            <?php while($address = mysqli_fetch_assoc($result_addresses)): ?>
              <div class="col-md-6 mb-3">
                <div class="card">
                  <div class="card-body">
                    <h6 class="card-title">
                      <?php echo $address['address_name']; ?>
                      <?php if($address['is_default']): ?>
                        <span class="badge bg-primary">Default</span>
                      <?php endif; ?>
                    </h6>
                    <p class="card-text">
                      <strong><?php echo $address['recipient_name']; ?></strong><br>
                      <?php echo $address['street_address']; ?><br>
                      <?php echo $address['city'] . ', ' . $address['state'] . ' ' . $address['zip_code']; ?><br>
                      <?php echo $address['country']; ?>
                    </p>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>