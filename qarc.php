!/usr/bin/php
<?php
// Where empty albums go to die
ini_set('display_errors', 'On');
declare(ticks = 1);
pcntl_signal(SIGTERM, "signal");
pcntl_signal(SIGINT, "signal");
pcntl_signal(SIGHUP, "signal");

require_once('./conf.qarc.php');
require_once('../conf/stdlib.php'); //assuming this is running out of the grasp repo...

echo "Running as: " . exec('whoami') . "\n";

echo "Starting worker thread\n";

$gm = new GearmanWorker();
//if (defined('LOCAL') && LOCAL) {
    $gm->addServer();
//}
$pandaCave = QARC_PANDACAVE_ENDPOINT; 
$FENs = array();
$fp = fopen($pandaCave, 'rb', false);
$data = json_decode(fread($fp, 102400), true);
if (count($data) == 0) {
    echo "No servers found in FEN pool from Panda Cave.";
    exit;
}
foreach ($data as $server) {
    $gm->addServer($server['InternalIP']);
}

$args = null;

$stop = false;
global $stop;

$gm->addFunction("qarcGithubHook", "qarcGithubHook", $args);

echo "Added the function to gearman\n";

while (!$stop && ($gm->work() || $gm->returnCode() == GEARMAN_TIMEOUT)) {
    switch ($gm->returnCode()) {
        case GEARMAN_SUCCESS:
            echo "Gearman Success\n";
            break;
        case GEARMAN_TIMEOUT:
            echo "Gearman Timeout\n";
            break;
        default:
            echo "ERROR RET: " . $gmc->returnCode() . "\n";
            exit;
    }
}

echo "Quitting\n";

function qarcGithubHook($job, $args)
{
    $jobInfo = json_decode($job->workload(), true);
    $after = (string)$jobInfo['after'];
    $ref = (string)$jobInfo['ref'];

    echo "Job: " . $after . " | " . $ref . "\n";
    
    // REF to whatever it is you care about
    if ($ref === "refs/heads/backbone") {
        echo "Trying to reset to " . $after + "\n";
        echo exec('cd /opt/www/sites/rc && git fetch upstream && git reset --hard ' . $after);
    }
}

function signal($signo) {
    print "signal called\n";
    global $stop;
    switch($signo) {
        case SIGTERM:
            print "SIGTERM\n";
            $stop = true;
            break;
        case SIGINT:
            print "SIGINT\n";
            $stop = true;
            break;
        case SIGHUP:
            print "SIGHUP";
            $stop = true;
            break;
    }
}

?>
