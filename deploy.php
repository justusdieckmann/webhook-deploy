#! /usr/bin/php

<?php

if (php_sapi_name() !== 'cli') {
    echo "[only cli access allowed]";
    exit(403);
}

require_once('./config.php');
global $CFG;

$json = base64_decode($argv[1]);
$signature = base64_decode($argv[2]);

if ('sha256=' . hash_hmac('sha256', $json, $CFG->secret) !== $signature ?? null) {
    echo "[invalid signature]";
    exit(406);
}

echo "[OK]";

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

        echo "* Pulling {$yaml['path']}\n";

        $remote = $yaml['remote'] ?? 'origin';
        $user = $yaml['user'] ?? 'admin';
        chdir($yaml['path']);
        echo shell_exec("sudo -u $user git fetch $remote && sudo -u $user git checkout $remote/$branch") . "\n";
        if (isset($yaml['cmd'])) {
            $out = [];
            $cmd = escapeshellarg($yaml['cmd']);
            exec("sudo -u $user bash -c $cmd", $out) . "\n";
            echo implode(PHP_EOL, $out);
        }
    }
}
