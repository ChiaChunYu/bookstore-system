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
$query = "SELECT Book.Title, Book.Price, OrderItem.Quantity FROM OrderItem
          JOIN Book ON OrderItem.BookID = Book.BookID
          JOIN `Order` ON OrderItem.OrderID = `Order`.OrderID
          WHERE `Order`.UserID = ? AND `Order`.OrderStatus = 'Pending'";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total_price = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Online Bookstore</title>
    <link rel="stylesheet" href="/static/cart.css" />
</head>
<body>
    <div class="container">
        <h1>Shopping Cart</h1>
        <?php if ($result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Title</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['Title']); ?></td>
                        <td><?php echo htmlspecialchars($row['Price']); ?></td>
                        <td><?php echo htmlspecialchars($row['Quantity']); ?></td>
                        <td><?php echo htmlspecialchars($row['Price'] * $row['Quantity']); ?></td>
                    </tr>
                    <?php $total_price += $row['Price'] * $row['Quantity']; ?>
                <?php endwhile; ?>
            </table>
            <h2>Total Price: <?php echo $total_price; ?></h2>
            <button onclick="location.href='checkout.php'">Proceed to Checkout</button>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
