<?php
$file = 'c:/laragon/www/Classy-One-Mobile/lib/screens/parent_dashboard_screen.dart';
$lines = file($file);

for ($i = 2375; $i < 2415; $i++) {
    if (isset($lines[$i])) {
        echo ($i + 1) . ": " . $lines[$i];
    }
}
