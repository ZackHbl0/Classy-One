<?php
$lines = file('C:/laragon/www/Classy-One-Mobile/lib/screens/main_screen.dart');
echo implode("", array_slice($lines, 0, 70));
