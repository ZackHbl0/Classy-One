<?php
$file = 'C:/laragon/www/Classy-One-Mobile/lib/utils/content_router.dart';
$content = file_get_contents($file);

$newFunc = '  static String _getFileExtension(String url) {
    try {
      final uri = Uri.parse(url);

      // 1. Check query parameter \'path\' if present (e.g. ?path=courses/video.mp4)
      if (uri.queryParameters.containsKey(\'path\')) {
        final qPath = uri.queryParameters[\'path\']!.toLowerCase();
        final dot = qPath.lastIndexOf(\'.\');
        if (dot != -1) {
          return qPath.substring(dot + 1).split(\'?\').first;
        }
      }

      // 2. Check uri.path
      final path = uri.path.toLowerCase();
      final lastDotIndex = path.lastIndexOf(\'.\');
      if (lastDotIndex != -1) {
        return path.substring(lastDotIndex + 1).split(\'?\').first;
      }

      // 3. Fallback: check for extensions anywhere in the URL string
      for (final ext in [\'mp4\', \'mkv\', \'webm\', \'mov\', \'avi\', \'pdf\', \'png\', \'jpg\', \'jpeg\', \'webp\']) {
        if (url.toLowerCase().contains(\'.$ext\')) {
          return ext;
        }
      }
    } catch (_) {}
    return \'\';
  }';

$content = preg_replace('/static String _getFileExtension\(String url\)\s*\{.*?\n  \}/s', trim($newFunc), $content);
file_put_contents($file, $content);
echo "REPLACED_REGEX";
