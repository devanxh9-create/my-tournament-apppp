<?php
require '../common/config.php';
if(!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }

if(isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    
    $stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE id=? AND status='Pending'");
    $stmt->execute([$id]);
    $w = $stmt->fetch();
    
    if($w) {
        if($action == 'approve') {
            // Already deducted on request. Just mark complete and add txn log.
            $pdo->beginTransaction();
            try {
                $pdo->prepare("UPDATE withdrawals SET status='Completed' WHERE id=?")->execute([$id]);
                $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'debit', 'Withdrawal Successful')")->execute([$w['user_id'], $w['amount']]);
                $pdo->commit();
                echo "<script>alert('Withdrawal marked as Completed!'); window.location.href='withdrawals.php';</script>"; exit;
            } catch(Exception $e) {
                $pdo->rollBack();
            }
        } elseif($action == 'reject') {
            // Refund the money to wallet
            $pdo->beginTransaction();
            try {
                $pdo->prepare("UPDATE withdrawals SET status='Rejected' WHERE id=?")->execute([$id]);
                $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id=?")->execute([$w['amount'], $w['user_id']]);
                $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'credit', 'Withdrawal Rejected (Refund)')")->execute([$w['user_id'], $w['amount']]);
                $pdo->commit();
                echo "<script>alert('Withdrawal Rejected & Refunded.'); window.location.href='withdrawals.php';</script>"; exit;
            } catch(Exception $e) {
                $pdo->rollBack();
            }
        }
    }
}

require 'common/header.php';
?>
<div class="flex items-center mb-4">
    <a href="settings.php" class="text-indigo-400 mr-3"><i class="fa-solid fa-arrow-left"></i></a>
    <h2 class="text-2xl font-bold">Withdrawal Requests</h2>
</div>

<div class="space-y-4">
    <?php
    $stmt = $pdo->query("SELECT w.*, u.username, u.upi_id FROM withdrawals w JOIN users u ON w.user_id = u.id ORDER BY w.created_at DESC");
    $withdrawals = $stmt->fetchAll();
    
    if(count($withdrawals) == 0) echo "<p class='text-gray-400'>No withdrawal requests yet.</p>";
    
    foreach($withdrawals as $w):
    ?>
    <div class="bg-gray-800 rounded-xl p-4 shadow border border-gray-700">
        <div class="flex justify-between items-start mb-2">
            <div>
                <h4 class="font-bold text-lg text-white"><?= htmlspecialchars($w['username']) ?></h4>
                <p class="text-xs text-gray-400 mt-1">User UPI: <br><span class="text-white font-mono bg-gray-900 px-2 py-1 rounded block mt-1"><?= htmlspecialchars($w['upi_id'] ?? 'Not Set') ?></span></p>
                <p class="text-xs text-gray-500 mt-2"><?= date('d M Y, h:i A', strtotime($w['created_at'])) ?></p>
            </div>
            <div class="text-right">
                <span class="text-red-400 font-bold text-xl">-₹<?= $w['amount'] ?></span>
                <span class="block text-xs px-2 py-1 rounded mt-1 
                    <?= $w['status']=='Pending'?'bg-yellow-500/20 text-yellow-500':($w['status']=='Completed'?'bg-green-500/20 text-green-500':'bg-red-500/20 text-red-500') ?>">
                    <?= $w['status'] ?>
                </span>
            </div>
        </div>
        
        <?php if($w['status'] == 'Pending'): ?>
        <div class="flex gap-2 mt-3 pt-3 border-t border-gray-700">
            <a href="?action=approve&id=<?= $w['id'] ?>" onclick="return confirm('Did you manually send the money? Mark as Approved?')" class="flex-1 bg-green-600 text-center text-white text-sm font-bold py-2 rounded-lg"><i class="fa-solid fa-check mr-1"></i> Sent (Approve)</a>
            <a href="?action=reject&id=<?= $w['id'] ?>" onclick="return confirm('Reject and refund money to user?')" class="flex-1 bg-red-600 text-center text-white text-sm font-bold py-2 rounded-lg"><i class="fa-solid fa-xmark mr-1"></i> Reject</a>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php require 'common/bottom.php'; ?>