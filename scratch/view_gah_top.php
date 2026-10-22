<?php
$lines = file('C:/laragon/www/Classy-One-Mobile/lib/widgets/global_app_header.dart');
echo implode("", array_slice($lines, 0, 140));
