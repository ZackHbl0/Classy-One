<?php
$dirs = ['c:/laragon/www/Classy-One', 'c:/laragon/www/Classy-One-Mobile'];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $f) {
        if ($f->isFile()) {
            $name = $f->getFilename();
            $path = $f->getPathname();
            if (strpos($path, 'vendor') !== false || strpos($path, 'node_modules') !== false || strpos($path, '.dart_tool') !== false) {
                continue;
            }
            if (preg_match('/(firebase|service[-_]account|google.*services)/i', $name)) {
                echo $path . "\n";
            }
        }
    }
}
