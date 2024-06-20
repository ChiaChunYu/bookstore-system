<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="/static/profile.css" />
    <script>
        function showField() {
            var field = document.getElementById("field").value;
            document.getElementById("name_field").style.display = "none";
            document.getElementById("password_field").style.display = "none";
            document.getElementById("address_field").style.display = "none";

            if (field === "name") {
                document.getElementById("name_field").style.display = "block";
            } else if (field === "password") {
                document.getElementById("password_field").style.display = "block";
            } else if (field === "address") {
                document.getElementById("address_field").style.display = "block";
            }
        }
    </script>
</head>

<body>
    <div class="container">
        <h1>User Profile</h1>
        <?php
        session_start();

        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit();
        }

        $host = 'localhost';
        $user = 'root';
        $password = '411021390';
        $database = 'OnlineBookstore';
        $port = 3307;

        $conn = new mysqli($host, $user, $password, $database, $port);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $user_id = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $current_password = $_POST['current_password'];
            $query = "SELECT Password FROM User WHERE UserID = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $stmt->bind_result($hashed_password);
            $stmt->fetch();
            $stmt->close();

            if (password_verify($current_password, $hashed_password)) {
                if (!empty($_POST['name'])) {
                    $name = $_POST['name'];
                    $query = "UPDATE User SET Name = ? WHERE UserID = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param('si', $name, $user_id);
                    $stmt->execute();
                    $stmt->close();
                    echo "<p style='color: green;'>Name updated successfully.</p>";
                }

                if (!empty($_POST['new_password'])) {
                    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                    $query = "UPDATE User SET Password = ? WHERE UserID = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param('si', $new_password, $user_id);
                    $stmt->execute();
                    $stmt->close();
                    echo "<p style='color: green;'>Password updated successfully.</p>";
                }

                if (!empty($_POST['address'])) {
                    $address = $_POST['address'];
                    $query = "UPDATE User SET Address = ? WHERE UserID = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param('si', $address, $user_id);
                    $stmt->execute();
                    $stmt->close();
                    echo "<p style='color: green;'>Address updated successfully.</p>";
                }
            } else {
                echo "<p style='color: red;'>Incorrect current password.</p>";
            }
        }

        $query = "SELECT Name, Account, Address FROM User WHERE UserID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->bind_result($name, $account, $address);
        $stmt->fetch();
        ?>

        <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
        <p><strong>Account:</strong> <?php echo htmlspecialchars($account); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($address); ?></p>

        <form action="" method="post">
            <label for="field">Choose a field to update:</label>
            <select id="field" name="field" onchange="showField()">
                <option value="">Select...</option>
                <option value="name">Name</option>
                <option value="password">Password</option>
                <option value="address">Address</option>
            </select>

            <div id="name_field" style="display:none;">
                <label for="name">New Name:</label>
                <input type="text" id="name" name="name" placeholder="Enter new name">
            </div>

            <div id="password_field" style="display:none;">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" placeholder="Enter new password">
            </div>

            <div id="address_field" style="display:none;">
                <label for="address">New Address:</label>
                <input type="text" id="address" name="address" placeholder="Enter new address">
            </div>

            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password" required>
            <input type="submit" value="Update Profile">
        </form>
        <?php
        $stmt->close();
        $conn->close();
        ?>
    </div>
</body>

</html>