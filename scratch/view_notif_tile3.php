<?php
$lines = file('C:/laragon/www/Classy-One-Mobile/lib/screens/notifications_page.dart');
echo implode("", array_slice($lines, 480, 70));
