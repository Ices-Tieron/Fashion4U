<?php
  session_start();
  include '../includes/db_connection.php';
  
  // Check if user is logged in and is admin
  if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php");
    exit();
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>4pm FASHION Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="../css/styles.css">
  <style>
    .admin-sidebar {
      min-height: calc(100vh - 56px);
      background-color: #212529;
    }
    .admin-sidebar .nav-link {
      color: rgba(255, 255, 255, 0.8);
      border-radius: 0;
    }
    .admin-sidebar .nav-link:hover,
    .admin-sidebar .nav-link.active {
      color: #fff;
      background-color: rgba(255, 255, 255, 0.1);
    }
    .admin-content {
      padding: 20px;
    }
    .card-dashboard {
      border-left: 4px solid;
    }
    .card-dashboard.card-primary {
      border-left-color: #0d6efd;
    }
    .card-dashboard.card-success {
      border-left-color: #198754;
    }
    .card-dashboard.card-warning {
      border-left-color: #ffc107;
    }
    .card-dashboard.card-danger {
      border-left-color: #dc3545;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.php">
        <img src="../images/logo.jpg" alt="Fashion Hub" height="30" class="me-2">
        Fashion Hub Admin
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarAdmin">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" href="../index.php" target="_blank">
              <i class="bi bi-box-arrow-up-right"></i> View Site
            </a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle"></i> Admin
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row">
      <div class="col-md-3 col-lg-2 admin-sidebar pt-3 px-0">
        <ul class="nav flex-column">
          <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
              <i class="bi bi-speedometer2"></i> Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" href="products.php">
              <i class="bi bi-box"></i> Products
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>" href="orders.php">
              <i class="bi bi-cart"></i> Orders
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>" href="payments.php">
              <i class="bi bi-credit-card"></i> Payments
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : ''; ?>" href="customers.php">
              <i class="bi bi-people"></i> Customers
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : ''; ?>" href="analytics.php">
              <i class="bi bi-graph-up"></i> Analytics
            </a>
          </li>
        </ul>
      </div>
      
      <div class="col-md-9 col-lg-10 admin-content">