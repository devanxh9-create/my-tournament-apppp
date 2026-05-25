<?php
require 'common/config.php';
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$user_id = $_SESSION['user_id'];

// Get User Info
$stmt = $pdo->prepare("SELECT wallet_balance, upi_id FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$balance = $user['wallet_balance'];

// Get Admin Payment Settings
$settings = $pdo->query("SELECT * FROM app_settings WHERE id=1")->fetch();

// Add Money (Deposit Request)
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_deposit'])) {
    $amount = floatval($_POST['amount']);
    $txn_id = trim($_POST['transaction_id']);
    
    if($amount > 0 && !empty($txn_id)) {
        $pdo->prepare("INSERT INTO deposits (user_id, amount, transaction_id) VALUES (?, ?, ?)")->execute([$user_id, $amount, $txn_id]);
        echo "<script>alert('Deposit request submitted! Admin will verify and add funds.'); window.location.href='wallet.php';</script>"; exit;
    }
}

// Withdraw Money (Withdrawal Request)
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_withdraw'])) {
    $amount = floatval($_POST['w_amount']);
    
    if(empty($user['upi_id'])) {
        echo "<script>alert('Please save your UPI ID in Profile first!'); window.location.href='profile.php';</script>"; exit;
    }
    
    if($amount >= 50 && $amount <= $balance) { // Assuming Min withdrawal 50
        $pdo->beginTransaction();
        try {
            // Deduct balance immediately to prevent double spending
            $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id=?")->execute([$amount, $user_id]);
            $pdo->prepare("INSERT INTO withdrawals (user_id, amount) VALUES (?, ?)")->execute([$user_id, $amount]);
            $pdo->commit();
            echo "<script>alert('Withdrawal request submitted!'); window.location.href='wallet.php';</script>"; exit;
        } catch(Exception $e) {
            $pdo->rollBack();
        }
    } else {
        echo "<script>alert('Invalid amount or insufficient balance (Min ₹50).');</script>";
    }
}

require 'common/header.php';
?>
<!-- Balance Card -->
<div class="bg-gradient-to-r from-blue-700 to-indigo-800 rounded-2xl p-6 shadow-xl mb-6 text-center relative overflow-hidden">
    <i class="fa-solid fa-wallet text-6xl text-white/10 absolute -right-4 -bottom-4"></i>
    <p class="text-blue-200 text-sm font-semibold uppercase tracking-wider mb-2">Total Balance</p>
    <h1 class="text-4xl font-bold text-white mb-6">₹<?= number_format($balance, 2) ?></h1>
    <div class="flex gap-4">
        <button onclick="document.getElementById('deposit-modal').classList.remove('hidden')" class="flex-1 bg-white text-blue-800 font-bold py-2 rounded-lg shadow">Add Money</button>
        <button onclick="document.getElementById('withdraw-modal').classList.remove('hidden')" class="flex-1 bg-blue-600 border border-blue-400 text-white font-bold py-2 rounded-lg shadow">Withdraw</button>
    </div>
</div>

