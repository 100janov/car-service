<?php
session_start();
require_once('../config.php');

// Check if the user is logged in
if (!isset($_SESSION['admin'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

// Validate form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['service_id']) && isset($_POST['confirm_close'])) {
    // Extract submitted data
    $service_id = $_POST['service_id'];

    // Check if all work done are paid (you may need to adjust this logic based on your requirements)
    $allPaid = true;
    // Perform necessary checks here

    // If all work done are paid and confirmation is received, update service status
    if ($allPaid && $_POST['confirm_close'] == "yes") {
        $updateServiceStatusStmt = $conn->prepare("UPDATE services SET service_status = 1 WHERE service_id = ?");
        $updateServiceStatusStmt->bind_param("i", $service_id);
        $updateServiceStatusStmt->execute();
        $updateServiceStatusStmt->close();
        
        // Redirect or output success message as needed
        // header("Location: some_page.php");
        // echo "Service closed successfully.";
        exit("Service closed successfully."); // If you're not redirecting
    } else {
        // Redirect or output error message as needed
        // header("Location: error_page.php");
        // echo "Error: Service not closed.";
        exit("Error: Service not closed.");
    }
} else {
    header("HTTP/1.1 400 Bad Request");
    exit();
}
?>