<?php
$context = stream_context_create([
    'http' => ['method' => 'HEAD', 'timeout' => 5]
]);
$fd = fopen('https://www.google.com', 'r', false, $context);
if ($fd) {
    $meta = stream_get_meta_data($fd);
    print_r($meta['wrapper_data']);
    fclose($fd);
} else {
    echo "Failed to connect\n";
}
