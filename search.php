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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Пребарај</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 800px;
            margin: 100px auto;
            text-align: center;
            padding-left: 190px;
        }
        h2 {
            margin-bottom: 30px;
        }
        .btn-container {
            display: inline-block;
            margin-right: 20px;
        }
        .btn {
            padding: 15px 40px;
            font-size: 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-label {
            margin-right: 10px;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="container">
    <h2>Пребарај Сопственик или Возило</h2>
    <div class="btn-container">
        <button id="search_owner" class="btn btn-secondary" onclick="window.location.href='search_owner.php'">
            <span class="btn-label">Пребарај Сопственик</span>
        </button>
    </div>
    <div class="btn-container">
        <button id="search_car" class="btn btn-secondary" onclick="window.location.href='search_car.php'">
            <span class="btn-label">Пребарај Возило</span>
        </button>
    </div>
</div>
</body>
</html>
