<?php
session_start();
require_once('../config.php');

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from the form
    $record_id = $_POST['record_id']; // Assuming this is the car_id
    $service_id = $_POST['service_id']; // Add this line to retrieve the service_id
    $work_done_array = $_POST['work_done'];
    $amount_due_array = $_POST['amount_due'];
    $payment_status_array = $_POST['payment_status'];
    $existing_work_detail_ids = $_POST['id'];

    // Remove empty entries
    $work_done_array = array_filter($work_done_array);
    $amount_due_array = array_filter($amount_due_array);

    // Loop through each work detail
    foreach ($work_done_array as $key => $work_done) {
        $amount_due = isset($amount_due_array[$key]) ? $amount_due_array[$key] : 0;
        $payment_status = isset($payment_status_array[$key]) ? $payment_status_array[$key] : 0;

        // Check if the work detail already exists
        $existing_work_detail_id = isset($existing_work_detail_ids[$key]) ? $existing_work_detail_ids[$key] : null;

        if (!empty($work_done)) {
            // Check if a record with the same details already exists
            $checkExistingStmt = $conn->prepare("SELECT id FROM work_details WHERE car_id = ? AND service_id = ? AND work_done = ? AND amount_due = ?");
            if (!$checkExistingStmt) {
                die('Error in prepare statement: ' . $conn->error);
            }
            $checkExistingStmt->bind_param("iiss", $record_id, $service_id, $work_done, $amount_due);
            $checkExistingStmt->execute();
            $checkExistingStmt->store_result();

            if ($checkExistingStmt->num_rows > 0) {
                // Update existing work detail
                $checkExistingStmt->bind_result($existing_work_detail_id);
                $checkExistingStmt->fetch();
                $checkExistingStmt->close();

                $updateStmt = $conn->prepare("UPDATE work_details SET is_paid = ? WHERE id = ?");
                if (!$updateStmt) {
                    die('Error in prepare statement: ' . $conn->error);
                }
                $updateStmt->bind_param("ii", $payment_status, $existing_work_detail_id);
                $updateStmt->execute();

                if ($updateStmt->error) {
                    die('Error in update statement: ' . $updateStmt->error);
                }
                $updateStmt->close();
            } else {
                // Insert a new work detail if it doesn't exist
                $insertStmt = $conn->prepare("INSERT INTO work_details (car_id, service_id, work_done, amount_due, is_paid) VALUES (?, ?, ?, ?, ?)");
                if (!$insertStmt) {
                    die('Error in prepare statement: ' . $conn->error);
                }
                $insertStmt->bind_param("iissi", $record_id, $service_id, $work_done, $amount_due, $payment_status);
                $insertStmt->execute();

                if ($insertStmt->error) {
                    die('Error in insert statement: ' . $insertStmt->error);
                }
                $insertStmt->close();
            }
        }
    }

    // Retrieve the updated work details HTML
    ob_start();
    include('work_details_table.php');
    $workDetailsHTML = ob_get_clean();

    // Send the updated work details HTML as a response
    echo $workDetailsHTML;
    exit();
} else {
    // If the form was not submitted via POST, redirect to the main admin page
    header("Location: index.php");
    exit();
}
?>
