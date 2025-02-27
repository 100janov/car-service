<?php
session_start();
date_default_timezone_set('Europe/Berlin'); // Replace 'Your/Timezone' with the desired time zone, for example, 'Europe/Berlin'

require_once('../config.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Ensure a service ID is provided in the URL
if (!isset($_GET['service_id'])) {
    header("Location: index.php"); // Redirect to the main admin page if no service ID is provided
    exit();
}

$service_id = intval($_GET['service_id']);

// Retrieve the details of the specific service
$stmt = $conn->prepare("SELECT services.*, cars.make, cars.model, cars.year, cars.engine FROM services
                        INNER JOIN cars ON services.car_id = cars.car_id
                        WHERE services.service_id = ?");

if (!$stmt) {
    die('Error in prepare statement: ' . $conn->error);
}

$stmt->bind_param("i", $service_id);
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
    <title>Преглед Сервис</title>
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
        <h2 class="text-center mb-4">Преглед Сервис</h2>

        <!-- Display car details -->
        <table class="table">
            <thead>
                <tr>
                    <th>Производител</th>
                    <th>Модел</th>
                    <th>Табличка</th>
                    <th>Година</th>
                    <th>Мотор</th>
                    <th>Километража</th>
                    <th>За Плаќање</th>
                    <th>Статус Плаќање</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?php echo $record['make']; ?>
                    </td>
                    <td>
                        <?php echo $record['model']; ?>
                    </td>
                    <td>
                        <?php echo $record['registration_plate']; ?>
                    </td>
                    <td>
                        <?php echo $record['year']; ?>
                    </td>
                    <td>
                        <?php echo $record['engine']; ?>
                    </td>
                    <td>
                        <?php echo $record['current_mileage']; ?>
                    </td>
                    <td>
                        <?php
                        $workDetailsStmt = $conn->prepare("SELECT * FROM work_details WHERE car_id = ? and service_id = ?");
                        $workDetailsStmt->bind_param("ii", $record['car_id'], $record['service_id']);
                        $workDetailsStmt->execute();
                        $workDetailsResult = $workDetailsStmt->get_result();
                        // Loop through work done details and display the sum of unpaid amounts
                        $totalAmount = 0;
                        while ($workDetails = $workDetailsResult->fetch_assoc()) {
                            if (!isset($workDetails['is_paid']) || $workDetails['is_paid'] == 0) {
                                // Only add the amount if it is unpaid
                                $totalAmount += $workDetails['amount_due'];
                            }
                        }
                        echo $totalAmount, ' ден.';
                        $workDetailsStmt->close();
                        ?>
                    </td>
                    <td>
                        <?php
                        $allWorkDonePaid = true;
                        $workDetailsStmt = $conn->prepare("SELECT is_paid FROM work_details WHERE car_id = ? AND service_id = ?");
                        $workDetailsStmt->bind_param("ii", $record['car_id'], $record['service_id']);
                        $workDetailsStmt->execute();
                        $workDetailsResult = $workDetailsStmt->get_result();

                        // Loop through work done details and check if all are paid
                        while ($workDetails = $workDetailsResult->fetch_assoc()) {
                            if ($workDetails['is_paid'] == 0) {
                                $allWorkDonePaid = false;
                                break;
                            }
                        }

                        $workDetailsStmt->close();

                        // Update the is_paid status in the cars table
                        $updatePaidStatusStmt = $conn->prepare("UPDATE cars SET is_paid = ? WHERE car_id = ?");
                        $updatePaidStatusStmt->bind_param("ii", $allWorkDonePaid, $record['car_id']);
                        $updatePaidStatusStmt->execute();
                        $updatePaidStatusStmt->close();

                        // Update the is_paid status in the services table
                        $updatePaidStatusServicesStmt = $conn->prepare("UPDATE services SET is_paid = ? WHERE service_id = ?");
                        $updatePaidStatusServicesStmt->bind_param("ii", $allWorkDonePaid, $record['service_id']);
                        $updatePaidStatusServicesStmt->execute();
                        $updatePaidStatusServicesStmt->close();

                        $paidStatus = ($allWorkDonePaid) ? 'Платено' : 'Неплатено';
                        $badgeClass = ($allWorkDonePaid) ? 'badge-success' : 'badge-danger';
                        echo "<span class='badge {$badgeClass}'>{$paidStatus}</span>";
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Display work done details -->
        <!-- Collapsible Work Done Details -->
        <div class="mt-4 mb-md-3">
            <div class="accordion" id="workDoneAccordion">
                <div class="card">



                    <form id="workDoneForm" method="POST">
                        <input type="hidden" name="service_id" value="<?php echo $record['service_id']; ?>">
                        <input type="hidden" name="record_id" value="<?php echo $record['car_id']; ?>">
                        <table class="table" id="workDoneTable">
                            <thead>
                                <tr>
                                    <th>Изработено</th>
                                    <th>Сума (денари)</th>
                                    <th>Статус Плаќање</th>
                                    <th>Датум</th> <!-- Added column for Timestamp -->
                                    <th>Опции</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $workDetailsStmt = $conn->prepare("SELECT * FROM work_details WHERE car_id = ? AND service_id = ?");
                                $workDetailsStmt->bind_param("ii", $record['car_id'], $record['service_id']);
                                $workDetailsStmt->execute();
                                $workDetailsResult = $workDetailsStmt->get_result();

                                // Loop through work done details and display them
                                while ($workDetails = $workDetailsResult->fetch_assoc()) {
                                    $paymentStatus = (isset($workDetails['is_paid']) && $workDetails['is_paid'] == 1) ? 'Paid' : 'Unpaid';
                                    $paymentBadgeClass = (isset($workDetails['is_paid']) && $workDetails['is_paid'] == 1) ? 'badge-success' : 'badge-danger';

                                    // Check if the payment is 'Paid' and disable fields accordingly
                                    $disabledAttribute = ($paymentStatus == 'Paid') ? 'disabled' : '';

                                    echo "<tr data-work-detail-id='{$workDetails['id']}'>
                                        <td><input type='text' name='work_done[]' class='form-control' value='{$workDetails['work_done']}' {$disabledAttribute}></td>
                                        <td><input type='number' name='amount_due[]' class='form-control' value='{$workDetails['amount_due']}' {$disabledAttribute}></td>
                                        <td>
                                            <select class='form-control' name='payment_status[]' {$disabledAttribute}>
                                                <option value='0' " . (($paymentStatus == 'Unpaid') ? 'selected' : '') . ">Неплатено</option>
                                                <option value='1' " . (($paymentStatus == 'Paid') ? 'selected' : '') . ">Платено</option>
                                            </select>
                                        </td>
                                        <td>" . date('d.m.Y H:i', strtotime($workDetails['timestamp'])) . "</td> <!-- Display formatted timestamp -->
                                        <td><button type='button' class='btn btn-danger' onclick='removeRow(this)' {$disabledAttribute} data-toggle='modal' data-target='#deleteConfirmationModal'>Избриши</button></td>
                                    </tr>";
                                }

                                // Form to add more work done details
                                echo "<tr id='workRowTemplate' style='display: none;'>
                                    <td><input type='text' name='work_done[]' class='form-control' placeholder='Изработено'></td>
                                    <td><input type='number' name='amount_due[]' class='form-control' placeholder='Сума'></td>
                                    <td>
                                        <select class='form-control' name='payment_status[]'>
                                            <option value='1'>Платено</option>
                                            <option value='0'>Неплатено</option>
                                        </select>
                                    </td>
                                    <td>" . date('d.m.Y H:i') . "</td> <!-- Display current timestamp -->
                                    <td><button type='button' class='btn btn-danger' onclick='removeRow(this)' data-toggle='modal' data-target='#deleteConfirmationModal'>Избриши</button></td>
                                </tr>";
                                ?>
                            </tbody>
                        </table>

                        <!-- Bootstrap Modal for Delete Confirmation -->
                        <div class="modal fade" id="deleteConfirmationModal" tabindex="-1"
                            aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteConfirmationModalLabel">Потврда</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        Дали сте сигурни дека сакате да го избришете овој ред?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-dismiss="modal">Откажи</button>
                                        <button type="button" class="btn btn-danger"
                                            id="confirmDeleteButton">Избриши</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="row justify-content-center mt-3">
                            <div class="col-md-4">
                                <button id="addWorkDoneButton" type="button" class="btn btn-success btn-block"
                                    onclick="addWorkRow()">Додај ново поле</button>
                            </div>

                            <div class="col-md-4">
                                <button id="submitWorkDoneButton" type="button" class="btn btn-success btn-block" <?php echo ($allWorkDonePaid) ? 'disabled' : ''; ?> onclick="submitWorkDone()">Потврди
                                    Изработено</button>
                            </div>

                            <!-- Success Modal -->
                            <div class="modal fade" id="successModal" tabindex="-1" role="dialog"
                                aria-labelledby="successModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="successModalLabel">Успешно направивте измена!
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class='modal-footer justify-content-center'>
                                            <button type="button" class="btn btn-primary"
                                                onclick="refreshPage()">OK</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <button id="closeServiceButton" type="button" class="btn btn-danger btn-block"
                                    onclick="closeService(<?php echo $record['service_id']; ?>)">Комплетирај
                                    сервис</button>
                            </div>



                            <?php
                            // Check if there are any records in work_details for the current service_id
                            $workDetailsTotalStmt = $conn->prepare("SELECT COUNT(*) AS num_records FROM work_details WHERE service_id = ?");
                            $workDetailsTotalStmt->bind_param("i", $record['service_id']);
                            $workDetailsTotalStmt->execute();
                            $workDetailsTotalResult = $workDetailsTotalStmt->get_result();
                            $workDetailsTotalCount = $workDetailsTotalResult->fetch_assoc()['num_records'];

                            if ($workDetailsTotalCount > 0) {
                                // If there are records, display the button
                                echo '<div class="mt-4 text-center">
                                        <a href="generate_pdf_service.php?service_id=' . $record['service_id'] . '&car_id=' . $record['car_id'] . '" class="btn btn-primary pdf-button">Креирај PDF</a>
                                      </div>';
                            }
                            ?>

                        </div>
                    </form>



                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript to handle adding and removing work done rows -->
    <script>
        // Check service status before disabling the "Add Work Done" button
        if (<?php echo $record['service_status']; ?> === 1) {
            document.getElementById('addWorkDoneButton').disabled = true;
            document.getElementById('closeServiceButton').disabled = true;
        }
        function addWorkRow() {
            var templateRow = document.getElementById('workRowTemplate');
            var newRow = templateRow.cloneNode(true);
            newRow.removeAttribute('id');
            newRow.style.display = 'table-row';
            document.getElementById('workDoneTable').getElementsByTagName('tbody')[0].appendChild(newRow);

            // Enable the submit button after adding a new work row
            document.getElementById('submitWorkDoneButton').removeAttribute('disabled');
        }

        function removeRow(button) {
            var row = button.closest('tr');
            var workDetailId = row.getAttribute('data-work-detail-id');

            if (workDetailId) {
                // Show Bootstrap modal for confirmation
                $('#deleteConfirmationModal').modal('show');

                // Set event listener for delete confirmation button click
                $('#confirmDeleteButton').on('click', function () {
                    // Send AJAX request to delete the row from the database
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'delete_work_row.php', true);
                    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            // If deletion was successful, remove the row from the table
                            row.parentNode.removeChild(row);
                            $('#deleteConfirmationModal').modal('hide'); // Hide modal after deletion
                            window.location.reload(true);
                        } else {
                            alert('Error deleting row. Please try again.');
                        }
                    };
                    xhr.send('work_detail_id=' + workDetailId);
                });
            } else {
                console.error("Work detail ID not found.");
            }
        }


        // New function to handle AJAX submission
        function submitWorkDone() {
            var form = document.getElementById('workDoneForm');
            var formData = new FormData(form);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'submit_work_done.php', true);

            xhr.onload = function () {
                if (xhr.status === 200) {
                    // Update the work details table with the received HTML
                    document.getElementById('workDoneTable').innerHTML = xhr.responseText;
                    // Show a modal with a success message
                    $('#successModal').modal('show');
                } else {
                    alert('Error submitting Work Done. Please try again.');
                }
            };

            xhr.onerror = function () {
                alert('Network error. Please try again.');
            };

            xhr.send(formData);
        }

        // Function to refresh the page
        function refreshPage() {
            window.location.reload(true);
        }

        // Handle form submission using AJAX
        document.getElementById('workDoneForm').addEventListener('submit', function (event) {
            event.preventDefault(); // Prevent the default form submission

            var formData = new FormData(this);

            fetch('submit_work_done.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(html => {
                    // Update the work details table with the received HTML
                    document.getElementById('workDoneTable').innerHTML = html;
                    alert('Work Done submitted successfully!');
                })
                .catch(error => {
                    alert('Error submitting Work Done. Please try again.');
                });
        }

        );
    </script>
    <script>
        function closeService(serviceId) {
            // Check if all work done are paid
            var allPaid = true;
            var workDoneRows = document.querySelectorAll('#workDoneTable tbody tr');
            workDoneRows.forEach(function (row) {
                var paymentStatus = row.querySelector('select[name="payment_status[]"]').value;
                if (paymentStatus === '0') {
                    allPaid = false;
                }
            });

            // Display appropriate alert based on payment status
            if (!allPaid) {
                alert('Сите изработки не се платени. Сервисот не може да се затвори!');
                return;
            }

            // If all work done are paid, ask for confirmation before closing the service
            if (confirm('Дали сте сигурни дека сакате да го затворите овој сервис?')) {
                // Disable the "Add Work Done" button
                document.getElementById('submitWorkDoneButton').disabled = true;

                // Send AJAX request to update service status
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'update_service_status.php', true);
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        alert('Сервисот е успешно затворен.');
                        // Optionally, redirect or perform any other action after successful closure
                    } else {
                        alert('Error closing service. Please try again.');
                    }
                };
                xhr.send('service_id=' + serviceId + '&confirm_close=yes');
                window.location.reload(true);
            }
        }
    </script>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>