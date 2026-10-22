<?php
$path = 'C:/laragon/www/Classy-One-Mobile/lib/providers/notification_provider.dart';
$content = file_get_contents($path);

if (strpos($content, 'fetchNotificationsSilently') === false) {
    $target = "Future<void> fetchNotifications(int idStudent) async {";
    $addition = "  /// Silently refreshes notifications from server without toggling full-screen isLoading\n  Future<void> fetchNotificationsSilently(int idStudent) async {\n    if (idStudent == 0) return;\n    try {\n      final result = await _authService.getNotifications(idStudent);\n      if (result['success'] == true) {\n        _notifications = result['data'] ?? [];\n        _errorMessage = '';\n        notifyListeners();\n      }\n    } catch (e) {\n      debugPrint('Error silently refreshing notifications: \$e');\n    }\n  }\n\n  ";
    
    $content = str_replace($target, $addition . $target, $content);
    file_put_contents($path, $content);
    echo "SUCCESS: fetchNotificationsSilently added.\n";
} else {
    echo "INFO: Already present.\n";
}
