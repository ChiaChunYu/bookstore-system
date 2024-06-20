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

$search_query = "";
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
    $query = "SELECT * FROM Book WHERE Title LIKE ? OR Author LIKE ? OR Genre LIKE ? OR ISBN LIKE ?";
    $stmt = $conn->prepare($query);
    $like_search_query = '%' . $search_query . '%';
    $stmt->bind_param('ssss', $like_search_query, $like_search_query, $like_search_query, $like_search_query);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $query = "SELECT * FROM Book";
    $result = $conn->query($query);
}

if (isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        echo "<p style='color: red;'>Please log in to add to cart.</p>";
    } else {
        $user_id = $_SESSION['user_id'];
        $book_id = $_POST['book_id'];

        $query = "SELECT OrderID FROM `Order` WHERE UserID = ? AND OrderStatus = 'Pending'";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result_check = $stmt->get_result();

        if ($result_check->num_rows > 0) {
            $row = $result_check->fetch_assoc();
            $order_id = $row['OrderID'];
        } else {
            $order_date = date('Y-m-d H:i:s');
            $query = "INSERT INTO `Order` (UserID, OrderDate, TotalPrice, OrderStatus) VALUES (?, ?, 0, 'Pending')";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('is', $user_id, $order_date);
            $stmt->execute();
            $order_id = $stmt->insert_id;
        }

        $query = "SELECT * FROM OrderItem WHERE OrderID = ? AND BookID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $order_id, $book_id);
        $stmt->execute();
        $result_check_item = $stmt->get_result();

        if ($result_check_item->num_rows > 0) {
            $query = "UPDATE OrderItem SET Quantity = Quantity + 1 WHERE OrderID = ? AND BookID = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ii', $order_id, $book_id);
        } else {
            $query = "INSERT INTO OrderItem (OrderID, BookID, Quantity, Price) VALUES (?, ?, 1, (SELECT Price FROM Book WHERE BookID = ?))";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('iii', $order_id, $book_id, $book_id);
        }

        if ($stmt->execute()) {
            $query = "UPDATE `Order` SET TotalPrice = (SELECT SUM(Price * Quantity) FROM OrderItem WHERE OrderID = ?) WHERE OrderID = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ii', $order_id, $order_id);
            $stmt->execute();

            echo "<p style='color: green;'>Book added to cart successfully.</p>";
        } else {
            echo "<p style='color: red;'>Error adding book to cart: " . $conn->error . "</p>";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Bookstore</title>
    <link rel="stylesheet" href="/static/index.css" />
</head>
<body>
    <div class="container">
        <h1>Online Bookstore</h1>
        <?php if (isset($_SESSION['admin_id'])): ?>
            <nav>
                <a href="admin_interface.php">Admin Interface</a> | 
                <a href="admin_order.php">View Orders</a> |
                <a href="logout.php">Logout</a>
            </nav>
        <?php elseif (isset($_SESSION['user_id'])): ?>
            <nav>
                <a href="cart.php">Cart</a> | 
                <a href="order.php">Orders</a> |
                <a href="profile.php">Profile</a> | 
                <a href="logout.php">Logout</a>
            </nav>
        <?php else: ?>
            <nav>
                <a href="login.php">User Login</a> | 
                <a href="register.php">Register</a> | 
                <a href="admin_login.php">Admin Login</a>
            </nav>
        <?php endif; ?>

        <form action="index.php" method="get">
            <input type="text" name="search" placeholder="Search for books" value="<?php echo htmlspecialchars($search_query); ?>">
            <input type="submit" value="Search">
        </form>

        <h2>Available Books</h2>
        <?php if ($result->num_rows > 0): ?>
            <div class="books">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="book">
                        <h3><?php echo htmlspecialchars($row['Title']); ?></h3>
                        <p><strong>Author:</strong> <?php echo htmlspecialchars($row['Author']); ?></p>
                        <p><strong>Genre:</strong> <?php echo htmlspecialchars($row['Genre']); ?></p>
                        <p><strong>ISBN:</strong> <?php echo htmlspecialchars($row['ISBN']); ?></p>
                        <p><strong>Price:</strong> $<?php echo htmlspecialchars($row['Price']); ?></p>
                        <p><strong>Stock Quantity:</strong> <?php echo htmlspecialchars($row['StockQuantity']); ?></p>
                        <form action="index.php" method="post">
                            <input type="hidden" name="book_id" value="<?php echo $row['BookID']; ?>">
                            <input type="submit" name="add_to_cart" value="Add to Cart">
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No books available.</p>
        <?php endif; ?>
    </div>
</body>
</html>
