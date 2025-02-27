<?php
session_start();
require_once('../config.php');

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Check if the ID parameter is set
if (isset($_GET['service_id'])) {
    $id = intval($_GET['service_id']);

    // Prepare and execute the delete statement
    $stmt = $conn->prepare("DELETE FROM services WHERE service_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirect back to view_services.php after successful deletion
        header("Location: view_services.php");
        exit();
    } else {
        echo "Error deleting service: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid service ID";
}
?>
