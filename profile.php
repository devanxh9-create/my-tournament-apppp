<?php
require 'common/config.php';
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$user_id = $_SESSION['user_id'];
$msg = '';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $email = trim($_POST['email']);
    $upi = trim($_POST['upi_id']);
    $pdo->prepare("UPDATE users SET email=?, upi_id=? WHERE id=?")->execute([$email, $upi, $user_id]);
    $msg = "<p class='text-green-500 text-sm mb-3'>Profile updated successfully.</p>";
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $old = $_POST['old_password'];
    $new = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id=?");
    $stmt->execute([$user_id]);
    if(password_verify($old, $stmt->fetchColumn())) {
        $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$new, $user_id]);
        $msg = "<p class='text-green-500 text-sm mb-3'>Password changed successfully.</p>";
    } else {
        $msg = "<p class='text-red-500 text-sm mb-3'>Old password is incorrect.</p>";
    }
}

$stmt = $pdo->prepare("SELECT username, email, upi_id FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

require 'common/header.php';
?>
<h2 class="text-2xl font-bold mb-4">My Profile</h2>
<?= $msg ?>
<div class="bg-gray-800 rounded-xl p-6 shadow-lg mb-6 border border-gray-700">
    <div class="flex items-center gap-4 mb-6">
        <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center text-2xl font-bold">
            <?= strtoupper(substr($user['username'],0,1)) ?>
        </div>
        <div>
            <h3 class="text-xl font-bold text-white"><?= htmlspecialchars($user['username']) ?></h3>
            <p class="text-sm text-gray-400">Player</p>
        </div>
    </div>
    
    <form method="POST" action="" class="mb-6 border-b border-gray-700 pb-6">
        <div class="mb-4">
            <label class="block text-gray-400 text-sm mb-1">Email Address</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500">
        </div>
        <div class="mb-4">
            <label class="block text-gray-400 text-sm mb-1">Your UPI ID (For Withdrawals)</label>
            <input type="text" name="upi_id" value="<?= htmlspecialchars($user['upi_id'] ?? '') ?>" placeholder="e.g. yourname@paytm" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500">
        </div>
        <button type="submit" name="update_profile" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded-lg transition">Update Profile</button>
    </form>
    
    <form method="POST" action="" class="mb-6">
        <h3 class="text-lg font-bold mb-3">Change Password</h3>
        <div class="mb-4">
            <input type="password" name="old_password" placeholder="Old Password" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white mb-2 focus:outline-none">
            <input type="password" name="new_password" placeholder="New Password" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none">
        </div>
        <button type="submit" name="update_password" class="w-full bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 rounded-lg transition">Change Password</button>
    </form>
</div>

<div class="mt-8 text-center">
    <a href="logout.php" class="text-red-500 font-bold"><i class="fa-solid fa-right-from-bracket mr-1"></i> Logout</a>
</div>

<div class="mt-6 bg-gray-800 border border-green-600/50 rounded-xl p-4 text-center shadow-lg mb-10">
    <h3 class="text-lg font-bold text-white mb-1">Help & Support</h3>
    <p class="text-gray-400 text-xs mb-3">Agar aapko koi problem hai, toh humse contact karein.</p>
    <a href="https://wa.me/919235496391" target="_blank" class="inline-flex items-center justify-center w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-4 rounded-lg transition">
        <i class="fa-brands fa-whatsapp text-xl mr-2"></i> WhatsApp: 9235496391
    </a>
</div>

<?php require 'common/bottom.php'; ?>
