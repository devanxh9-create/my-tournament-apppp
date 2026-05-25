</div>
    <?php if(isset($_SESSION['user_id'])): 
        $current = basename($_SERVER['PHP_SELF']);
    ?>
    <div class="fixed bottom-0 w-full bg-gray-800 flex justify-around p-3 pb-safe shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] z-50 border-t border-gray-700 h-16">
        <a href="index.php" class="text-center flex flex-col items-center <?= $current=='index.php'?'text-blue-500':'text-gray-400 hover:text-blue-400' ?>">
            <i class="fa-solid fa-house text-xl mb-1"></i><span class="text-[10px]">Home</span>
        </a>
        <a href="my_tournaments.php" class="text-center flex flex-col items-center <?= $current=='my_tournaments.php'?'text-blue-500':'text-gray-400 hover:text-blue-400' ?>">
            <i class="fa-solid fa-trophy text-xl mb-1"></i><span class="text-[10px]">Matches</span>
        </a>
        <a href="wallet.php" class="text-center flex flex-col items-center <?= $current=='wallet.php'?'text-blue-500':'text-gray-400 hover:text-blue-400' ?>">
            <i class="fa-solid fa-wallet text-xl mb-1"></i><span class="text-[10px]">Wallet</span>
        </a>
        <a href="profile.php" class="text-center flex flex-col items-center <?= $current=='profile.php'?'text-blue-500':'text-gray-400 hover:text-blue-400' ?>">
            <i class="fa-solid fa-user text-xl mb-1"></i><span class="text-[10px]">Profile</span>
        </a>
    </div>
    <?php endif; ?>
</body>
</html>