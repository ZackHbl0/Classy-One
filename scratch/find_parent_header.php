<?php
$file = 'c:/laragon/www/Classy-One-Mobile/lib/screens/parent_dashboard_screen.dart';
$lines = file($file);

foreach ($lines as $num => $line) {
    if (stripos($line, 'Bienvenue') !== false || stripos($line, 'Mohamed') !== false || stripos($line, '_buildChildSelectorBar') !== false) {
        echo "Found at line " . ($num + 1) . ": " . trim($line) . "\n";
    }
}
