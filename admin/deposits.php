<?php
require '../common/config.php';
if(!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }

if(isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    
    $stmt = $pdo->prepare("SELECT * FROM deposits WHERE id=? AND status='Pending'");
    $stmt->execute([$id]);
    $dep = $stmt->fetch();
    
    if($dep) {
        if($action == 'approve') {
            $pdo->beginTransaction();
            try {
                $pdo->prepare("UPDATE deposits SET status='Completed' WHERE id=?")->execute([$id]);
                $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id=?")->execute([$dep['amount'], $dep['user_id']]);
                $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'credit', 'Deposit Approved (UPI)')")->execute([$dep['user_id'], $dep['amount']]);
                $pdo->commit();
                echo "<script>alert('Deposit Approved!'); window.location.href='deposits.php';</script>"; exit;
            } catch(Exception $e) {
                $pdo->rollBack();
            }
        } elseif($action == 'reject') {
            $pdo->prepare("UPDATE deposits SET status='Rejected' WHERE id=?")->execute([$id]);
            echo "<script>alert('Deposit Rejected.'); window.location.href='deposits.php';</script>"; exit;
        }
    }
}

require 'common/header.php';
?>
<div class="flex items-center mb-4">
    <a href="settings.php" class="text-indigo-400 mr-3"><i class="fa-solid fa-arrow-left"></i></a>
    <h2 class="text-2xl font-bold">Deposit Requests</h2>
</div>

<div class="space-y-4">
    <?php
    $stmt = $pdo->query("SELECT d.*, u.username FROM deposits d JOIN users u ON d.user_id = u.id ORDER BY d.created_at DESC");
    $deposits = $stmt->fetchAll();
    
    if(count($deposits) == 0) echo "<p class='text-gray-400'>No deposit requests yet.</p>";
    
    foreach($deposits as $d):
    ?>
    <div class="bg-gray-800 rounded-xl p-4 shadow border border-gray-700">
        <div class="flex justify-between items-start mb-2">
            <div>
                <h4 class="font-bold text-lg text-white"><?= htmlspecialchars($d['username']) ?></h4>
                <p class="text-xs text-gray-400">Txn ID: <span class="text-white font-mono"><?= htmlspecialchars($d['transaction_id']) ?></span></p>
                <p class="text-xs text-gray-500 mt-1"><?= date('d M Y, h:i A', strtotime($d['created_at'])) ?></p>
            </div>
            <div class="text-right">
                <span class="text-green-400 font-bold text-xl">+₹<?= $d['amount'] ?></span>
                <span class="block text-xs px-2 py-1 rounded mt-1 
                    <?= $d['status']=='Pending'?'bg-yellow-500/20 text-yellow-500':($d['status']=='Completed'?'bg-green-500/20 text-green-500':'bg-red-500/20 text-red-500') ?>">
                    <?= $d['status'] ?>
                </span>
            </div>
        </div>
        
        <?php if($d['status'] == 'Pending'): ?>
        <div class="flex gap-2 mt-3 pt-3 border-t border-gray-700">
            <a href="?action=approve&id=<?= $d['id'] ?>" onclick="return confirm('Approve this deposit?')" class="flex-1 bg-green-600 text-center text-white text-sm font-bold py-2 rounded-lg"><i class="fa-solid fa-check mr-1"></i> Approve</a>
            <a href="?action=reject&id=<?= $d['id'] ?>" onclick="return confirm('Reject this deposit?')" class="flex-1 bg-red-600 text-center text-white text-sm font-bold py-2 rounded-lg"><i class="fa-solid fa-xmark mr-1"></i> Reject</a>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php require 'common/bottom.php'; ?>