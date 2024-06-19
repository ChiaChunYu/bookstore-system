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

if (isset($_POST['add_book'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $genre = $_POST['genre'];
    $isbn = $_POST['isbn'];
    $price = $_POST['price'];
    $stock_quantity = $_POST['stock_quantity'];

    $query = "INSERT INTO Book (Title, Author, Genre, ISBN, Price, StockQuantity) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssdi', $title, $author, $genre, $isbn, $price, $stock_quantity);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Book added successfully.</p>";
    } else {
        echo "<p style='color: red;'>Error adding book: " . $conn->error . "</p>";
    }

    $stmt->close();
}

if (isset($_POST['update_price'])) {
    $book_id = $_POST['book_id'];
    $new_price = $_POST['new_price'];

    $query = "UPDATE Book SET Price = ? WHERE BookID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('di', $new_price, $book_id);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Book price updated successfully.</p>";
    } else {
        echo "<p style='color: red;'>Error updating book price: " . $conn->error . "</p>";
    }

    $stmt->close();
}

if (isset($_POST['update_quantity'])) {
    $book_id = $_POST['book_id'];
    $quantity_change = $_POST['quantity_change'];

    $query = "UPDATE Book SET StockQuantity = StockQuantity + ? WHERE BookID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $quantity_change, $book_id);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Book quantity updated successfully.</p>";
    } else {
        echo "<p style='color: red;'>Error updating book quantity: " . $conn->error . "</p>";
    }

    $stmt->close();
}

$query = "SELECT BookID, Title FROM Book";
$books = $conn->query($query);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Interface - Online Bookstore</title>
    <link rel="stylesheet" href="/static/admin_interface.css" />
</head>
<body>
    <div class="container">
        <h1>Admin Interface</h1>
        <h2>Add New Book</h2>
        <form action="" method="post">
            <input type="hidden" name="add_book" value="1">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>
            <label for="author">Author:</label>
            <input type="text" id="author" name="author" required>
            <label for="genre">Genre:</label>
            <input type="text" id="genre" name="genre" required>
            <label for="isbn">ISBN:</label>
            <input type="text" id="isbn" name="isbn" required>
            <label for="price">Price:</label>
            <input type="number" step="0.01" id="price" name="price" required>
            <label for="stock_quantity">Stock Quantity:</label>
            <input type="number" id="stock_quantity" name="stock_quantity" required>
            <input type="submit" value="Add Book">
        </form>

        <h2>Update Book Price</h2>
        <form action="" method="post">
            <input type="hidden" name="update_price" value="1">
            <label for="book_id">Book:</label>
            <select id="book_id" name="book_id" required>
                <option value="">Select a book</option>
                <?php while ($row = $books->fetch_assoc()): ?>
                    <option value="<?php echo $row['BookID']; ?>"><?php echo htmlspecialchars($row['Title']); ?></option>
                <?php endwhile; ?>
            </select>
            <label for="new_price">New Price:</label>
            <input type="number" step="0.01" id="new_price" name="new_price" required>
            <input type="submit" value="Update Price">
        </form>

        <h2>Update Book Quantity</h2>
        <form action="" method="post">
            <input type="hidden" name="update_quantity" value="1">
            <label for="book_id_quantity">Book:</label>
            <select id="book_id_quantity" name="book_id" required>
                <option value="">Select a book</option>
                <?php
                $books->data_seek(0);
                while ($row = $books->fetch_assoc()): ?>
                    <option value="<?php echo $row['BookID']; ?>"><?php echo htmlspecialchars($row['Title']); ?></option>
                <?php endwhile; ?>
            </select>
            <label for="quantity_change">Quantity Change:</label>
            <input type="number" id="quantity_change" name="quantity_change" required>
            <input type="submit" value="Update Quantity">
        </form>
        <p><a href="index.php">Back to Home</a></p>
    </div>
</body>
</html>
