<?php
require_once '../config/database.php';

$query = $pdo->query("SELECT NOW() as mysql_time, @@session.time_zone as timezone");
$row = $query->fetch(PDO::FETCH_ASSOC);

echo "MySQL Current Time: " . $row['mysql_time'] . "<br>";
echo "MySQL Timezone: " . $row['timezone'];
?>