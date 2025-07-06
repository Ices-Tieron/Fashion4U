<?php
  include 'includes/header.php';
  
  // Get all customers (non-admin users)
  $query = "SELECT * FROM users WHERE is_admin = 0 ORDER BY created_at DESC";
  $result = mysqli_query($conn, $query);
  
  // Handle query errors
  if (!$result) {
    echo '<div class="alert alert-danger">Error executing query: ' . mysqli_error($conn) . '</div>';
  }
?>

<h1 class="h2 mb-4">Customers</h1>

<div class="card shadow mb-4">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered table-hover" id="customersTable" width="100%" cellspacing="0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Location</th>
            <th>Registered</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while($user = mysqli_fetch_assoc($result)): ?>
              <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo $user['username']; ?></td>
                <td><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></td>
                <td><?php echo $user['email']; ?></td>
                <td><?php echo $user['phone'] ? $user['phone'] : 'N/A'; ?></td>
                <td>
                  <?php 
                    $location = [];
                    if (!empty($user['city'])) $location[] = $user['city'];
                    if (!empty($user['state'])) $location[] = $user['state'];
                    echo !empty($location) ? implode(', ', $location) : 'N/A'; 
                  ?>
                </td>
                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                <td>
                  <a href="customer-details.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info">
                    <i class="bi bi-eye"></i> View
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center">No customers found</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable for better search and pagination
    try {
      $('#customersTable').DataTable({
        order: [[6, 'desc']], // Sort by registration date column (index 6) in descending order
      });
    } catch (e) {
      console.error('Error initializing DataTable:', e);
    }
  });
</script>

<?php include 'includes/footer.php'; ?>