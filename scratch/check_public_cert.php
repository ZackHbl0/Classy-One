<?php
$url = 'https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-fbsvc%40classy-one.iam.gserviceaccount.com';
$content = file_get_contents($url);
echo $content . "\n";
