<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once('../config.php');

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 215px;
            height: 100%;
            background-color: #1a20d9; /* Blue background */
            padding: 20px;
            color: #fff; /* White text */
            display: flex;
            flex-direction: column;
        }

        .sidebar img {
            width: 100%;
            margin-bottom: 20px;
        }

        .menu-item {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            color: #fff;
            text-decoration: none;
            padding: 8px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .menu-item:hover {
            color: #ccc; /* Lighter text on hover */
        }

        .menu-item i {
            margin-right: 10px;
        }

        /* Highlighted menu item */
        .active {
            background-color: #fff; /* White background */
            color: #1a20d9; /* Orange text */
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <!-- Logo/Image -->
        <img src="images/logo.png" alt="Logo">

        <!-- Menu Items -->
        <a href="index.php" class="menu-item"><i class="fas fa-home"></i>Почетна</a>
        <a href="add_record.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'add_record.php' ? 'active' : ''; ?>"><i class="fa-solid fa-car-side"></i>Внеси возило</a>
        <a href="view_records.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'view_records.php' ? 'active' : ''; ?>"><i class="fa-solid fa-warehouse"></i>Преглед возила</a>
        <a href="add_service.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'add_service.php' ? 'active' : ''; ?>"><i class="fa-solid fa-wrench"></i>Внеси сервис</a>
        <a href="view_services.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'view_services.php' ? 'active' : ''; ?>"><i class="fa-solid fa-tv"></i>Преглед сервиси</a>
        <a href="search.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'search.php' ? 'active' : ''; ?>"><i class="fa-solid fa-magnifying-glass"></i>Пребарај</a>
        <a href="logout.php" class="menu-item"><i class="fa-solid fa-power-off"></i>Одјави се</a>
        <!-- Add more menu items as needed -->
    </div>
</body>
</html>
