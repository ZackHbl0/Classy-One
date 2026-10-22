<?php
$bat = '@echo off
echo [*] Configuration automatique du port 8000 (ADB Reverse)...
adb reverse tcp:8000 tcp:8000
echo [*] Lancement de l\'application Flutter...
flutter run
';

file_put_contents('C:/laragon/www/Classy-One-Mobile/run.bat', $bat);
echo "RUN_BAT_CREATED";