<!-- Transactions / Pending Requests -->
<h3 class="text-lg font-bold mb-3 text-gray-300">Recent Activity</h3>
<div class="bg-gray-800 rounded-xl shadow-lg border border-gray-700 overflow-hidden">
    <?php
    $stmt = $pdo->prepare("SELECT 'txn' as tbl, amount, type, description, created_at FROM transactions WHERE user_id=? 
                           UNION ALL 
                           SELECT 'dep' as tbl, amount, 'credit' as type, CONCAT('Deposit (', status, ')') as description, created_at FROM deposits WHERE user_id=? 
                           UNION ALL 
                           SELECT 'wit' as tbl, amount, 'debit' as type, CONCAT('Withdrawal (', status, ')') as description, created_at FROM withdrawals WHERE user_id=? 
                           ORDER BY created_at DESC LIMIT 20");
    $stmt->execute([$user_id, $user_id, $user_id]);
    $activities = $stmt->fetchAll();
    
    if(count($activities) == 0) echo "<p class='p-4 text-center text-gray-500'>No activity yet.</p>";
    foreach($activities as $act): 
        $is_cr = $act['type'] == 'credit';
        $is_pending = strpos($act['description'], 'Pending') !== false;
    ?>
    <div class="flex justify-between items-center p-4 border-b border-gray-700 last:border-0 opacity-<?= $is_pending ? '60' : '100' ?>">
        <div class="flex items-center">
            <div class="<?= $is_cr ? 'bg-green-500/20 text-green-500' : 'bg-red-500/20 text-red-500' ?> p-2 rounded-full mr-3">
                <i class="fa-solid <?= $is_cr ? 'fa-arrow-down' : 'fa-arrow-up' ?>"></i>
            </div>
            <div>
                <p class="text-sm text-white font-semibold"><?= htmlspecialchars($act['description']) ?></p>
                <p class="text-xs text-gray-500"><?= date('d M y, h:i a', strtotime($act['created_at'])) ?></p>
            </div>
        </div>
        <div class="font-bold <?= $is_cr ? 'text-green-400' : 'text-red-400' ?>">
            <?= $is_cr ? '+' : '-' ?>₹<?= $act['amount'] ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Add Money Modal -->
<div id="deposit-modal" class="hidden fixed inset-0 bg-black/80 flex items-center justify-center z-[100] p-4">
    <div class="bg-gray-800 rounded-2xl p-6 w-full max-w-sm max-h-[90vh] overflow-y-auto border border-gray-700">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Add Money</h2>
            <button onclick="document.getElementById('deposit-modal').classList.add('hidden')" class="text-gray-400 text-xl"><i class="fa-solid fa-xmark"></i></button>
        </div>
        
        <?php if(!empty($settings['admin_upi_id'])): ?>
            <div class="bg-gray-900 p-4 rounded-lg mb-4 text-center border border-gray-700">
                <p class="text-sm text-gray-400 mb-2">Scan QR or send to UPI ID</p>
                <?php if(!empty($settings['admin_qr_image'])): ?>
                    <img src="uploads/<?= $settings['admin_qr_image'] ?>" alt="QR" class="w-40 h-40 mx-auto rounded mb-3 bg-white p-1">
                <?php endif; ?>
                <div class="bg-gray-800 px-3 py-2 rounded text-indigo-400 font-mono font-bold select-all">
                    <?= htmlspecialchars($settings['admin_upi_id']) ?>
                </div>
            </div>
            
            <form method="POST" action="">
                <input type="number" name="amount" placeholder="Amount Paid (₹)" required min="10" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none mb-3">
                <input type="text" name="transaction_id" placeholder="UPI Transaction/UTR ID" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none mb-4">
                <button type="submit" name="submit_deposit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition shadow">Submit Deposit Request</button>
            </form>
        <?php else: ?>
            <p class="text-center text-red-400 py-4">Deposit system is currently unavailable (Admin UPI not set).</p>
        <?php endif; ?>
    </div>
</div>

<!-- Withdraw Modal -->
<div id="withdraw-modal" class="hidden fixed inset-0 bg-black/80 flex items-center justify-center z-[100] p-4">
    <div class="bg-gray-800 rounded-2xl p-6 w-full max-w-sm border border-gray-700">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Withdraw Money</h2>
            <button onclick="document.getElementById('withdraw-modal').classList.add('hidden')" class="text-gray-400 text-xl"><i class="fa-solid fa-xmark"></i></button>
        </div>
        
        <?php if(empty($user['upi_id'])): ?>
            <div class="text-center py-4">
                <i class="fa-solid fa-circle-exclamation text-yellow-500 text-3xl mb-2"></i>
                <p class="text-gray-300 mb-4 text-sm">You need to save your UPI ID in your profile before withdrawing.</p>
                <a href="profile.php" class="inline-block bg-blue-600 px-4 py-2 rounded font-bold text-white">Go to Profile</a>
            </div>
        <?php else: ?>
            <p class="text-sm text-gray-400 mb-1">Withdrawing to UPI:</p>
            <div class="bg-gray-900 px-3 py-2 rounded text-indigo-400 font-mono font-bold mb-4 border border-gray-700">
                <?= htmlspecialchars($user['upi_id']) ?>
            </div>
            
            <form method="POST" action="">
                <input type="number" name="w_amount" placeholder="Amount (Min ₹50)" required min="50" max="<?= $balance ?>" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none mb-4">
                <button type="submit" name="submit_withdraw" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition shadow">Submit Withdrawal Request</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require 'common/bottom.php'; ?>