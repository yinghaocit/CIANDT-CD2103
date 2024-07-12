<?php

define('BASE_PATH', __DIR__);

use Swoole\Coroutine;
use Swoole\Runtime;

Runtime::enableCoroutine();

$client = [];

try {
  $client = new Mosquitto\Client();
  $client->setCredentials('admin', 'o8Uslg');
  $client->connect('mosquitto', 1883, 60);
  $g = [
    'learned_ir_code' => 'EYkSiRLuAYkGRgLuAUYCiQbuAUADAUYCgAOAC0AB4AMLAJlgF4AbQAcDRgKJBuADB0ALwAOAGwFGAoADA7ACmQHgCwvgAycDiQbuAYAjQAEDmQGwAsAPAe4BwBtABwWJBkYCiQZAByADCxImCdoAngDaAJkB7iABAYkSIAMgFwNGAu4BQAdAA0ALQANACwKwApkgAwHuAUALQBMDRgKZAUALA4kGmQHAFwGJBkADAJmgBwFGAoADwA8D7gFGAuAFAwOwApkBQBMCsAJHIAME7gGJBpkgA0ARCu4BiQbuAbACmQFGYAMH7gHFA9oAsALAJwXuAbAC7gFAB',
  ];

  $m = json_encode($g);

  $client->publish($t, $m, 1);
}
catch (\Mosquitto\Exception $exception) {
  var_dump('err', $exception->getMessage());
}
