<?php
$file = 'C:/laragon/www/Classy-One-Mobile/lib/screens/planning_page.dart';
$content = file_get_contents($file);

$target = "const ScreenHeader(title: 'Emploi du temps', showBackButton: true)";
$replace = "const ScreenHeader(title: 'Emploi du temps', showBackButton: false)";

if (strpos($content, $target) !== false) {
    $content = str_replace($target, $replace, $content);
    file_put_contents($file, $content);
    echo "PLANNING_PAGE_HEADER_UPDATED";
} else {
    echo "TARGET_NOT_FOUND";
}
