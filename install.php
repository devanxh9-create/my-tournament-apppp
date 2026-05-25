<?php
require 'common/config.php';

try {
    // 1. Force drop the old database to clear out bad tables
    $pdo->exec("DROP DATABASE IF EXISTS `$db`");
    
    // 2. Create a fresh database
    $pdo->exec("CREATE DATABASE `$db`");
    $pdo->exec("USE `$db`");

    // 3. Create all tables with the correct columns (including commission)
    $sql = "
    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        wallet_balance DECIMAL(10,2) DEFAULT 0.00,
        is_blocked TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL
    );

    CREATE TABLE tournaments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        game_name VARCHAR(100) NOT NULL,
        entry_fee DECIMAL(10,2) NOT NULL,
        prize_pool DECIMAL(10,2) NOT NULL,
        match_time DATETIME NOT NULL,
        commission INT DEFAULT 20,
        room_id VARCHAR(50) DEFAULT NULL,
        room_password VARCHAR(50) DEFAULT NULL,
        status ENUM('Upcoming', 'Live', 'Completed') DEFAULT 'Upcoming',
        winner_id INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE participants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        tournament_id INT NOT NULL,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY(tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE
    );

    CREATE TABLE transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        type ENUM('credit', 'debit') NOT NULL,
        description VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    );
    ";
    
    $pdo->exec($sql);

    // 4. Insert default admin
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT INTO admin (username, password) VALUES ('admin', '$hash')");

    echo "<script>
        alert('Database Reset & Installed Successfully! The commission error is now fixed.'); 
        window.location.href='admin/login.php';
    </script>";

} catch (PDOException $e) {
    die("Installation Failed: " . $e->getMessage());
}
?>