<?php
$content = file_get_contents('C:/laragon/www/Classy-One-Mobile/lib/services/auth_service.dart');
preg_match('/Future<Map<String, dynamic>> login\([^\{]+\{.*?return \{.*?message\': \'Connection error: \$e\'\};\s*\}/s', $content, $m);
echo $m[0] ?? 'NOT FOUND';
