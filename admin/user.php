<?php
require '../common/config.php';
if(!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }

// ==========================================
// 🚀 AUTO-FIX DATABASE (Ye khud column bana dega)
// ==========================================
try {
    $pdo->exec("ALTER TABLE users ADD is_blocked TINYINT(1) NOT NULL DEFAULT 0");
} catch (PDOException $e) {
    // Column pehle se bana hai toh error ignore karega
}
// ==========================================

// --- 1. Block / Unblock Logic ---
if(isset($_GET['toggle_block'])) {
    $uid = $_GET['toggle_block'];
    try {
        $pdo->prepare("UPDATE users SET is_blocked = IF(is_blocked = 1, 0, 1) WHERE id=?")->execute([$uid]);
        header("Location: user.php"); 
        exit;
    } catch (PDOException $e) {
        die("Database Error: " . htmlspecialchars($e->getMessage()));
    }
}

// --- 2. Delete User Logic ---
if(isset($_GET['delete_user'])) {
    $uid = $_GET['delete_user'];
    try {
        $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$uid]);
        header("Location: user.php");
        exit;
    } catch (PDOException $e) {
        die("Database Error: " . htmlspecialchars($e->getMessage()));
    }
}

require 'common/header.php';
?>

<h2 class="text-2xl font-bold mb-4 text-white">Registered Users</h2>
<div class="space-y-3">
    <?php
    try {
        $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
        while($u = $stmt->fetch()):
    ?>
    <div class="bg-gray-800 rounded-xl p-4 shadow border border-gray-700 flex justify-between items-center">
        <div style="flex: 1; min-width: 0; padding-right: 10px;">
            <h4 class="font-bold text-lg truncate <?= isset($u['is_blocked']) && $u['is_blocked'] ? 'text-red-400 line-through' : 'text-white' ?>">
                <?= htmlspecialchars($u['username']) ?>
            </h4>
            <p class="text-xs text-gray-400 truncate"><?= htmlspecialchars($u['email']) ?></p>
            <p class="text-sm font-semibold text-yellow-400 mt-1">₹<?= htmlspecialchars($u['wallet_balance'] ?? '0.00') ?></p>
        </div>
        
        <div class="flex items-center gap-2">
            <?php $isBlocked = isset($u['is_blocked']) ? $u['is_blocked'] : 0; ?>
            <a href="?toggle_block=<?= $u['id'] ?>" class="px-2.5 py-1 text-xs font-bold rounded-lg border <?= $isBlocked ? 'border-green-500 text-green-500 hover:bg-green-900/30' : 'border-red-500 text-red-500 hover:bg-red-900/30' ?>">
                <?= $isBlocked ? 'Unblock' : 'Block' ?>
            </a>
            
            <a href="?delete_user=<?= $u['id'] ?>" onclick="return confirm('Kya aap sach me is user ko delete karna chahte hain?')" class="px-2.5 py-1 text-xs font-bold rounded-lg border border-rose-600 text-rose-500 hover:bg-rose-900/30">
                <i class="fa-solid fa-trash"></i>
            </a>
        </div>
    </div>
    <?php 
        endwhile;
    } catch (PDOException $e) {
        echo "<div class='text-red-500 font-bold p-4 bg-gray-800 rounded'>Error loading users: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    ?>
</div>

<?php require 'common/bottom.php'; ?>
