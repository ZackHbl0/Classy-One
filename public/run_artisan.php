<?php
chdir(__DIR__ . '/..');
echo "CWD: " . getcwd() . "<br>";
$output = shell_exec('php artisan config:clear 2>&1');
$output .= "\n" . shell_exec('php artisan route:clear 2>&1');
$output .= "\n" . shell_exec('php artisan view:clear 2>&1');
echo "<h2>Artisan Output</h2><pre>$output</pre>";
