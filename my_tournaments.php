<?php
require 'common/config.php';
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$user_id = $_SESSION['user_id'];
require 'common/header.php';
?>
<div class="flex justify-around bg-gray-800 rounded-lg p-1 mb-6">
    <button id="tab-live" class="w-1/2 text-center py-2 bg-gray-700 text-white font-bold rounded-md shadow" onclick="switchTab('live')">Live / Upcoming</button>
    <button id="tab-completed" class="w-1/2 text-center py-2 text-gray-400 font-bold rounded-md" onclick="switchTab('completed')">Completed</button>
</div>

<!-- Live / Upcoming Tab -->
<div id="content-live" class="space-y-4">
    <?php
    $stmt = $pdo->prepare("SELECT t.* FROM tournaments t JOIN participants p ON t.id = p.tournament_id WHERE p.user_id = ? AND t.status != 'Completed' ORDER BY t.match_time ASC");
    $stmt->execute([$user_id]);
    $live_t = $stmt->fetchAll();
    
    if(count($live_t) == 0) echo "<p class='text-gray-400 text-center'>You haven't joined any upcoming tournaments.</p>";
    foreach($live_t as $t): ?>
        <div class="bg-gray-800 rounded-xl p-4 shadow-lg border-l-4 <?= $t['status']=='Live'?'border-red-500':'border-blue-500' ?>">
            <div class="flex justify-between items-center mb-2">
                <h3 class="text-lg font-bold"><?= htmlspecialchars($t['title']) ?></h3>
                <span class="px-2 py-1 text-xs font-bold rounded-full <?= $t['status']=='Live'?'bg-red-500/20 text-red-500 animate-pulse':'bg-blue-500/20 text-blue-500' ?>"><?= $t['status'] ?></span>
            </div>
            <p class="text-gray-400 text-sm mb-3"><i class="fa-regular fa-clock mr-1"></i> <?= date('d M Y, h:i A', strtotime($t['match_time'])) ?></p>
            
            <?php if($t['status'] == 'Live' && !empty($t['room_id'])): ?>
            <div class="bg-gray-900 p-3 rounded-lg mt-2 border border-gray-700">
                <p class="text-sm"><span class="text-gray-500">Room ID:</span> <strong class="text-white"><?= htmlspecialchars($t['room_id']) ?></strong></p>
                <p class="text-sm"><span class="text-gray-500">Password:</span> <strong class="text-white"><?= htmlspecialchars($t['room_password']) ?></strong></p>
            </div>
            <?php else: ?>
            <p class="text-xs text-yellow-500 bg-yellow-500/10 p-2 rounded-lg text-center mt-2">Room details will be available when match is Live.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<!-- Completed Tab -->
<div id="content-completed" class="space-y-4 hidden">
    <?php
    $stmt = $pdo->prepare("SELECT t.* FROM tournaments t JOIN participants p ON t.id = p.tournament_id WHERE p.user_id = ? AND t.status = 'Completed' ORDER BY t.match_time DESC");
    $stmt->execute([$user_id]);
    $comp_t = $stmt->fetchAll();

    if(count($comp_t) == 0) echo "<p class='text-gray-400 text-center'>No completed tournaments found.</p>";
    foreach($comp_t as $t): ?>
        <div class="bg-gray-800 rounded-xl p-4 shadow-lg border border-gray-700">
            <h3 class="text-lg font-bold mb-1"><?= htmlspecialchars($t['title']) ?></h3>
            <p class="text-gray-400 text-sm mb-3"><i class="fa-regular fa-clock mr-1"></i> <?= date('d M Y', strtotime($t['match_time'])) ?></p>
            <?php if($t['winner_id'] == $user_id): ?>
                <div class="bg-green-500/20 text-green-400 border border-green-500 p-2 rounded-lg text-center font-bold text-sm">
                    <i class="fa-solid fa-trophy mr-1"></i> Winner! Prize: ₹<?= $t['prize_pool'] ?>
                </div>
            <?php else: ?>
                <div class="bg-gray-700 p-2 rounded-lg text-center text-sm text-gray-300">Participated</div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<script>
    function switchTab(tab) {
        if(tab === 'live') {
            document.getElementById('content-live').classList.remove('hidden');
            document.getElementById('content-completed').classList.add('hidden');
            document.getElementById('tab-live').className = 'w-1/2 text-center py-2 bg-gray-700 text-white font-bold rounded-md shadow';
            document.getElementById('tab-completed').className = 'w-1/2 text-center py-2 text-gray-400 font-bold rounded-md';
        } else {
            document.getElementById('content-completed').classList.remove('hidden');
            document.getElementById('content-live').classList.add('hidden');
            document.getElementById('tab-completed').className = 'w-1/2 text-center py-2 bg-gray-700 text-white font-bold rounded-md shadow';
            document.getElementById('tab-live').className = 'w-1/2 text-center py-2 text-gray-400 font-bold rounded-md';
        }
    }
</script>
<?php require 'common/bottom.php'; ?>