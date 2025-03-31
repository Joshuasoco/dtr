<?php
// env.php - Use for securely storing sensitive information

// Define your environment variables securely (replace with actual values or fetch from a .env file)
define('DB_HOST', 'localhost');
define('DB_NAME', 'duty_tracker');
define('DB_USER', 'root');
define('DB_PASS', '');

// Make sure you use these constants in your database connection file (database.php) to fetch the credentials dynamically.
?>