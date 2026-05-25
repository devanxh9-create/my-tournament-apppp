<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Adept Play</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            -webkit-user-select: none; 
            -moz-user-select: none; 
            -ms-user-select: none; 
            user-select: none; 
            touch-action: pan-y;
        }
        ::-webkit-scrollbar { display: none; }
    </style>
    <script>
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('keydown', e => { 
            if (e.ctrlKey && (e.key === '=' || e.key === '-' || e.key === '0')) e.preventDefault(); 
        });
    </script>
</head>
<body class="bg-gray-900 text-white font-sans pb-24">
    <div class="fixed top-0 w-full bg-gray-800 p-4 shadow-md z-50 flex justify-between items-center h-16">
        <div class="text-xl font-bold text-blue-500 tracking-wide">
            <i class="fa-solid fa-gamepad mr-2"></i>Adept Play
        </div>
        <?php if(isset($_SESSION['user_id'])): 
            $stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id=?");
            $stmt->execute([$_SESSION['user_id']]);
            $bal = $stmt->fetchColumn() ?: 0;
        ?>
            <a href="wallet.php" class="bg-gray-700 px-4 py-1.5 rounded-full text-sm font-semibold border border-gray-600 shadow-sm flex items-center">
                <i class="fa-solid fa-wallet text-yellow-400 mr-2"></i> ₹<?= number_format($bal, 2) ?>
            </a>
        <?php endif; ?>
    </div>
    <div class="pt-20 px-4">