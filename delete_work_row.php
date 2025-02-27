<?php
session_start();
require_once('../config.php');

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the ID of the work detail to be deleted
    $work_detail_id = $_POST['work_detail_id'];

    // Check if the ID is valid
    if (!empty($work_detail_id)) {
        // Perform the deletion in the database
        $deleteStmt = $conn->prepare("DELETE FROM work_details WHERE id = ?");
        $deleteStmt->bind_param("i", $work_detail_id);
        
        if ($deleteStmt->execute()) {
            
        } else {
            
        }

        $deleteStmt->close();
    } else {
        echo "Invalid work detail ID.";
    }
} else {
    // If the request method is not POST, redirect to the main admin page
    header("Location: index.php");
    exit();
}
?>
