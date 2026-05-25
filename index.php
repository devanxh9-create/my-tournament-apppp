<?php
require 'common/config.php';
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$msg = '';
$user_id = $_SESSION['user_id'];

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['join_tournament'])) {
    $t_id = $_POST['tournament_id'];
    
    // Check if already joined
    $chk = $pdo->prepare("SELECT id FROM participants WHERE user_id=? AND tournament_id=?");
    $chk->execute([$user_id, $t_id]);
    if($chk->rowCount() > 0) {
        $msg = "You have already joined this tournament.";
    } else {
        $stmt = $pdo->prepare("SELECT entry_fee, title FROM tournaments WHERE id=?");
        $stmt->execute([$t_id]);
        $tournament = $stmt->fetch();
        
        $stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id=?");
        $stmt->execute([$user_id]);
        $balance = $stmt->fetchColumn();

        if($balance >= $tournament['entry_fee']) {
            $pdo->beginTransaction();
            try {
                // Deduct Balance
                $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id=?")->execute([$tournament['entry_fee'], $user_id]);
                // Add Participant
                $pdo->prepare("INSERT INTO participants (user_id, tournament_id) VALUES (?, ?)")->execute([$user_id, $t_id]);
                // Log Tx
                $desc = "Joined Tournament: " . $tournament['title'];
                $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'debit', ?)")->execute([$user_id, $tournament['entry_fee'], $desc]);
                $pdo->commit();
                $msg = "Successfully joined the tournament!";
            } catch(Exception $e) {
                $pdo->rollBack();
                $msg = "Error joining tournament.";
            }
        } else {
            $msg = "Insufficient wallet balance. Please add money.";
        }
    }
    echo "<script>alert('$msg'); window.location.href='index.php';</script>"; exit;
}

require 'common/header.php';
?>
<h2 class="text-2xl font-bold mb-4">Upcoming Tournaments</h2>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <?php
    $stmt = $pdo->query("SELECT * FROM tournaments WHERE status = 'Upcoming' ORDER BY match_time ASC");
    $tournaments = $stmt->fetchAll();

    if(count($tournaments) == 0) echo "<p class='text-gray-400'>No upcoming tournaments at the moment.</p>";
    foreach($tournaments as $t): 
        // Check if joined
        $chk = $pdo->prepare("SELECT id FROM participants WHERE user_id=? AND tournament_id=?");
        $chk->execute([$user_id, $t['id']]);
        $joined = $chk->rowCount() > 0;
    ?>
    <div class="bg-gray-800 rounded-xl p-4 shadow-lg border border-gray-700 relative overflow-hidden">
        <div class="absolute top-0 right-0 bg-blue-600 text-xs px-3 py-1 font-bold rounded-bl-lg">
            <?= htmlspecialchars($t['game_name']) ?>
        </div>
        <h3 class="text-xl font-bold mt-2 mb-1"><?= htmlspecialchars($t['title']) ?></h3>
        <p class="text-gray-400 text-sm mb-4"><i class="fa-regular fa-clock mr-1"></i> <?= date('d M Y, h:i A', strtotime($t['match_time'])) ?></p>
        <div class="flex justify-between bg-gray-900 rounded-lg p-3 mb-4">
            <div class="text-center">
                <span class="block text-gray-500 text-xs uppercase">Entry Fee</span>
                <span class="font-bold text-yellow-400">₹<?= $t['entry_fee'] ?></span>
            </div>
            <div class="text-center border-l border-gray-700 pl-4">
                <span class="block text-gray-500 text-xs uppercase">Prize Pool</span>
                <span class="font-bold text-green-400">₹<?= $t['prize_pool'] ?></span>
            </div>
        </div>
        <?php if($joined): ?>
            <button disabled class="w-full bg-gray-600 text-white font-bold py-2 rounded-lg cursor-not-allowed">Joined</button>
        <?php else: ?>
            <form method="POST" action="">
                <input type="hidden" name="tournament_id" value="<?= $t['id'] ?>">
                <button type="submit" name="join_tournament" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded-lg transition shadow-md">Join Now</button>
            </form>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php require 'common/bottom.php'; ?>