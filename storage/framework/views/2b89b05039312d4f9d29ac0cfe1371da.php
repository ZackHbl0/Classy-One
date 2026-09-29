<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — ClassyOne</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        html, body {
            width: 100%;
            height: 100%;
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            overflow-x: hidden;
        }
    </style>
    <?php echo \Filament\Support\Facades\FilamentAsset::renderStyles() ?>
</head>
<body class="antialiased h-full">
    <?php echo e($slot); ?>

    <?php echo \Filament\Support\Facades\FilamentAsset::renderScripts() ?>
</body>
</html>
<?php /**PATH C:\laragon\www\Classy-One\resources\views/layouts/filament-login.blade.php ENDPATH**/ ?>