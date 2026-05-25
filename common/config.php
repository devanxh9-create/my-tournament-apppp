<?php
session_start();

$host = '127.0.0.1';
$user = 'root';
$pass = 'root';
$db   = 'adept_play';

try {
    // Connect without specifying the DB first to allow install.php to create it
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Select DB if it exists
    $pdo->exec("USE `$db`");
} catch (PDOException $e) {
    if (basename($_SERVER['PHP_SELF']) !== 'install.php') {
        die("Database connection failed. Please run install.php first.");
    }
}
?>