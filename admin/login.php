<?php
require '../common/config.php';
if(isset($_SESSION['admin_id'])) { header("Location: index.php"); exit; }

$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: index.php"); exit;
    } else {
        $msg = "<p class='text-red-500 text-sm mb-3'>Invalid Admin Credentials.</p>";
    }
}
require 'common/header.php';
?>
<div class="bg-gray-900 rounded-2xl p-6 shadow-2xl mt-10 border border-gray-800">
    <div class="text-center mb-6">
        <i class="fa-solid fa-shield-halved text-5xl text-indigo-500 mb-3"></i>
        <h2 class="text-2xl font-bold text-white">Admin Secure Login</h2>
    </div>
    <?= $msg ?>
    <form method="POST" action="">
        <div class="mb-4">
            <input type="text" name="username" placeholder="Admin Username" required class="w-full bg-gray-950 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-indigo-500">
        </div>
        <div class="mb-6">
            <input type="password" name="password" placeholder="Password" required class="w-full bg-gray-950 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-indigo-500">
        </div>
        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg transition shadow-lg">Authenticate</button>
    </form>
</div>
<?php require 'common/bottom.php'; ?>