import mysql.connector
from mysql.connector import Error

def create_database(host, user, password, port, database_name):
    try:
        connection = mysql.connector.connect(
            host=host,
            user=user,
            password=password,
            port=port
        )
        if connection.is_connected():
            cursor = connection.cursor()
            cursor.execute(f"CREATE DATABASE IF NOT EXISTS {database_name}")
            print(f"Database '{database_name}' created successfully")
    except Error as e:
        print(f"Error while connecting to MySQL or creating database: {e}")
    finally:
        if connection and connection.is_connected():
            cursor.close()
            connection.close()
            print("MySQL connection is closed")

def create_tables(host, user, password, port, database_name):
    try:
        connection = mysql.connector.connect(
            host=host,
            user=user,
            password=password,
            port=port,
            database=database_name
        )
        if connection.is_connected():
            cursor = connection.cursor()
            
            # 創建 User 表
            cursor.execute("""
                CREATE TABLE IF NOT EXISTS User (
                    UserID INT AUTO_INCREMENT PRIMARY KEY,
                    Name VARCHAR(100) NOT NULL,
                    Email VARCHAR(100) NOT NULL UNIQUE,
                    Password VARCHAR(255) NOT NULL,
                    Address VARCHAR(255) NOT NULL
                )
            """)
            print("Table 'User' created successfully")
            
            # 創建 Book 表
            cursor.execute("""
                CREATE TABLE IF NOT EXISTS Book (
                    BookID INT AUTO_INCREMENT PRIMARY KEY,
                    Title VARCHAR(255) NOT NULL,
                    Author VARCHAR(255) NOT NULL,
                    Genre VARCHAR(100),
                    ISBN VARCHAR(13) NOT NULL UNIQUE,
                    Price DECIMAL(10, 2) NOT NULL,
                    StockQuantity INT NOT NULL
                )
            """)
            print("Table 'Book' created successfully")
            
            # 創建 Order 表
            cursor.execute("""
                CREATE TABLE IF NOT EXISTS `Order` (
                    OrderID INT AUTO_INCREMENT PRIMARY KEY,
                    UserID INT,
                    OrderDate DATETIME DEFAULT CURRENT_TIMESTAMP,
                    TotalPrice DECIMAL(10, 2) NOT NULL,
                    OrderStatus VARCHAR(50) NOT NULL,
                    FOREIGN KEY (UserID) REFERENCES User(UserID)
                )
            """)
            print("Table 'Order' created successfully")
            
            # 創建 OrderItem 表
            cursor.execute("""
                CREATE TABLE IF NOT EXISTS OrderItem (
                    OrderItemID INT AUTO_INCREMENT PRIMARY KEY,
                    OrderID INT,
                    BookID INT,
                    Quantity INT NOT NULL,
                    Price DECIMAL(10, 2) NOT NULL,
                    FOREIGN KEY (OrderID) REFERENCES `Order`(OrderID),
                    FOREIGN KEY (BookID) REFERENCES Book(BookID)
                )
            """)
            print("Table 'OrderItem' created successfully")
            
    except Error as e:
        print(f"Error while connecting to MySQL or creating tables: {e}")
    finally:
        if connection and connection.is_connected():
            cursor.close()
            connection.close()
            print("MySQL connection is closed")

# 創建資料庫
create_database('localhost', 'root', '411021390', 3307, 'OnlineBookstore')

# 創建表
create_tables('localhost', 'root', '411021390', 3307, 'OnlineBookstore')
