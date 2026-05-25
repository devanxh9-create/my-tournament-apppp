<?php
require 'common/config.php';

try {
    // 1. Create Deposits Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS deposits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        transaction_id VARCHAR(100) NOT NULL,
        status ENUM('Pending', 'Completed', 'Rejected') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // 2. Create Withdrawals Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS withdrawals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        status ENUM('Pending', 'Completed', 'Rejected') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // 3. Create Settings Table for Admin UPI & QR
    $pdo->exec("CREATE TABLE IF NOT EXISTS app_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_upi_id VARCHAR(255) NULL,
        admin_qr_image VARCHAR(255) NULL
    )");

    // Insert a default settings row if empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM app_settings");
    if($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO app_settings (admin_upi_id, admin_qr_image) VALUES ('', '')");
    }

    // 4. Safely add upi_id column to users table
    $check = $pdo->query("SHOW COLUMNS FROM users LIKE 'upi_id'");
    if ($check->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN upi_id VARCHAR(255) NULL");
    }

    echo "<div style='background:#111827; color:#fff; padding:20px; font-family:sans-serif; text-align:center;'>";
    echo "<h2 style='color:#10b981;'>✅ Database schema updated successfully!</h2>";
    echo "<p>All necessary tables and columns for the Payment system have been added safely.</p>";
    echo "<p style='color:#ef4444; font-weight:bold;'>You can now safely delete this file (update_database.php).</p>";
    echo "<br><a href='admin/settings.php' style='background:#4f46e5; padding:10px 20px; border-radius:5px; color:#fff; text-decoration:none; display:inline-block; margin-top:15px;'>Go to Admin Settings</a>";
    echo "</div>";

} catch (PDOException $e) {
    die("Database Update Failed: " . $e->getMessage());
}
?>