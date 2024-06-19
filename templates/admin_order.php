<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
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

if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = 'Completed';
    $query = "UPDATE `Order` SET OrderStatus = ? WHERE OrderID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $new_status, $order_id);
    $stmt->execute();
    $stmt->close();
}

$query = "SELECT `Order`.OrderID, `Order`.OrderDate, `Order`.OrderStatus, User.Name, User.Account, User.Address FROM `Order`
          JOIN User ON `Order`.UserID = User.UserID
          ORDER BY `Order`.OrderDate DESC";
$result = $conn->query($query);

$orders = [];
while ($row = $result->fetch_assoc()) {
    $order_id = $row['OrderID'];
    $orders[$order_id] = [
        'OrderDate' => $row['OrderDate'],
        'OrderStatus' => $row['OrderStatus'],
        'UserName' => $row['Name'],
        'UserAccount' => $row['Account'],
        'UserAddress' => $row['Address'],
        'Items' => []
    ];

    $item_query = "SELECT Book.Title, Book.Price, OrderItem.Quantity FROM OrderItem
                   JOIN Book ON OrderItem.BookID = Book.BookID
                   WHERE OrderItem.OrderID = ?";
    $item_stmt = $conn->prepare($item_query);
    $item_stmt->bind_param('i', $order_id);
    $item_stmt->execute();
    $item_result = $item_stmt->get_result();

    while ($item_row = $item_result->fetch_assoc()) {
        $orders[$order_id]['Items'][] = [
            'Title' => $item_row['Title'],
            'Price' => $item_row['Price'],
            'Quantity' => $item_row['Quantity'],
            'Total' => $item_row['Price'] * $item_row['Quantity']
        ];
    }
    $item_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Orders</title>
    <link rel="stylesheet" href="/static/admin_order.css" />
</head>
<body>
    <div class="container">
        <h1>Orders</h1>
        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order_id => $order): ?>
                <div class="order">
                    <h2>Order ID: <?php echo htmlspecialchars($order_id); ?></h2>
                    <p>Order Date: <?php echo htmlspecialchars($order['OrderDate']); ?></p>
                    <p>Order Status: <?php echo htmlspecialchars($order['OrderStatus']); ?></p>
                    <p>User Name: <?php echo htmlspecialchars($order['UserName']); ?></p>
                    <p>User Account: <?php echo htmlspecialchars($order['UserAccount']); ?></p>
                    <p>User Address: <?php echo htmlspecialchars($order['UserAddress']); ?></p>
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
                    <?php if ($order['OrderStatus'] == 'Processing'): ?>
                        <form action="admin_order.php" method="post">
                            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                            <button type="submit" name="update_status">Mark as Completed</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No orders found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
