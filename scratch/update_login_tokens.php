<?php

$loginPath = 'C:/laragon/www/Classy-One-Mobile/lib/screens/login_page.dart';
$parentLoginPath = 'C:/laragon/www/Classy-One-Mobile/lib/screens/parent_login_screen.dart';

// 1. Update login_page.dart
$loginCode = file_get_contents($loginPath);
if (strpos($loginCode, 'FcmService.updateToken()') === false) {
    // Add import
    $loginCode = "import '../services/fcm_service.dart';\n" . $loginCode;
    
    // Add call after remember_me
    $target = "await prefs.setBool('remember_me', false);\n          }";
    $replacement = "await prefs.setBool('remember_me', false);\n          }\n\n          // Sync FCM push token for this student\n          FcmService.updateToken();";
    $loginCode = str_replace($target, $replacement, $loginCode);
    
    file_put_contents($loginPath, $loginCode);
    echo "login_page.dart updated.\n";
} else {
    echo "login_page.dart already has FcmService call.\n";
}

// 2. Update parent_login_screen.dart
$parentCode = file_get_contents($parentLoginPath);
if (strpos($parentCode, 'FcmService.updateToken()') === false) {
    // Add import
    $parentCode = "import '../services/fcm_service.dart';\n" . $parentCode;
    
    // Add call after parent prefs
    $target = "await prefs.setString('user_role', 'parent');";
    $replacement = "await prefs.setString('user_role', 'parent');\n          // Sync FCM push token for parent\n          FcmService.updateToken();";
    $parentCode = str_replace($target, $replacement, $parentCode);
    
    file_put_contents($parentLoginPath, $parentCode);
    echo "parent_login_screen.dart updated.\n";
} else {
    echo "parent_login_screen.dart already has FcmService call.\n";
}
