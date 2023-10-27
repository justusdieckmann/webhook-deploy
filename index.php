<?php

if (isset($_SERVER['HTTP_X_GITHUB_EVENT']) && $_SERVER['HTTP_X_GITHUB_EVENT'] !== 'push') {
    http_response_code(418);
    die();
}

echo "Hi GitHub :)\n";

$json = base64_encode(file_get_contents('php://input'));
$signature = base64_encode($_SERVER['HTTP_X_HUB_SIGNATURE_256']);

$out = [];
$code = null;
exec("/var/deploy/elevator $json $signature 2>&1", $out, $code);

$code = $code ?: 200;

http_response_code($code);
echo join(PHP_EOL, $out);
