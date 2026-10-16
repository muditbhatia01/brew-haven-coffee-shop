<?php
declare(strict_types=1);
$host = '127.0.0.1';
$dbname = 'brew_haven';
$dbuser = 'root';
$dbpass = ''; // Default XAMPP local setup. Set this if you configured a MySQL password.
$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
try {
    $pdo = new PDO($dsn, $dbuser, $dbpass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed. Check config/db.php and make sure MySQL is running and database brew_haven exists.');
}
