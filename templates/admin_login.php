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
    $admin_account = $_POST['admin_account'];
    $admin_password = $_POST['admin_password'];

   
    $query = "SELECT AdminID, Password FROM Admin WHERE Account = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $admin_account);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($admin_id, $stored_password);
        $stmt->fetch();

        if ($admin_password === $stored_password) {
            $_SESSION['admin_id'] = $admin_id;
            header('Location: index.php');
            exit();
        } else {
            echo "<p style='color: red;'>Incorrect password.</p>";
        }
    } else {
        echo "<p style='color: red;'>Admin not found.</p>";
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
    <title>Admin Login</title>
    <link rel="stylesheet" href="/static/admin_login.css" />
</head>

<body>
    <div class="container">
        <h1>Admin Login</h1>
        <form action="" method="post">
            <label for="admin_account">Account:</label>
            <input type="text" id="admin_account" name="admin_account" required>
            <label for="admin_password">Password:</label>
            <input type="password" id="admin_password" name="admin_password" required>
            <input type="submit" value="Login">
        </form>
    </div>
</body>

</html>
