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
$query = "SELECT `Order`.OrderID, `Order`.OrderDate, `Order`.OrderStatus, Book.Title, Book.Price, OrderItem.Quantity FROM `Order`
          JOIN OrderItem ON `Order`.OrderID = OrderItem.OrderID
          JOIN Book ON OrderItem.BookID = Book.BookID
          WHERE `Order`.UserID = ? AND `Order`.OrderStatus = 'Processing'
          ORDER BY `Order`.OrderDate DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[$row['OrderID']]['OrderDate'] = $row['OrderDate'];
    $orders[$row['OrderID']]['OrderStatus'] = $row['OrderStatus'];
    $orders[$row['OrderID']]['Items'][] = [
        'Title' => $row['Title'],
        'Price' => $row['Price'],
        'Quantity' => $row['Quantity'],
        'Total' => $row['Price'] * $row['Quantity']
    ];
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Online Bookstore</title>
    <link rel="stylesheet" href="/static/order.css" />
</head>
<body>
    <div class="container">
        <h1>Order History</h1>
        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order_id => $order): ?>
                <div class="order">
                    <h2>Order ID: <?php echo htmlspecialchars($order_id); ?></h2>
                    <p>Order Date: <?php echo htmlspecialchars($order['OrderDate']); ?></p>
                    <p>Order Status: <?php echo htmlspecialchars($order['OrderStatus']); ?></p>
                    <table>
                        <tr>
                            <th>Title</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                        <?php foreach ($order['Items'] as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['Title']); ?></td>
                                <td><?php echo htmlspecialchars($item['Price']); ?></td>
                                <td><?php echo htmlspecialchars($item['Quantity']); ?></td>
                                <td><?php echo htmlspecialchars($item['Total']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                    <h3>Order Total: <?php echo array_sum(array_column($order['Items'], 'Total')); ?></h3>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>You have no processing orders.</p>
        <?php endif; ?>
    </div>
</body>
</html>
