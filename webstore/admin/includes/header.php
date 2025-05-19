<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
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

    <style>
    .admin-body {
        min-width: 320px;
        overflow-x: hidden;
    }

    .admin-container {
        display: flex;
        min-height: 100vh;
        position: relative;
    }

    .admin-content {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
    }

    .admin-topnav {
        background: #fff;
        padding: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: sticky;
        top: 0;
        z-index: 999;
        min-height: 60px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .topnav-right {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-left: auto;
        flex-wrap: nowrap;
    }

    .admin-name {
        font-size: 0.9rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 200px;
    }

    .btn-outline-primary {
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        min-width: fit-content;
    }

    /* Responsive adjustments */
    @media (max-width: 1200px) {
        .admin-name {
            max-width: 150px;
        }
    }

    @media (max-width: 768px) {
        .admin-topnav {
            padding: 0.75rem;
        }

        .admin-name {
            max-width: 120px;
            font-size: 0.85rem;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    }

    @media (max-width: 480px) {
        .admin-name {
            max-width: 100px;
        }

        .topnav-right {
            gap: 0.5rem;
        }
    }

    /* High DPI screen support */
    @media screen and (-webkit-min-device-pixel-ratio: 2), 
           screen and (min-resolution: 192dpi) {
        .admin-topnav {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    }

    /* Zoom support */
    @media screen and (min-resolution: 1dppx) {
        .admin-container {
            min-width: 320px;
        }

        .admin-content {
            min-width: 280px;
        }
    }
    </style>
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