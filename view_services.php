<?php
session_start();
require_once('../config.php');

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Set default query parameters
$search_plate = isset($_GET['search_plate']) ? '%' . $_GET['search_plate'] . '%' : '';
$showAll = isset($_POST['show_all']) && $_POST['show_all'] == 'true';

// Pagination variables
$recordsPerPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $recordsPerPage;

// Handle search by registration plate
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    header("Location: view_services.php?search_plate=" . urlencode($_POST['search_plate']));
    exit();
}

// Fetch services based on search or show all
if (!empty($search_plate)) {
    // Handle search logic
    $searchStmt = $conn->prepare("SELECT * FROM services WHERE registration_plate LIKE ? ORDER BY service_date DESC LIMIT ?, ? ");
    $searchStmt->bind_param("sii", $search_plate, $offset, $recordsPerPage);
    $searchStmt->execute();
    $servicesResult = $searchStmt->get_result();

    // Count total records for pagination
    $totalRecordsStmt = $conn->prepare("SELECT COUNT(*) AS total FROM services WHERE registration_plate LIKE ?");
    $totalRecordsStmt->bind_param("s", $search_plate);
    $totalRecordsStmt->execute();
    $totalRecordsResult = $totalRecordsStmt->get_result();
    $totalRecords = $totalRecordsResult->fetch_assoc()['total'];
} elseif (isset($_GET['unpaid']) && $_GET['unpaid'] == 'true') {
    // Fetch unpaid services
    $unpaidStmt = $conn->prepare("SELECT * FROM services WHERE is_paid = 0 ORDER BY service_date DESC LIMIT ?, ? ");
    $unpaidStmt->bind_param("ii", $offset, $recordsPerPage);
    $unpaidStmt->execute();
    $servicesResult = $unpaidStmt->get_result();

    // Count total unpaid records for pagination
    $totalUnpaidRecordsStmt = $conn->query("SELECT COUNT(*) AS total FROM services WHERE is_paid = 0");
    $totalRecords = $totalUnpaidRecordsStmt->fetch_assoc()['total'];
} elseif (isset($_GET['paid']) && $_GET['paid'] == 'true') {
    // Fetch paid services
    $paidStmt = $conn->prepare("SELECT * FROM services WHERE is_paid = 1 ORDER BY service_date DESC LIMIT ?, ? ");
    $paidStmt->bind_param("ii", $offset, $recordsPerPage);
    $paidStmt->execute();
    $servicesResult = $paidStmt->get_result();

    // Count total paid records for pagination
    $totalPaidRecordsStmt = $conn->query("SELECT COUNT(*) AS total FROM services WHERE is_paid = 1");
    $totalRecords = $totalPaidRecordsStmt->fetch_assoc()['total'];
} else {
    // Fetch all services if "Show All Services" button is clicked or no search term is provided
    $allServicesStmt = $conn->prepare("SELECT * FROM services ORDER BY service_date DESC LIMIT ?, ? ");
    $allServicesStmt->bind_param("ii", $offset, $recordsPerPage);
    $allServicesStmt->execute();
    $servicesResult = $allServicesStmt->get_result();

    // Count total records for pagination
    $totalRecordsStmt = $conn->query("SELECT COUNT(*) AS total FROM services");
    $totalRecords = $totalRecordsStmt->fetch_assoc()['total'];
}

$totalPages = ceil($totalRecords / $recordsPerPage);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Преглед Сервиси</title>
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

        .table th,
        .table td {
            text-align: center;
        }

        .badge {
            font-size: 14px;
            padding: 8px 12px;
            width: 100px; /* Set a fixed width for all badges */
            text-align: center; /* Center the content within the badge */
        }

        .action-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pdf-button {
            white-space: nowrap;
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

        /* Additional CSS for buttons */
        .btn-search {
            width: auto; /* Set width to auto for search button */
        }

        .btn-group {
            margin-top: 10px; /* Adjust margin to separate buttons */
        }

        @media (max-width: 576px) {
            .btn-group {
                margin-top: 0; /* Adjust margin for small screens */
            }
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Преглед Сервиси</h2>

        <!-- Search Form -->
        <form action="view_services.php" method="GET" class="mb-3">
            <div class="form-group d-flex justify-content-between align-items-center">
                <input type="text" class="form-control mr-2" id="searchPlate" name="search_plate" placeholder="Внеси табличка">
                <button type="submit" name="search" class="btn btn-secondary btn-search">Пребарај</button>
            </div>
        </form>

        <!-- Button Group for showing unpaid/paid services -->
        <div class="btn-group ml-auto" style="float:right; display: flex; align-items: center;">
            <label for="showOnly" class="mr-1 mb-0" style="margin-top: 2px;">Прикажи само:</label> <!-- Added label -->
            <a href="view_services.php?unpaid=true" class="btn btn-warning mr-2">Неплатени сервиси</a>
            <a href="view_services.php?paid=true" class="btn btn-success">Платени сервиси</a>
        </div>
        <br><br>
        

        <!-- Display services -->
        <h4 class="mb-3">Сите Сервиси</h4>
        <?php if ($servicesResult->num_rows > 0) : ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Табличка</th>
                        <th>Датум на сервис</th>
                        <th>Километража</th>
                        <th>Статус Плаќање</th>
                        <th>Статус Сервис</th>
                        <th>Опции</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($service = $servicesResult->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo $service['registration_plate']; ?></td>
                            <td><?php echo (new DateTime($service['service_date']))->format('d.m.Y H:i'); ?></td>
                            <td><?php echo $service['current_mileage']; ?></td>
                            <td>
                                    <?php if ($service['is_paid'] == 1) : ?>
                                        <span class='badge badge-success mr-1' style='min-width: 80px; display: inline-block;'>Платено</span>
                                    <?php elseif ($service['is_paid'] == 0) : ?>
                                        <span class='badge badge-danger mr-1' style='min-width: 80px; display: inline-block;'>Неплатено</span>
                                    <?php endif; ?>
                            </td>
                            <td><?php 
                            if($service['service_status'] == 1){
                                echo "<span class='badge badge-danger'>Затворен</span>";
                            } else {
                                echo "<span class='badge badge-success'>Отворен</span>";
                            }
                        ?></td>
                            <td>
                                <a href="view_service.php?service_id=<?php echo $service['service_id']; ?>" class="btn btn-primary">Преглед</a>
                                <a href='delete_service.php?service_id=<?php echo $service['service_id']; ?>' onclick='return confirm("Дали си сигурен дека сакаш да го избришеш овој сервис?")' class='btn btn-danger mr-3'>Избриши</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <!-- Pagination -->
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                        <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                            <a class="page-link" href="view_services.php?page=<?php echo $i . ($search_plate ? '&search_plate=' . urlencode($_GET['search_plate']) : '') . (isset($_GET['unpaid']) ? '&unpaid=true' : '') . (isset($_GET['paid']) ? '&paid=true' : ''); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php else : ?>
            <p>Нема пронајдени сервиси.</p>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.8/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>


</html>
