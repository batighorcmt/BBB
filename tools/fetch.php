<?php
$url = 'http://127.0.0.1:8000/login';
$body = @file_get_contents($url);
echo 'LEN:' . ($body === false ? 'FALSE' : strlen($body)) . "\n";
echo "----BODY----\n";
echo base64_encode($body);
echo "\n----END----\n";