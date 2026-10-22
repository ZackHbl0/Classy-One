<?php
$dir = 'c:/laragon/www/Classy-One-Mobile/assets';

function listAssets($dir) {
    if (!is_dir($dir)) return [];
    $res = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $f) {
        if ($f->isFile()) {
            $res[] = str_replace('\\', '/', $f->getPathname());
        }
    }
    return $res;
}

$assets = listAssets($dir);
echo "Total assets: " . count($assets) . "\n";
foreach ($assets as $a) {
    echo $a . "\n";
}
