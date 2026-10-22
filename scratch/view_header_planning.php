<?php
echo "=== SCREEN_HEADER.DART ===\n";
echo file_get_contents('C:/laragon/www/Classy-One-Mobile/lib/widgets/screen_header.dart');
echo "\n=== PLANNING_PAGE.DART TOP ===\n";
$lines = file('C:/laragon/www/Classy-One-Mobile/lib/screens/planning_page.dart');
echo implode("", array_slice($lines, 0, 100));
