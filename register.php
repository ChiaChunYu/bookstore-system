<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $host = 'localhost';
    $user = 'root';
    $password = '411021390';
    $database = 'OnlineBookstore';
    $port = 3307;

    $conn = new mysqli($host, $user, $password, $database, $port);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $name = $_POST['name'];
    $account = $_POST['account'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $address = $_POST['address'];

    $check_query = "SELECT * FROM User WHERE Account = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param('s', $account);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<p style='color: red;'>This account is already registered.</p>";
    } else {
        $query = "INSERT INTO User (Name, Account, Password, Address) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ssss', $name, $account, $password, $address);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Registration successful. You can now <a href='login.php'>login</a>.</p>";
        } else {
            echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
        }
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="/static/register.css" />
</head>

<body>
    <div class="container">
        <h1>Register</h1>
        <form action="" method="post">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
            <label for="account">Account:</label>
            <input type="text" id="account" name="account" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <label for="address">Address:</label>
            <input type="text" id="address" name="address" required>
            <input type="submit" value="Register">
        </form>
    </div>
</body>

</html>
