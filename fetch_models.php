<?php
require_once('../config.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['make'])) {
    $make = $_GET['make'];
    
    // Fetch models from the database based on the selected make
    $stmt = $conn->prepare("SELECT DISTINCT model FROM cars WHERE make = ?");
    $stmt->bind_param("s", $make);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $models = [];
    while ($row = $result->fetch_assoc()) {
        $models[] = $row['model'];
    }
    
    echo json_encode($models);
}
?>
