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

create_database('localhost', 'root', '411021390', 3307, 'testdb')
