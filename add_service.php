<?php
session_start();
require_once('../config.php');

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch all cars for the dropdown
$carsStmt = $conn->prepare("SELECT car_id, registration_plate FROM cars");
$carsStmt->execute();
$carsResult = $carsStmt->get_result();
$carsStmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the form is submitted
    if (isset($_POST['create_service'])) {
        $registration_plate = $_POST['registration_plate'];

        // Check if the registration plate exists in the database
        $carInfoStmt = $conn->prepare("SELECT car_id FROM cars WHERE registration_plate = ?");
        $carInfoStmt->bind_param("s", $registration_plate);
        $carInfoStmt->execute();
        $carInfoResult = $carInfoStmt->get_result();
        if ($carInfoResult->num_rows === 0) {
            // Display modal dialog if the registration plate is not found
            echo "<script>alert('Внесената табличка не е пронајдена во базата');</script>";
        } else {
            // Insert a new service into the services table with car_id and current_mileage
            $carInfo = $carInfoResult->fetch_assoc();
            $car_id = $carInfo['car_id'];
            $insertServiceStmt = $conn->prepare("INSERT INTO services (car_id, registration_plate, current_mileage, service_date) VALUES (?, ?, ?, NOW())");
            $insertServiceStmt->bind_param("isi", $car_id, $registration_plate, $_POST['current_mileage']);
            $insertServiceStmt->execute();
            $insertedServiceId = $insertServiceStmt->insert_id; // Get the ID of the newly inserted service
            $insertServiceStmt->close();

            // Redirect to the view_service.php page with the newly created service's ID
            header("Location: view_service.php?service_id=" . $insertedServiceId);
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Внеси Сервис</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            padding-top: 0px;
        }

        @media (max-width: 576px) {
            body {
                padding-top: 0;
            }
        }

        .container {
            max-width: 100%;
            padding-right: 40px;
            padding-left: 250px;
            margin-right: auto;
            margin-left: auto;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }

        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }

        .btn-block {
            width: 100%;
        }

        .mt-md-0 {
            margin-top: 0 !important;
        }

        .mb-md-3 {
            margin-bottom: 3rem !important;
        }

        .mt-md-3 {
            margin-top: 3rem !important;
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Внеси Сервис</h2>

    <!-- Form to add a new service -->
    <form action="add_service.php" method="POST">
        <div class="form-group">
            <label for="registrationPlate">Внеси Регистерска Табличка:</label>
            <input type="text" class="form-control" id="registrationPlate" name="registration_plate" required>
        </div>
        <div class="form-group">
            <label for="currentMileage">Моментална Километража:</label>
            <input type="number" class="form-control" id="currentMileage" name="current_mileage" required>
        </div>
        <button type="submit" name="create_service" class="btn btn-secondary btn-block">Креирај нов Сервис</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.8/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
