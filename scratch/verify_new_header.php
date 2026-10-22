<?php
$file = 'c:/laragon/www/Classy-One-Mobile/lib/screens/parent_dashboard_screen.dart';
$lines = file($file);

echo "=== VERIFY NEW TOP HEADER ===\n";
for ($i = 2240; $i < 2330; $i++) {
    if (isset($lines[$i])) {
        echo ($i + 1) . ": " . $lines[$i];
    }
}

echo "\n=== VERIFY NEW CHILD SELECTOR ===\n";
for ($i = 2640; $i < 2740; $i++) {
    if (isset($lines[$i])) {
        if (stripos($lines[$i], '_buildChildSelectorBar') !== false) {
            for ($j = $i - 2; $j < $i + 70; $j++) {
                if (isset($lines[$j])) {
                    echo ($j + 1) . ": " . $lines[$j];
                }
            }
            break;
        }
    }
}
