<?php
$lines = file('C:/laragon/www/Classy-One-Mobile/lib/screens/in_app_video_player_screen.dart');
echo implode("", array_slice($lines, 90, 40));
