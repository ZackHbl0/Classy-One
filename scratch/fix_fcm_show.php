<?php
$path = 'C:/laragon/www/Classy-One-Mobile/lib/services/fcm_service.dart';
$content = file_get_contents($path);

$target = <<<'DART'
    await _localNotifications.show(
      id,
      title,
      body,
      notificationDetails,
      payload: data != null ? jsonEncode(data) : null,
    );
DART;

$replacement = <<<'DART'
    await _localNotifications.show(
      id: id,
      title: title,
      body: body,
      notificationDetails: notificationDetails,
      payload: data != null ? jsonEncode(data) : null,
    );
DART;

if (strpos($content, $target) !== false) {
    $content = str_replace($target, $replacement, $content);
    file_put_contents($path, $content);
    echo "SUCCESS: Updated show method with named arguments.\n";
} else {
    echo "Target not found.\n";
}
