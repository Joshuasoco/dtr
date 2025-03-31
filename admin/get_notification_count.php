<?php
require 'database.php'; // Database connection

// Count submitted duty logs (Modify the query as needed)
$sql = "SELECT COUNT(*) AS count FROM duty_logs WHERE status = 'Submitted'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

// Return JSON response
echo json_encode(["count" => $row['count']]);
?>