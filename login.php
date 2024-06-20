<?php
session_start();

$host = 'localhost';
$user = 'root';
$password = '411021390';
$database = 'OnlineBookstore';
$port = 3307;

$conn = new mysqli($host, $user, $password, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $account = $_POST['account'];
    $password = $_POST['password'];

    $query = "SELECT UserID, Password FROM User WHERE Account = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $account);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            header('Location: index.php');
            exit();
        } else {
            echo "<p style='color: red;'>Incorrect password.</p>";
        }
    } else {
        echo "<p style='color: red;'>Account not found.</p>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="/static/login.css" />
</head>

<body>
    <div class="container">
        <h1>User Login</h1>
        <form action="" method="post">
            <label for="account">Account:</label>
            <input type="text" id="account" name="account" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <input type="submit" value="Login">
        </form>
    </div>
</body>

</html>