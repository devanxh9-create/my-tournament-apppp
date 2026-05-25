<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Admin - Adept Play</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { -webkit-user-select: none; user-select: none; touch-action: pan-y; }
        ::-webkit-scrollbar { display: none; }
    </style>
    <script>
        document.addEventListener('contextmenu', e => e.preventDefault());
    </script>
</head>
<body class="bg-gray-950 text-white font-sans pb-24">
    <div class="fixed top-0 w-full bg-indigo-900 p-4 shadow-md z-50 flex justify-between items-center h-16 border-b border-indigo-800">
        <div class="text-xl font-bold text-white tracking-wide">
            <i class="fa-solid fa-user-tie mr-2"></i>Admin Panel
        </div>
        <?php if(isset($_SESSION['admin_id'])): ?>
            <a href="logout.php" class="text-indigo-200 hover:text-white"><i class="fa-solid fa-right-from-bracket text-xl"></i></a>
        <?php endif; ?>
    </div>
    <div class="pt-20 px-4 max-w-2xl mx-auto">