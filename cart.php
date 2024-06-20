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

if (isset($_POST['remove_item'])) {
    $book_id = $_POST['remove_item'];
    $query = "DELETE FROM OrderItem WHERE OrderID = (SELECT OrderID FROM `Order` WHERE UserID = ? AND OrderStatus = 'Pending') AND BookID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $user_id, $book_id);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['increase_quantity'])) {
    $book_id = $_POST['increase_quantity'];

    $query = "SELECT StockQuantity FROM Book WHERE BookID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $book_id);
    $stmt->execute();
    $stmt->bind_result($stock_quantity);
    $stmt->fetch();
    $stmt->close();

    $query = "SELECT Quantity FROM OrderItem WHERE OrderID = (SELECT OrderID FROM `Order` WHERE UserID = ? AND OrderStatus = 'Pending') AND BookID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $user_id, $book_id);
    $stmt->execute();
    $stmt->bind_result($current_quantity);
    $stmt->fetch();
    $stmt->close();

    if ($current_quantity < $stock_quantity) {
        $query = "UPDATE OrderItem SET Quantity = Quantity + 1 WHERE OrderID = (SELECT OrderID FROM `Order` WHERE UserID = ? AND OrderStatus = 'Pending') AND BookID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $user_id, $book_id);
        $stmt->execute();
        $stmt->close();
    }
}

if (isset($_POST['decrease_quantity'])) {
    $book_id = $_POST['decrease_quantity'];
    $query = "UPDATE OrderItem SET Quantity = CASE WHEN Quantity > 1 THEN Quantity - 1 ELSE Quantity END WHERE OrderID = (SELECT OrderID FROM `Order` WHERE UserID = ? AND OrderStatus = 'Pending') AND BookID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $user_id, $book_id);
    $stmt->execute();
    $stmt->close();
}

$query = "SELECT Book.BookID, Book.Title, Book.Price, OrderItem.Quantity FROM OrderItem
          JOIN Book ON OrderItem.BookID = Book.BookID
          JOIN `Order` ON OrderItem.OrderID = `Order`.OrderID
          WHERE `Order`.UserID = ? AND `Order`.OrderStatus = 'Pending'";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total_price = 0;

if (isset($_POST['confirm_order'])) {
    $user_password = $_POST['password'];

    $query = "SELECT Password FROM User WHERE UserID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($user_password, $hashed_password)) {
        echo "<p style='color: red;'>Incorrect password.</p>";
    } else {
        $query = "SELECT OrderID FROM `Order` WHERE UserID = ? AND OrderStatus = 'Pending'";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->bind_result($order_id);
        $stmt->fetch();
        $stmt->close();

        $query = "SELECT BookID, Quantity FROM OrderItem WHERE OrderID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $order_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $can_confirm_order = true;

        while ($row = $result->fetch_assoc()) {
            $query = "SELECT StockQuantity FROM Book WHERE BookID = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $row['BookID']);
            $stmt->execute();
            $stmt->bind_result($stock_quantity);
            $stmt->fetch();
            $stmt->close();

            if ($row['Quantity'] > $stock_quantity) {
                echo "<p style='color: red;'>Not enough stock for book ID " . htmlspecialchars($row['BookID']) . ". Please adjust your cart.</p>";
                $can_confirm_order = false;
                break;
            }
        }

        if ($can_confirm_order) {
            $result->data_seek(0);

            while ($row = $result->fetch_assoc()) {
                $query = "UPDATE Book SET StockQuantity = StockQuantity - ? WHERE BookID = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('ii', $row['Quantity'], $row['BookID']);
                $stmt->execute();
            }

            $query = "UPDATE `Order` SET OrderStatus = 'Processing', TotalPrice = ? WHERE OrderID = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('di', $total_price, $order_id);
            $stmt->execute();

            echo "<p style='color: green;'>Order confirmed. Thank you for your purchase!</p>";
        }
    }
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="/static/cart.css" />
</head>
<body>
    <div class="container">
        <h1>Shopping Cart</h1>
        <?php if ($result->num_rows > 0): ?>
            <form action="cart.php" method="post">
                <table>
                    <tr>
                        <th>Title</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['Title']); ?></td>
                            <td><?php echo htmlspecialchars($row['Price']); ?></td>
                            <td><?php echo htmlspecialchars($row['Quantity']); ?></td>
                            <td><?php echo htmlspecialchars($row['Price'] * $row['Quantity']); ?></td>
                            <td>
                                <button type="submit" name="increase_quantity" value="<?php echo $row['BookID']; ?>">Increase</button>
                                <button type="submit" name="decrease_quantity" value="<?php echo $row['BookID']; ?>">Decrease</button>
                                <button type="submit" name="remove_item" value="<?php echo $row['BookID']; ?>">Remove</button>
                            </td>
                        </tr>
                        <?php $total_price += $row['Price'] * $row['Quantity']; ?>
                    <?php endwhile; ?>
                </table>
                <h2>Total Price: <?php echo $total_price; ?></h2>
                <button type="button" onclick="document.getElementById('confirmOrder').style.display='block'">Proceed to Checkout</button>
            </form>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </div>

    <div id="confirmOrder" style="display:none;">
        <h2>Confirm Order</h2>
        <form action="cart.php" method="post">
            <input type="hidden" name="confirm_order" value="1">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Confirm Order</button>
        </form>
        <button type="button" onclick="document.getElementById('confirmOrder').style.display='none'">Cancel</button>
    </div>
</body>
</html>
