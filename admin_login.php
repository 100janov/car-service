<?php
session_start();
require_once('../config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $check_user_query = "SELECT * FROM admin_users WHERE username = ?";
    $stmt = $conn->prepare($check_user_query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        // Verify password using password_verify()
        if (password_verify($password, $user_data['password'])) {
            $_SESSION['admin'] = true;
            header("Location: index.php");
            exit();
        } else {
            $login_error = "Погрешна лозинка";
        }
    } else {
        $login_error = "Погрешно Корисничко име";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Car Service</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            max-width: 400px;
            margin: auto;
            margin-top: 50px;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #007bff;
        }
        
        #logo {
            width: 220px; /* Adjust the width as needed */
            display: block;
            margin: 0 auto; /* Center the logo */
            margin-bottom: 20px; /* Add some space below the logo */
        }

        form {
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            color: #495057;
            font-weight: bold;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <img src="images/logo_black.png" alt="Logo" id="logo">
    <form action="" method="POST">
        <div class="form-group">
            <label for="username">Корисничко име:</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Лозинка:</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Пријави се</button>
        <?php if (isset($login_error)) { ?>
            <div class="alert alert-danger mt-3" role="alert">
                <?php echo $login_error; ?>
            </div>
        <?php } ?>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.8/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>