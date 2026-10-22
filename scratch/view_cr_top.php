<?php
$lines = file('C:/laragon/www/Classy-One-Mobile/lib/utils/content_router.dart');
echo implode("", array_slice($lines, 0, 45));
