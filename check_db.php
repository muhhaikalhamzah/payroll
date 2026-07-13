<?php
$env = parse_ini_file('.env');
$dsn = "mysql:host=" . $env['DB_HOST'] . ";port=" . $env['DB_PORT'] . ";dbname=" . $env['DB_DATABASE'];
$pdo = new PDO($dsn, $env['DB_USERNAME'], $env['DB_PASSWORD']);
$stmt = $pdo->query("SHOW TABLES LIKE 'notifications'");
if ($stmt->rowCount() > 0) {
    echo "Notifications table exists.\n";
    $stmt = $pdo->query("SELECT * FROM notifications LIMIT 5");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Rows: " . count($rows) . "\n";
    print_r($rows);
} else {
    echo "Notifications table does not exist.\n";
}
