<?php

require('../stdlib.php');

$githubIPs = array('207.97.227.253' => true,
                   '50.57.128.197' => true,
                   '108.171.174.178' => true,
                   );

if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
    $remoteIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $remoteIP = $_SERVER['REMOTE_ADDR'];
}

if (isset($githubIPs[$remoteIP]) && isset($_POST['payload'])) {
    $gm = new GearmanClient();
    $gm->addServer();
    $gm->doBackground("qarcGithubHook", $_POST['payload']);
}

?>