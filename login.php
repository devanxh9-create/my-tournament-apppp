<?php
require 'common/config.php';
if(isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            if($user['is_blocked']) { $msg = "<p class='text-red-500 text-sm mb-3'>Your account is blocked.</p>"; }
            else { $_SESSION['user_id'] = $user['id']; header("Location: index.php"); exit; }
        } else {
            $msg = "<p class='text-red-500 text-sm mb-3'>Invalid username or password.</p>";
        }
    } elseif (isset($_POST['signup'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if($stmt->rowCount() > 0) {
            $msg = "<p class='text-red-500 text-sm mb-3'>Username or Email already exists.</p>";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if($stmt->execute([$username, $email, $password])) {
                $msg = "<p class='text-green-500 text-sm mb-3'>Account created successfully! Please login.</p>";
            }
        }
    }
}
require 'common/header.php';
?>
<div class="max-w-md mx-auto bg-gray-800 rounded-2xl p-6 shadow-lg mt-10">
    <div class="flex justify-around border-b border-gray-700 pb-2 mb-6">
        <button id="tab-login" class="text-blue-500 font-bold border-b-2 border-blue-500 pb-1 px-4" onclick="switchTab('login')">Login</button>
        <button id="tab-signup" class="text-gray-400 font-bold pb-1 px-4" onclick="switchTab('signup')">Sign Up</button>
    </div>
    <?= $msg ?>
    <!-- Login Form -->
    <form id="form-login" method="POST" action="">
        <div class="mb-4">
            <label class="block text-gray-400 text-sm mb-1">Username</label>
            <input type="text" name="username" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
        </div>
        <div class="mb-6">
            <label class="block text-gray-400 text-sm mb-1">Password</label>
            <input type="password" name="password" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
        </div>
        <button type="submit" name="login" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition">Login</button>
    </form>
    <!-- Signup Form -->
    <form id="form-signup" method="POST" action="" class="hidden">
        <div class="mb-4">
            <label class="block text-gray-400 text-sm mb-1">Username</label>
            <input type="text" name="username" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
        </div>
        <div class="mb-4">
            <label class="block text-gray-400 text-sm mb-1">Email</label>
            <input type="email" name="email" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
        </div>
        <div class="mb-6">
            <label class="block text-gray-400 text-sm mb-1">Password</label>
            <input type="password" name="password" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
        </div>
        <button type="submit" name="signup" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition">Sign Up</button>
    </form>
</div>
<script>
    function switchTab(tab) {
        if(tab === 'login') {
            document.getElementById('form-login').classList.remove('hidden');
            document.getElementById('form-signup').classList.add('hidden');
            document.getElementById('tab-login').classList.add('text-blue-500', 'border-b-2', 'border-blue-500');
            document.getElementById('tab-login').classList.remove('text-gray-400');
            document.getElementById('tab-signup').classList.remove('text-blue-500', 'border-b-2', 'border-blue-500');
            document.getElementById('tab-signup').classList.add('text-gray-400');
        } else {
            document.getElementById('form-signup').classList.remove('hidden');
            document.getElementById('form-login').classList.add('hidden');
            document.getElementById('tab-signup').classList.add('text-blue-500', 'border-b-2', 'border-blue-500');
            document.getElementById('tab-signup').classList.remove('text-gray-400');
            document.getElementById('tab-login').classList.remove('text-blue-500', 'border-b-2', 'border-blue-500');
            document.getElementById('tab-login').classList.add('text-gray-400');
        }
    }
</script>
<?php require 'common/bottom.php'; ?>