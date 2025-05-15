<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="css/admin-layout.css">
    <link rel="stylesheet" href="../css/styles.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin-body">
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="admin-content">
            <!-- Top Navigation -->
            <nav class="admin-topnav">
                <div class="topnav-right">
                    <span class="admin-name">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <a href="<?php echo SITE_URL; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="fas fa-external-link-alt"></i> View Site
                    </a>
                </div>
            </nav>
            
            <!-- Content Container -->
            <div class="content-wrapper">