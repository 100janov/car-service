<?php
session_start();
require_once('../config.php');

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Статистики</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            max-width: 850px;
            padding: 20px;
            padding-left: 230px;
            text-align: center;
        }

        .card {
            background-color: #fff;
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .card-icon {
            margin-right: 10px;
        }

        .card-title {
            font-size: 18px;
            margin-bottom: 5px;
            text-align: right;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

<div class="container">
    
    <h2 class="text-center mb-4">Статистики</h2>
    
    <!-- Dashboard Section -->
    <div class="row mb-4">
        <div class="col-md-6">
            <?php
            // Fetch total records count from the cars table
            $totalRecordsStmt = $conn->prepare("SELECT COUNT(*) AS total_records FROM cars");
            $totalRecordsStmt->execute();
            $totalRecordsResult = $totalRecordsStmt->get_result();
            $totalRecords = $totalRecordsResult->fetch_assoc()['total_records'];
            ?>
            <div class="card">
                    <div class="card-title">Вкупно Возила</div>
                    <div class="stat-number"><?php echo $totalRecords; ?></div>
                </div>
        </div>
        <div class="col-md-6">
            <?php
            // Fetch total services count from the services table
            $totalServicesStmt = $conn->prepare("SELECT COUNT(*) AS total_services FROM services");
            $totalServicesStmt->execute();
            $totalServicesResult = $totalServicesStmt->get_result();
            $totalServices = $totalServicesResult->fetch_assoc()['total_services'];
            
            // Fetch total closed services count from the services table
            $totalServicesClosedStmt = $conn->prepare("SELECT COUNT(*) AS closed_services FROM services WHERE service_status = 1");
            $totalServicesClosedStmt->execute();
            $totalServicesClosedResult = $totalServicesClosedStmt->get_result();
            $totalServicesClosed = $totalServicesClosedResult->fetch_assoc()['closed_services'];
            ?>
            <div class="card">
                <h3 class="card-title">Затворени Сервиси</h3>
                <div class="stat-number"><?php echo $totalServicesClosed; ?></div>
            </div>
        </div>
        
        <div class="col-md-6">
            <?php
            // Fetch total services count from the services table
            $totalServicesUnpaidStmt = $conn->prepare("SELECT COUNT(*) AS is_paid FROM services WHERE is_paid = 0");
            $totalServicesUnpaidStmt->execute();
            $totalServicesUnpaidResult = $totalServicesUnpaidStmt->get_result();
            $totalServicesUnpaid = $totalServicesUnpaidResult->fetch_assoc()['is_paid'];
            ?>
            <div class="card">
                <h3 class="card-title">Неплатени Сервиси</h3>
                <div class="stat-number"><?php echo $totalServicesUnpaid; ?> од <?php echo $totalServices; ?></div>
            </div>
        </div>
        
        <div class="col-md-6">
            <?php
            // Fetch total services count from the services table
            $totalServicesPaidStmt = $conn->prepare("SELECT COUNT(*) AS is_paid FROM services WHERE is_paid = 1");
            $totalServicesPaidStmt->execute();
            $totalServicesPaidResult = $totalServicesPaidStmt->get_result();
            $totalServicesPaid = $totalServicesPaidResult->fetch_assoc()['is_paid'];
            ?>
            <div class="card">
                <h3 class="card-title">Платени Сервиси</h3>
                <div class="stat-number"><?php echo $totalServicesPaid; ?> од <?php echo $totalServices; ?></div>
            </div>
        </div>
    </div>



</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.8/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

