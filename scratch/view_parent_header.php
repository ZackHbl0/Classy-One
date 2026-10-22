<?php
$file = 'c:/laragon/www/Classy-One-Mobile/lib/screens/parent_dashboard_screen.dart';
$lines = file($file);

echo "=== AROUND LINE 2270 (Top Bar) ===\n";
for ($i = 2250; $i < 2330; $i++) {
    if (isset($lines[$i])) {
        echo ($i + 1) . ": " . $lines[$i];
    }
}

echo "\n=== AROUND LINE 385 (Body / Usage) ===\n";
for ($i = 375; $i < 420; $i++) {
    if (isset($lines[$i])) {
        echo ($i + 1) . ": " . $lines[$i];
    }
}
