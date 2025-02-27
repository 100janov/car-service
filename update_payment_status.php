<?php
session_start();
require_once('../config.php');

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['record_id']) && isset($_POST['paymentStatus'])) {
    $record_id = intval($_POST['record_id']);
    $payment_status = intval($_POST['paymentStatus']);

    $stmt = $conn->prepare("UPDATE cars SET is_paid = ? WHERE car_id = ?");
    $stmt->bind_param("ii", $payment_status, $record_id);

    if ($stmt->execute()) {
        // Redirect back to view_record.php with the updated payment status
        header("Location: view_record.php?record_id={$record_id}");
        exit();
    } else {
        // Handle update failure
        echo "Failed to update payment status.";
    }

    $stmt->close();
} else {
    // Redirect to admin.php if the form is accessed directly
    header("Location: index.php");
    exit();
}
?>
