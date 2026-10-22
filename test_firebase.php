<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $messaging = app('firebase.messaging');
    $message = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', 'fake-token')
        ->withNotification(\Kreait\Firebase\Messaging\Notification::create('test', 'test'));
    $messaging->send($message);
} catch (\Throwable $e) {
    echo "ERROR CLASS: " . get_class($e) . "\n";
    echo "ERROR MSG: " . $e->getMessage() . "\n";
    $curr = $e;
    while ($curr = $curr->getPrevious()) {
        echo "CAUSED BY: " . get_class($curr) . " => " . $curr->getMessage() . "\n";
    }
}
