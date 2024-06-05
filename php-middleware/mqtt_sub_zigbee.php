<?php
// require_once __DIR__ . "/vendor/autoload.php";
require_once("./src/Controller/MosquittoController.php");

use zigbee2mqtt\src\controller\MosquittoController;


define('BASE_PATH', __DIR__);

try {

  $mosquitto_config = require_once BASE_PATH . "/config/mosquitto.php";

  $mqc = new MosquittoController($mosquitto_config);
  $t = 'zigbee2mqtt/#';

  $client = $mqc->client;

  $client->onConnect(function (int $rc, string $message) {
    var_dump($rc, $message);
  });

  $client->onDisconnect(function (int $rc) {
    var_dump($rc);
  });


  $client->subscribe($t, 1);
  $client->onMessage(function ($data) use ($mqc) {
    /** @var Mosquitto\Message $data */
    $mqc->onZigbeeMsg($data);
  });

  $client->loopForever();
} catch (\Mosquitto\Exception $exception) {
  var_dump('error', $exception->getMessage());
}
