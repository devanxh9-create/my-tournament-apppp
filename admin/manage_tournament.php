<?php
require '../common/config.php';
if(!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }

// ==========================================
// 🚀 MEGA AUTO-FIX DATABASE (Tables aur Columns khud bana dega)
// ==========================================
try {
    // 1. Tournaments table me 'winner_id' add karega
    $pdo->exec("ALTER TABLE tournaments ADD winner_id INT DEFAULT NULL");
} catch (PDOException $e) {}

try {
    // 2. Users table me 'wallet_balance' add karega (agar nahi hai toh)
    $pdo->exec("ALTER TABLE users ADD wallet_balance DECIMAL(10,2) NOT NULL DEFAULT 0.00");
} catch (PDOException $e) {}

try {
    // 3. Transactions table banayega (Prizes ki history rakhne ke liye)
    $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        type VARCHAR(50) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {}
// ==========================================

$t_id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM tournaments WHERE id=?");
$stmt->execute([$t_id]);
$t = $stmt->fetch();
if(!$t) { header("Location: tournament.php"); exit; }

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_room'])) {
    $rid = $_POST['room_id'];
    $rpass = $_POST['room_password'];
    // Update to Live if not completed
    $status = $t['status'] == 'Upcoming' ? 'Live' : $t['status'];
    try {
        $pdo->prepare("UPDATE tournaments SET room_id=?, room_password=?, status=? WHERE id=?")->execute([$rid, $rpass, $status, $t_id]);
        echo "<script>alert('Room Details Updated!'); window.location.href='manage_tournament.php?id=$t_id';</script>"; exit;
    } catch(Exception $e) {
        echo "<script>alert('Error updating room: " . addslashes($e->getMessage()) . "');</script>";
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['declare_winner'])) {
    $winner_id = $_POST['winner_id'];
    if($t['status'] != 'Completed') {
        $pdo->beginTransaction();
        try {
            // Update tournament winner
            $pdo->prepare("UPDATE tournaments SET winner_id=?, status='Completed' WHERE id=?")->execute([$winner_id, $t_id]);
            
            // Add prize to wallet
            $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id=?")->execute([$t['prize_pool'], $winner_id]);
            
            // Log Transaction
            $desc = "Won Tournament: " . $t['title'];
            $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'credit', ?)")->execute([$winner_id, $t['prize_pool'], $desc]);
            
            $pdo->commit();
            echo "<script>alert('Winner Declared & Prize Distributed Successfully!'); window.location.href='manage_tournament.php?id=$t_id';</script>"; exit;
        } catch(Exception $e) {
            $pdo->rollBack();
            // Ab error chupna nahi chahiye, saaf-saaf screen par dikhega!
            $asli_error = addslashes($e->getMessage());
            echo "<script>alert('Error declaring winner: $asli_error');</script>";
        }
    }
}

// Get Participants
$p_stmt = $pdo->prepare("SELECT u.id, u.username FROM participants p JOIN users u ON p.user_id = u.id WHERE p.tournament_id=?");
$p_stmt->execute([$t_id]);
$participants = $p_stmt->fetchAll();

require 'common/header.php';
?>
<div class="mb-4">
    <a href="tournament.php" class="text-indigo-400 text-sm"><i class="fa-solid fa-arrow-left mr-1"></i> Back to Tournaments</a>
</div>
<div class="bg-gray-800 rounded-xl p-4 shadow mb-6">
    <h2 class="text-xl font-bold mb-1"><?= htmlspecialchars($t['title']) ?></h2>
    <div class="flex gap-2 mt-2">
        <span class="bg-gray-700 px-2 py-1 rounded text-xs">Players: <?= count($participants) ?></span>
        <span class="bg-gray-700 px-2 py-1 rounded text-xs">Prize: ₹<?= $t['prize_pool'] ?></span>
        <span class="bg-gray-700 px-2 py-1 rounded text-xs">Status: <?= $t['status'] ?></span>
    </div>
</div>

<?php if($t['status'] != 'Completed'): ?>
<div class="bg-gray-900 border border-gray-700 rounded-xl p-4 mb-6 shadow">
    <h3 class="font-bold text-indigo-400 mb-3"><i class="fa-solid fa-key mr-2"></i> Update Room Details</h3>
    <form method="POST" action="">
        <input type="text" name="room_id" value="<?= htmlspecialchars($t['room_id'] ?? '') ?>" placeholder="Room ID" required class="w-full bg-gray-950 border border-gray-700 rounded-lg px-4 py-2 text-white mb-2 focus:outline-none">
        <input type="text" name="room_password" value="<?= htmlspecialchars($t['room_password'] ?? '') ?>" placeholder="Room Password" required class="w-full bg-gray-950 border border-gray-700 rounded-lg px-4 py-2 text-white mb-3 focus:outline-none">
        <button type="submit" name="update_room" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 rounded-lg">Set Room (Go Live)</button>
    </form>
</div>

<div class="bg-gray-900 border border-gray-700 rounded-xl p-4 mb-6 shadow">
    <h3 class="font-bold text-green-400 mb-3"><i class="fa-solid fa-trophy mr-2"></i> Declare Winner</h3>
    <?php if(count($participants) > 0): ?>
    <form method="POST" action="" onsubmit="return confirm('Are you sure? This will distribute the prize amount (₹<?= $t['prize_pool'] ?>) immediately.')">
        <select name="winner_id" required class="w-full bg-gray-950 border border-gray-700 rounded-lg px-4 py-2 text-white mb-3 focus:outline-none">
            <option value="">Select Winner...</option>
            <?php foreach($participants as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['username']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="declare_winner" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 rounded-lg">Declare Winner & Distribute Prize</button>
    </form>
    <?php else: ?>
        <p class="text-sm text-gray-500">No participants joined yet.</p>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="bg-green-900/40 border border-green-700 rounded-xl p-4 mb-6 text-center text-green-400 font-bold">
    This tournament is completed.
    <?php 
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id=?");
    $stmt->execute([$t['winner_id']]);
    $winner = $stmt->fetchColumn();
    if($winner) echo "<br>Winner: " . htmlspecialchars($winner);
    ?>
</div>
<?php endif; ?>

<h3 class="font-bold mb-2">Participant List</h3>
<div class="bg-gray-800 rounded-xl shadow border border-gray-700 overflow-hidden mb-6">
    <?php if(count($participants)==0) echo "<p class='p-3 text-gray-400 text-sm'>No participants.</p>"; ?>
    <?php foreach($participants as $p): ?>
        <div class="p-3 border-b border-gray-700 last:border-0 font-semibold text-sm">
            <i class="fa-solid fa-user text-gray-500 mr-2"></i> <?= htmlspecialchars($p['username']) ?>
        </div>
    <?php endforeach; ?>
</div>
<?php require 'common/bottom.php'; ?>
