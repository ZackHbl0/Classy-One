<?php
$file = 'c:/laragon/www/Classy-One-Mobile/lib/screens/parent_dashboard_screen.dart';
$lines = file($file);

$targets = [393, 426, 614, 862, 1989];
foreach ($targets as $lineNum) {
    echo "--- Around line $lineNum ---\n";
    for ($i = max(0, $lineNum - 5); $i <= min(count($lines), $lineNum + 5); $i++) {
        echo "$i: " . $lines[$i - 1];
    }
}
