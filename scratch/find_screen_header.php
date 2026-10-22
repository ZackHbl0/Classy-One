<?php
function searchDir($dir) {
    foreach (scandir($dir) as $f) {
        if ($f === '.' || $f === '..') continue;
        $path = $dir . '/' . $f;
        if (is_dir($path)) searchDir($path);
        elseif (str_ends_with($path, '.dart')) {
            $c = file_get_contents($path);
            if (strpos($c, 'Emploi du temps') !== false || strpos($c, 'ScreenHeader') !== false) {
                echo $path . "\n";
            }
        }
    }
}
searchDir('C:/laragon/www/Classy-One-Mobile/lib');
