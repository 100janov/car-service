<?php
session_start();
require_once('../config.php');

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch distinct makes from the cars table
$makeQuery = "SELECT DISTINCT make FROM cars";
$makeResult = $conn->query($makeQuery);

// Set default query parameters
$searchMake = isset($_GET['make']) ? $_GET['make'] : '';
$searchModel = isset($_GET['model']) ? $_GET['model'] : '';

// Code for viewing records with total amount due
$sql = "SELECT *
        FROM cars
        WHERE make = ? AND model = ?
        GROUP BY car_id";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Error in prepare statement: ' . $conn->error);
}

// Bind parameters
$stmt->bind_param('ss', $searchMake, $searchModel);

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
    <title>Пребарај Возило</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .container {
            max-width: 100%;
            padding-right: 40px;
            padding-left: 250px;
            margin-right: auto;
            margin-left: auto;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="container mt-5">
    <h2 class="text-center mb-3">Пребарај Возило</h2>

     <!-- Search form -->
    <form action="" method="GET" class="form-inline mb-2" id="search-form">
        <div class="form-group mr-2">
            <select id="make" class="form-control" name="make" required>
                <option value="">Избери Производител</option>
                <?php while ($row = $makeResult->fetch_assoc()) : ?>
                    <option value="<?php echo $row['make']; ?>" <?php echo ($searchMake == $row['make']) ? 'selected' : ''; ?>><?php echo $row['make']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group mr-2">
            <select id="model" class="form-control" name="model" required>
                <option value="">Избери Модел</option>
                <?php if (!empty($searchModel)) : ?>
                    <option value="<?php echo $searchModel; ?>" selected><?php echo $searchModel; ?></option>
                <?php endif; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary">Пребарај</button>
    </form>

    <!-- Display car data -->
    <div id="car-data" class="table-responsive">
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
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.8/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    $(document).ready(function () {
        $('#make').change(function () {
            var make = $(this).val();
            $.ajax({
                url: 'fetch_models.php',
                type: 'GET',
                data: {make: make},
                dataType: 'json',
                success: function (data) {
                    var options = '<option value="">Избери Модел</option>';
                    $.each(data, function (key, value) {
                        options += '<option value="' + value + '">' + value + '</option>';
                    });
                    $('#model').html(options);
                }
            });
        });
    });
</script>
</body>
</html>
