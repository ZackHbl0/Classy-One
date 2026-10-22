<?php
$brainDir = 'C:/Users/zakar/.gemini/antigravity-ide/brain/a845819f-2fba-49d9-81a6-fd054f5bfc25';
$destDir = 'c:/laragon/www/Classy-One-Mobile/assets/images';

$files = glob($brainDir . '/child_avatar_*.jpg');
foreach ($files as $file) {
    $base = basename($file);
    if (strpos($base, 'child_avatar_boy1') !== false) {
        copy($file, $destDir . '/child_avatar_boy1.jpg');
        echo "Copied to child_avatar_boy1.jpg\n";
    } elseif (strpos($base, 'child_avatar_boy2') !== false) {
        copy($file, $destDir . '/child_avatar_boy2.jpg');
        echo "Copied to child_avatar_boy2.jpg\n";
    } elseif (strpos($base, 'child_avatar_girl1') !== false) {
        copy($file, $destDir . '/child_avatar_girl1.jpg');
        echo "Copied to child_avatar_girl1.jpg\n";
    }
}
