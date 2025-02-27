<?php
session_start();
require_once('../config.php');

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Check if the ID parameter is set
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Prepare and execute the delete statement
    $stmt = $conn->prepare("DELETE FROM cars WHERE car_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirect back to view_records.php after successful deletion
        header("Location: view_records.php");
        exit();
    } else {
        echo "Error deleting record: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid record ID";
}
?>
