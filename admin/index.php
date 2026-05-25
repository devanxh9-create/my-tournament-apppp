<?php
require '../common/config.php';
if(!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }

$tot_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$tot_tournaments = $pdo->query("SELECT COUNT(*) FROM tournaments")->fetchColumn();
$prize_dist = $pdo->query("SELECT SUM(prize_pool) FROM tournaments WHERE status='Completed'")->fetchColumn() ?: 0;

// Revenue approx: (entry_fee * participants) - prize_pool for completed
$stmt = $pdo->query("SELECT t.prize_pool, t.entry_fee, (SELECT COUNT(*) FROM participants WHERE tournament_id = t.id) as players FROM tournaments t WHERE t.status='Completed'");
$revenue = 0;
while($row = $stmt->fetch()) {
    $revenue += (($row['entry_fee'] * $row['players']) - $row['prize_pool']);
}

require 'common/header.php';
?>
<h2 class="text-2xl font-bold mb-4 text-white">Dashboard Overview</h2>
<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="bg-gray-800 p-4 rounded-xl shadow-lg border border-gray-700">
        <i class="fa-solid fa-users text-blue-500 text-2xl mb-2"></i>
        <h4 class="text-gray-400 text-xs uppercase font-semibold">Total Users</h4>
        <p class="text-xl font-bold text-white"><?= $tot_users ?></p>
    </div>
    <div class="bg-gray-800 p-4 rounded-xl shadow-lg border border-gray-700">
        <i class="fa-solid fa-gamepad text-purple-500 text-2xl mb-2"></i>
        <h4 class="text-gray-400 text-xs uppercase font-semibold">Tournaments</h4>
        <p class="text-xl font-bold text-white"><?= $tot_tournaments ?></p>
    </div>
    <div class="bg-gray-800 p-4 rounded-xl shadow-lg border border-gray-700">
        <i class="fa-solid fa-trophy text-yellow-500 text-2xl mb-2"></i>
        <h4 class="text-gray-400 text-xs uppercase font-semibold">Prize Dist.</h4>
        <p class="text-xl font-bold text-white">₹<?= number_format($prize_dist, 2) ?></p>
    </div>
    <div class="bg-gray-800 p-4 rounded-xl shadow-lg border border-gray-700">
        <i class="fa-solid fa-chart-line text-green-500 text-2xl mb-2"></i>
        <h4 class="text-gray-400 text-xs uppercase font-semibold">Net Revenue</h4>
        <p class="text-xl font-bold text-white">₹<?= number_format($revenue, 2) ?></p>
    </div>
</div>
<a href="tournament.php" class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg shadow-lg mb-6">
    <i class="fa-solid fa-plus mr-2"></i> Create New Tournament
</a>
<?php require 'common/bottom.php'; ?>