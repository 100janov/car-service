<?php
session_start();
require_once('../config.php');

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Retrieve the record ID from the URL
$recordId = $_GET['record_id'];

// Initialize variable to track whether the form is submitted
$isSubmitted = false;

// Fetch the record from the database using $recordId
$sql = "SELECT registration_plate, owner_name, mobile_phone FROM cars WHERE car_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Error in prepare statement: ' . $conn->error);
}

$stmt->bind_param('i', $recordId);
$stmt->execute();

if ($stmt->error) {
    die('Error in query: ' . $stmt->error);
}

$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Update record in the database if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $registrationPlate = $_POST['registration_plate'];
    $ownerName = $_POST['owner_name'];
    $mobilePhone = $_POST['mobile_phone'];

    // Update the record in the database
    $updateSql = "UPDATE cars SET registration_plate = ?, owner_name = ?, mobile_phone = ? WHERE car_id = ?";
    $updateStmt = $conn->prepare($updateSql);

    if (!$updateStmt) {
        die('Error in prepare statement: ' . $conn->error);
    }

    $updateStmt->bind_param('sssi', $registrationPlate, $ownerName, $mobilePhone, $recordId);
    $updateStmt->execute();

    if ($updateStmt->error) {
        die('Error in update query: ' . $updateStmt->error);
    }

    // Set the flag to true to show the modal
    $isSubmitted = true;

    // Redirect to the same page to trigger a refresh
    header("Location: {$_SERVER['REQUEST_URI']}");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Измени Возило</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-3">Измени Возило</h2>
        <form method="post">
            <div class="form-group">
                <label for="registration_plate">Регистарска Табличка:</label>
                <input type="text" class="form-control" id="registration_plate" name="registration_plate" value="<?php echo $row['registration_plate']; ?>" required>
            </div>
            <div class="form-group">
                <label for="owner_name">Сопственик:</label>
                <input type="text" class="form-control" id="owner_name" name="owner_name" value="<?php echo $row['owner_name']; ?>" required>
            </div>
            <div class="form-group">
                <label for="mobile_phone">Телефон:</label>
                <input type="text" class="form-control" id="mobile_phone" name="mobile_phone" value="<?php echo $row['mobile_phone']; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary" data-toggle='modal' data-target='#successModal'>Зачувај</button>
        </form>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">Success</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    The fields have been successfully updated.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap and jQuery libraries -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Show success modal if form is submitted -->
    <?php if ($isSubmitted) : ?>
    <script>
        $(document).ready(function() {
            $('#successModal').modal('show');
        });
    </script>
    <?php endif; ?>
</body>
</html>
