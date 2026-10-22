<?php
$lines = file('C:/laragon/www/Classy-One-Mobile/lib/screens/courses_screen.dart');
echo implode("", array_slice($lines, 200, 60));
