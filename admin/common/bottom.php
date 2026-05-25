</div>
    <?php if(isset($_SESSION['admin_id'])): 
        $current = basename($_SERVER['PHP_SELF']);
    ?>
    <div class="fixed bottom-0 w-full bg-gray-900 flex justify-around p-3 pb-safe shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.3)] z-50 border-t border-gray-800 h-16">
        <a href="index.php" class="text-center flex flex-col items-center <?= $current=='index.php'?'text-indigo-500':'text-gray-500 hover:text-indigo-400' ?>">
            <i class="fa-solid fa-chart-pie text-xl mb-1"></i><span class="text-[10px]">Dashboard</span>
        </a>
        <a href="tournament.php" class="text-center flex flex-col items-center <?= ($current=='tournament.php'||$current=='manage_tournament.php')?'text-indigo-500':'text-gray-500 hover:text-indigo-400' ?>">
            <i class="fa-solid fa-gamepad text-xl mb-1"></i><span class="text-[10px]">Tourneys</span>
        </a>
        <a href="user.php" class="text-center flex flex-col items-center <?= $current=='user.php'?'text-indigo-500':'text-gray-500 hover:text-indigo-400' ?>">
            <i class="fa-solid fa-users text-xl mb-1"></i><span class="text-[10px]">Users</span>
        </a>
        <a href="setting.php" class="text-center flex flex-col items-center <?= $current=='setting.php'?'text-indigo-500':'text-gray-500 hover:text-indigo-400' ?>">
            <i class="fa-solid fa-gear text-xl mb-1"></i><span class="text-[10px]">Settings</span>
        </a>
    </div>
    <?php endif; ?>
</body>
</html>