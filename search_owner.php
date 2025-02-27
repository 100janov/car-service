<?php
session_start();
require_once('../config.php');

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Set default query parameters
$searchTerm = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '';

// Code for viewing records with total amount due
$sql = "SELECT *
        FROM cars
        WHERE owner_name LIKE ?
        GROUP BY car_id";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Error in prepare statement: ' . $conn->error);
}

// Bind parameters
$stmt->bind_param('s', $searchTerm);

// Execute the query
$stmt->execute();

if ($stmt->error) {
    die('Error in query: ' . $stmt->error);
}


$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Пребарај Сопственик</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Add your custom styles here */
        body {
            padding-top: 0px;
        }

        @media (max-width: 576px) {
            body {
                padding-top: 0;
            }
        }

        .table th, .table td {
            text-align: center;
        }

        .badge {
            font-size: 14px;
            padding: 8px 12px;
        }

        .action-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pdf-button {
            white-space: nowrap;
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
    <h2 class="text-center mb-3">Пребарај Сопственик</h2>

    <!-- Search form -->
    <form action="search_owner.php" method="GET" class="form-inline float-right mb-2">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Пребарај по име на сопственик">
            <div class="input-group-append">
                <button type="submit" class="btn btn-secondary">Пребарај</button>
            </div>
        </div>
    </form>

    <!-- Display car workshop data -->
    <div class="table-responsive">
        <table class="table mt-3">
            <thead>
                <tr>
                    <th>Производител</th>
                    <th>Модел</th>
                    <th>Табличка</th>
                    <th>Година</th>
                    <th>Мотор</th>
                    <th>Сопственик</th>
                    <th>Телефон</th>
                    <th>Опции</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo $row['make']; ?></td>
                        <td><?php echo $row['model']; ?></td>
                        <td><?php echo $row['registration_plate']; ?></td>
                        <td><?php echo $row['year']; ?></td>
                        <td><?php echo $row['engine']; ?></td>
                        <td><?php echo $row['owner_name']; ?></td>
                        <td><?php echo $row['mobile_phone']; ?></td>
                        <td class="action-buttons">
                            <a href='view_record.php?record_id=<?php echo $row['car_id']; ?>' class='btn btn-primary'>Преглед</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.8/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
