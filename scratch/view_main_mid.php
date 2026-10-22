<?php
$lines = file('C:/laragon/www/Classy-One-Mobile/lib/main.dart');
echo implode("", array_slice($lines, 65, 55));
