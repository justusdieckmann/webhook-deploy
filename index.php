<?php

if ($_SERVER['HTTP_X_GITHUB_EVENT'] !== 'push') {
    http_response_code(418);
    die();
}

$json = escapeshellarg(base64_encode(file_get_contents('php://input')));
$signature = escapeshellarg(base64_encode($_SERVER['HTTP_X_HUB_SIGNATURE_256']));

[$code, $value] = explode(' - ', shell_exec("/var/deploy/elevator $json $signature") . "\n", 2);
http_response_code($code);
echo $value;