<?php
require '../common/config.php';
if(!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }

// ==========================================
// 🚀 AUTO-FIX DATABASE (Columns automatically bana dega)
// ==========================================
try {
    // 1. Commission column check/add
    $pdo->exec("ALTER TABLE tournaments ADD commission INT NOT NULL DEFAULT 20");
} catch (PDOException $e) {}

try {
    // 2. Match Type column (Solo, Duo, Squad) automatically add karega
    $pdo->exec("ALTER TABLE tournaments ADD match_type VARCHAR(20) NOT NULL DEFAULT 'Solo'");
} catch (PDOException $e) {}
// ==========================================

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_tournament'])) {
    $title = $_POST['title'];
    $game = $_POST['game_name'];
    $entry = $_POST['entry_fee'];
    $prize = $_POST['prize_pool'];
    $time = $_POST['match_time'];
    $comm = $_POST['commission'];
    $mtype = $_POST['match_type']; // Naya field fetch kiya
    
    try {
        $stmt = $pdo->prepare("INSERT INTO tournaments (title, game_name, entry_fee, prize_pool, match_time, commission, match_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if($stmt->execute([$title, $game, $entry, $prize, $time, $comm, $mtype])) {
            echo "<script>alert('Tournament Created Successfully!'); window.location.href='tournament.php';</script>"; 
            exit;
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error saving tournament: " . addslashes($e->getMessage()) . "');</script>";
    }
}

if(isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM tournaments WHERE id=?")->execute([$_GET['delete']]);
    header("Location: tournament.php"); exit;
}

require 'common/header.php';
?>
<button onclick="document.getElementById('add-modal').classList.remove('hidden')" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-lg shadow-lg mb-6"><i class="fa-solid fa-plus mr-2"></i> Create Tournament</button>

<h3 class="text-xl font-bold mb-3">All Tournaments</h3>
<div class="space-y-4">
    <?php
    try {
        $stmt = $pdo->query("SELECT * FROM tournaments ORDER BY id DESC");
        while($t = $stmt->fetch()):
            $status = isset($t['status']) ? $t['status'] : 'Upcoming';
            $matchType = isset($t['match_type']) ? $t['match_type'] : 'Solo'; // Match type fallback
    ?>
    <div class="bg-gray-800 p-4 rounded-xl shadow border-l-4 <?= $status=='Completed'?'border-green-500':($status=='Live'?'border-red-500':'border-blue-500') ?>">
        <div class="flex justify-between items-start mb-2">
            <div>
                <h4 class="font-bold text-lg"><?= htmlspecialchars($t['title']) ?></h4>
                <p class="text-xs text-gray-400">
                    <?= htmlspecialchars($t['game_name']) ?> (<?= htmlspecialchars($matchType) ?>) • <?= date('d M Y, h:i A', strtotime($t['match_time'])) ?>
                </p>
            </div>
            <span class="text-xs px-2 py-1 rounded bg-gray-700 text-white font-semibold"><?= htmlspecialchars($status) ?></span>
        </div>
        <div class="flex justify-between mt-3 gap-2">
            <a href="manage_tournament.php?id=<?= $t['id'] ?>" class="flex-1 text-center bg-gray-700 text-white text-sm py-2 rounded-lg font-bold"><i class="fa-solid fa-gear mr-1"></i> Manage</a>
            <a href="?delete=<?= $t['id'] ?>" onclick="return confirm('Are you sure you want to delete this tournament?')" class="flex-1 text-center bg-red-900/40 text-red-400 text-sm py-2 rounded-lg font-bold border border-red-800"><i class="fa-solid fa-trash mr-1"></i> Delete</a>
        </div>
    </div>
    <?php 
        endwhile;
    } catch (PDOException $e) {
        echo "<div class='text-red-500 font-bold p-4 bg-gray-800 rounded'>Error loading tournaments: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    ?>
</div>

<div id="add-modal" class="hidden fixed inset-0 bg-black/90 flex items-center justify-center z-[100] p-4">
    <div class="bg-gray-900 rounded-2xl p-6 w-full max-w-sm border border-gray-700 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold mb-4">Create Tournament</h2>
        <form method="POST" action="">
            <input type="text" name="title" placeholder="Tournament Title" required class="w-full bg-gray-950 border border-gray-700 rounded-lg px-4 py-2 text-white mb-3">
            
            <input type="text" name="game_name" placeholder="Game Name (e.g., BGMI)" required class="w-full bg-gray-950 border border-gray-700 rounded-lg px-4 py-2 text-white mb-3">
            
            <select name="match_type" required class="w-full bg-gray-950 border border-gray-700 rounded-lg px-4 py-2 text-white mb-3" style="color-scheme: dark;">
                <option value="Solo">Solo</option>
                <option value="Duo">Duo</option>
                <option value="Squad">Squad</option>
            </select>

            <div class="flex gap-2 mb-3">
                <input type="number" name="entry_fee" placeholder="Entry Fee (₹)" required class="w-1/2 bg-gray-950 border border-gray-700 rounded-lg px-4 py-2 text-white">
                <input type="number" name="prize_pool" placeholder="Prize Pool (₹)" required class="w-1/2 bg-gray-950 border border-gray-700 rounded-lg px-4 py-2 text-white">
            </div>
            
            <input type="datetime-local" name="match_time" required class="w-full bg-gray-950 border border-gray-700 rounded-lg px-4 py-2 text-white mb-3" style="color-scheme: dark;">
            
            <input type="number" name="commission" placeholder="Commission % (e.g., 20)" value="20" class="w-full bg-gray-950 border border-gray-700 rounded-lg px-4 py-2 text-white mb-4">
            
            <div class="flex gap-3">
                <button type="submit" name="add_tournament" class="flex-1 bg-indigo-600 text-white font-bold py-2 rounded-lg">Save</button>
                <button type="button" onclick="document.getElementById('add-modal').classList.add('hidden')" class="flex-1 bg-gray-700 text-white font-bold py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>
</div>
<?php require 'common/bottom.php'; ?>
