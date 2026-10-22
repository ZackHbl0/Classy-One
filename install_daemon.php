<?php
$startup = getenv('APPDATA') . '/Microsoft/Windows/Start Menu/Programs/Startup';
$src = 'C:/laragon/www/Classy-One/start_adb_daemon.vbs';
$dest = $startup . '/start_adb_daemon.vbs';

if (copy($src, $dest)) {
    echo "COPIED_TO_STARTUP: " . $dest;
} else {
    echo "COPY_FAILED";
}
