<?php
$lines = file('C:/laragon/www/Classy-One-Mobile/lib/screens/planning_page.dart');
echo implode("", array_slice($lines, 175, 20));
