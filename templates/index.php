<?php
session_start();

// 連接資料庫
$host = 'localhost';
$user = 'root';
$password = '411021390';  // 請替換為您的 MySQL 密碼
$database = 'OnlineBookstore';
$port = 3307;

$conn = new mysqli($host, $user, $password, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 獲取所有書籍的資料
$query = "SELECT BookID, Title, Author, Genre, ISBN, Price, StockQuantity FROM Book";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Bookstore</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Online Bookstore</h1>
        <?php if (isset($_SESSION['user_id'])): ?>
            <p>Welcome, User! <a href="logout.php">Logout</a></p>
            <nav>
                <a href="cart.php">Cart</a> | 
                <a href="profile.php">Profile</a> | 
                <a href="order.php">Orders</a>
            </nav>
        <?php else: ?>
            <p>Please <a href="login.php">Login</a> or <a href="register.php">Register</a></p>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['admin_id'])): ?>
            <p>Welcome, Admin! <a href="admin_logout.php">Logout</a></p>
            <nav>
                <a href="admin_interface.php">Admin Interface</a>
            </nav>
        <?php else: ?>
            <p><a href="admin_login.php">Admin Login</a></p>
        <?php endif; ?>
        
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
                        <button onclick="location.href='book_details.php?book_id=<?php echo $row['BookID']; ?>'">View Details</button>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No books available.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
