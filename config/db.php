<?php
/*
 * Database Connection File
 * This file establishes a connection to MySQL database using MySQLi
 * Include this file in any PHP file that needs database access
 */

// Database configuration variables
$host = 'localhost';        
$user = 'root';            
$pass = '';                
$dbname = 'minishop';     

$conn = new mysqli($host, $user, $pass, $dbname);

// Check if connection was successful
if ($conn->connect_error) {
    die("
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Database Connection Error</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }
            .error-container {
                background: white;
                padding: 40px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                max-width: 500px;
                text-align: center;
            }
            .error-container h1 {
                color: #e74c3c;
                margin-bottom: 20px;
            }
            .error-container p {
                color: #7f8c8d;
                line-height: 1.6;
                margin-bottom: 15px;
            }
            .error-details {
                background: #ecf0f1;
                padding: 15px;
                border-radius: 5px;
                margin: 20px 0;
                text-align: left;
            }
            .error-code {
                color: #c0392b;
                font-family: monospace;
                font-size: 14px;
            }
            .help-list {
                text-align: left;
                margin: 20px 0;
            }
            .help-list li {
                margin: 10px 0;
                color: #555;
            }
        </style>
    </head>
    <body>
        <div class='error-container'>
            <h1>⚠️ Database Connection Failed</h1>
            <p>Unable to connect to the MySQL database. Please check the following:</p>
            
            <div class='error-details'>
                <strong>Error Message:</strong><br>
                <span class='error-code'>" . $conn->connect_error . "</span>
            </div>
            
            <ul class='help-list'>
                <li><strong>Is XAMPP running?</strong> Make sure Apache and MySQL are started in XAMPP Control Panel.</li>
                <li><strong>Does the database exist?</strong> Check if 'myshop_db' exists in phpMyAdmin.</li>
                <li><strong>Are credentials correct?</strong> Verify username and password in /config/db.php</li>
                <li><strong>Is MySQL port 3306 available?</strong> Another service might be using this port.</li>
            </ul>
            
            <p style='margin-top: 30px; font-size: 14px; color: #95a5a6;'>
                Check /config/db.php file to update database settings.
            </p>
        </div>
    </body>
    </html>
    ");
}

$conn->set_charset("utf8mb4");

/*
 * Connection successful!
 * The $conn object is now available to use in any file that includes this db.php
 * 
 * USAGE EXAMPLE:
 * 
 * // Include database connection
 * include 'config/db.php';
 * 
 * // Run a query
 * $sql = "SELECT * FROM products";
 * $result = $conn->query($sql);
 * 
 * // Fetch data
 * while($row = $result->fetch_assoc()) {
 *     echo $row['name'];
 * }
 * 
 * // Close connection (optional, PHP does this automatically)
 * $conn->close();
 */

// Uncomment the line below to test the connection
// echo "Database connected successfully!";
?>