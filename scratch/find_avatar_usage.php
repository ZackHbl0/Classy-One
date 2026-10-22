<?php
$dir = 'c:/laragon/www/Classy-One-Mobile/lib';

function searchAvatar($dir) {
    $res = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $f) {
        if ($f->isFile() && $f->getExtension() === 'dart') {
            $cnt = file_get_contents($f->getPathname());
            if (stripos($cnt, 'avatar') !== false || stripos($cnt, 'image') !== false) {
                // look for asset paths
                preg_match_all('/assets\/[a-zA-Z0-9_\-\.\/]+/', $cnt, $m);
                if (!empty($m[0])) {
                    $res = array_merge($res, $m[0]);
                }
            }
        }
    }
    return array_unique($res);
}

$found = searchAvatar($dir);
print_r($found);
