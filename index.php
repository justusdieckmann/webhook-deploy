<?php

require_once('./config.php');
global $CFG;

$json = file_get_contents('php://input');

if ('sha256=' . hash_hmac('sha256', $json, $CFG->secret) !== $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? null) {
    http_response_code(406);
    die();
}

if ($_SERVER['HTTP_X_GITHUB_EVENT'] !== 'push') {
    http_response_code(418);
    die();
}

$data = json_decode($json, true);

$CFG->configdir = realpath($CFG->configdir);

foreach(scandir($CFG->configdir) as $configfile) {
    $path = $CFG->configdir . '/' . $configfile;
    if (!is_file($path) || !is_readable($path))
        continue;

    $yaml = yaml_parse_file($path);
    $branch = $yaml['branch'] ?? 'main';
    if ($data['repository']['full_name'] === $yaml['repo'] &&
        $data['ref'] === 'refs/heads/' . $branch) {

        echo "* Pulling " . $yaml['path'] . "\n";

        $remote = $yaml['remote'] ?? 'origin';
        chdir($yaml['path']);
        echo shell_exec("git fetch $remote && git checkout $remote/$branch") . "\n";
        if (isset($yaml['cmd'])) {
            echo shell_exec($yaml['cmd']) . "\n";
        }
    }
}
