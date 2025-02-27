<?php
session_start();
date_default_timezone_set('Europe/Berlin');

require_once('../config.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Ensure a record ID is provided in the URL
if (!isset($_GET['record_id'])) {
    header("Location: index.php");
    exit();
}

$record_id = intval($_GET['record_id']);

// Retrieve the details of the specific record
$stmt = $conn->prepare("SELECT * FROM cars WHERE car_id = ?");

if (!$stmt) {
    die('Error in prepare statement: ' . $conn->error);
}

$stmt->bind_param("i", $record_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $record = $result->fetch_assoc();
} else {
    // Redirect to the main admin page if the record ID is not found
    header("Location: index.php");
    exit();
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Преглед Возило</title>
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

        .col-md-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
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
        <h2 class="text-center mb-4">Преглед Возило</h2>

        <!-- Display car details -->
        <table class="table">
            <thead>
                <tr>
                    <th>Производител</th>
                    <th>Модел</th>
                    <th>Табличка</th>
                    <th>Година</th>
                    <th>Мотор</th>
                    <th>Сопственик</th>
                    <th>Телефон</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo $record['make']; ?></td>
                    <td><?php echo $record['model']; ?></td>
                    <td><?php echo $record['registration_plate']; ?></td>
                    <td><?php echo $record['year']; ?></td>
                    <td><?php echo $record['engine']; ?></td>
                    <td><?php echo $record['owner_name']; ?></td>
                    <td><?php echo $record['mobile_phone']; ?></td>
                </tr>
            </tbody>
        </table>

        <!-- View All Services Button -->
        <div class="mt-4 text-center">
            <button id="viewServicesButton" class="btn btn-info">Прикажи сите Сервиси</button>
        </div>
        <br>
        <!-- All Services Section -->
        <div id="servicesSection" style="display: none;">
            <h3 class="text-center mb-3">Сите сервиси за ова возило</h3>
            <table class="table" id="servicesTable">
                <thead>
                    <tr>
                        <th>Датум</th>
                        <th>Километража</th>
                        <th>Статус Плаќање</th>
                        <th>За плаќање</th>
                        <th>Статус Сервис</th>
                        <th>Опции</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $servicesStmt = $conn->prepare("SELECT s.*, 
                                        COALESCE(SUM(CASE WHEN w.is_paid = 0 THEN w.amount_due ELSE 0 END), 0) AS total_due
                                FROM services s
                                LEFT JOIN work_details w ON s.service_id = w.service_id AND w.is_paid = 0
                                WHERE s.car_id = ? 
                                GROUP BY s.service_id
                                ORDER BY s.timestamp DESC");
                    $servicesStmt->bind_param("i", $record_id);
                    $servicesStmt->execute();
                    $servicesResult = $servicesStmt->get_result();

                    while ($service = $servicesResult->fetch_assoc()) {
                        $paymentStatus = ($service['total_due'] > 0) ? 'Неплатено' : 'Платено';
                        $paymentBadgeClass = ($service['total_due'] > 0) ? 'badge-danger' : 'badge-success';

                        echo "<tr>
                                <td>" . date('d.m.Y H:i', strtotime($service['timestamp'])) . "</td>
                                <td>{$service['current_mileage']}</td>
                                <td><span class='badge {$paymentBadgeClass}'>{$paymentStatus}</span></td>
                                <td>{$service['total_due']}</td>
                                <td>";

                                    if ($service['service_status'] == 1) {
                                        echo "<span class='badge badge-success mr-1' style='min-width: 80px; display: inline-block;'>Затворен</span>";
                                    } elseif ($service['service_status'] == 0) {
                                        echo "<span class='badge badge-danger mr-1' style='min-width: 80px; display: inline-block;'>Отворен</span>";
                                    }
                                    
                                    echo "</td>
                                <td><button class='btn btn-secondary viewButton' data-service-id='{$service['service_id']}'>Преглед</button></td>
                            </tr>";
                    }

                    $servicesStmt->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.8/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        document.getElementById('viewServicesButton').addEventListener('click', function () {
            var servicesSection = document.getElementById('servicesSection');
            servicesSection.style.display = (servicesSection.style.display === 'none') ? 'block' : 'none';
        });

        // Add click event for view buttons
        var viewButtons = document.querySelectorAll('.viewButton');
        viewButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var serviceId = button.getAttribute('data-service-id');
                window.location.href = 'view_service.php?service_id=' + serviceId;
            });
        });
    </script>
</body>

</html>
