#! /usr/bin/php

<?php

if (php_sapi_name() !== 'cli') {
    die('403 - only cli access allowed');
}

require_once('./config.php');
global $CFG;

$json = base64_decode($argv[2]);
$signature = base64_decode($argv[3]);

if ('sha256=' . hash_hmac('sha256', $json, $CFG->secret) !== $signature ?? null) {
    die('406 - invalid signature');
}

echo "200 - OK";

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
        $user = $yaml['user'] ?? 'admin';
        chdir($yaml['path']);
        echo shell_exec("sudo -u $user git fetch $remote && git checkout $remote/$branch") . "\n";
        if (isset($yaml['cmd'])) {
            echo shell_exec("sudo -u $user {$yaml['cmd']}") . "\n";
        }
    }
}
