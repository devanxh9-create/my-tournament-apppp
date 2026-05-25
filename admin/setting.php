<?php
require '../common/config.php';
if(!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }

$msg = '';

// Handle Password Update
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $old = $_POST['old_password'];
    $new = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("SELECT password FROM admin WHERE id=?");
    $stmt->execute([$_SESSION['admin_id']]);
    if(password_verify($old, $stmt->fetchColumn())) {
        $pdo->prepare("UPDATE admin SET password=? WHERE id=?")->execute([$new, $_SESSION['admin_id']]);
        $msg = "<p class='text-green-500 text-sm mb-3'>Password updated successfully.</p>";
    } else {
        $msg = "<p class='text-red-500 text-sm mb-3'>Old password is incorrect.</p>";
    }
}

// Handle Payment Settings Update
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_payment'])) {
    $upi_id = trim($_POST['admin_upi_id']);
    $qr_image = $_POST['existing_qr'];

    // Handle File Upload
    if(isset($_FILES['qr_image']) && $_FILES['qr_image']['error'] == 0) {
        $dir = '../uploads/';
        if(!is_dir($dir)) mkdir($dir, 0777, true); // Auto-create uploads folder
        
        $ext = strtolower(pathinfo($_FILES['qr_image']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $filename = 'qr_' . time() . '.' . $ext;
            if(move_uploaded_file($_FILES['qr_image']['tmp_name'], $dir . $filename)) {
                $qr_image = $filename;
            }
        } else {
            $msg = "<p class='text-red-500 text-sm mb-3'>Only JPG and PNG images allowed.</p>";
        }
    }
    
    $pdo->prepare("UPDATE app_settings SET admin_upi_id=?, admin_qr_image=? WHERE id=1")->execute([$upi_id, $qr_image]);
    if(!$msg) $msg = "<p class='text-green-500 text-sm mb-3'>Payment settings updated successfully.</p>";
}

$settings = $pdo->query("SELECT * FROM app_settings WHERE id=1")->fetch();

require 'common/header.php';
?>
<div class="flex justify-between items-center mb-4">
    <h2 class="text-2xl font-bold">App Settings</h2>
    <div class="flex gap-2">
        <a href="deposits.php" class="bg-green-600 hover:bg-green-700 text-xs text-white px-3 py-2 rounded shadow font-bold"><i class="fa-solid fa-arrow-down mr-1"></i> Deposits</a>
        <a href="withdrawals.php" class="bg-red-600 hover:bg-red-700 text-xs text-white px-3 py-2 rounded shadow font-bold"><i class="fa-solid fa-arrow-up mr-1"></i> Withdrawals</a>
    </div>
</div>

<?= $msg ?>

<!-- Payment Settings Card -->
<div class="bg-gray-800 rounded-2xl p-6 shadow-lg border border-gray-700 mb-6">
    <h3 class="text-lg font-bold mb-3"><i class="fa-solid fa-qrcode text-indigo-500 mr-2"></i> Payment Setup (UPI/QR)</h3>
    <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="existing_qr" value="<?= htmlspecialchars($settings['admin_qr_image'] ?? '') ?>">
        
        <label class="block text-gray-400 text-sm mb-1">Admin UPI ID</label>
        <input type="text" name="admin_upi_id" value="<?= htmlspecialchars($settings['admin_upi_id'] ?? '') ?>" placeholder="e.g. yourname@upi" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white mb-3 focus:outline-none focus:border-indigo-500">
        
        <label class="block text-gray-400 text-sm mb-1">Payment QR Code Image</label>
        <?php if(!empty($settings['admin_qr_image'])): ?>
            <div class="mb-3">
                <img src="../uploads/<?= htmlspecialchars($settings['admin_qr_image']) ?>" alt="QR Code" class="w-32 h-32 object-contain rounded border border-gray-600 bg-white">
            </div>
        <?php endif; ?>
        <input type="file" name="qr_image" accept="image/*" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white mb-4 focus:outline-none focus:border-indigo-500">
        
        <button type="submit" name="update_payment" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg shadow transition">Save Payment Details</button>
    </form>
</div>

<!-- Password Update Card -->
<div class="bg-gray-800 rounded-2xl p-6 shadow-lg border border-gray-700">
    <h3 class="text-lg font-bold mb-3"><i class="fa-solid fa-lock text-indigo-500 mr-2"></i> Update Password</h3>
    <form method="POST" action="">
        <input type="password" name="old_password" placeholder="Current Admin Password" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white mb-3 focus:outline-none focus:border-indigo-500">
        <input type="password" name="new_password" placeholder="New Admin Password" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white mb-4 focus:outline-none focus:border-indigo-500">
        <button type="submit" name="update_password" class="w-full bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 rounded-lg shadow transition">Update Authentication</button>
    </form>
</div>
<?php require 'common/bottom.php'; ?>