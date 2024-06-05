<?php
define('BASE_PATH', __DIR__);

use Swoole\Coroutine;
use Swoole\Runtime;

Runtime::enableCoroutine();

$client = [];

try {
  $client = new Mosquitto\Client();
  $client->setCredentials('admin', '123456');
  $client->setWill('dead', 'I am offline ', 1, false);
  $client->connect('zigbee2mqtt_mqtt_1', 1883, 60);
  // $client->connect('192.168.20.68', 1883, 60);
  $g = [
    'friendly_name' => '0x70ac08fffe65a18c',
    'payload' => [
      'version' => 7,
      'timestamp' => time(),
      'state' => [
        'learned_ir_code' => 'CTkSORL+AcMG/gFAAUAHQAMH/gG6AYcCugFACwOHAv4BQAGACwG6AUADQAtAA4ALC3EBwwa6AYcC/gHDBuADAwC6IA+AGwj9AroBwwb+AYfgAAdACwBxIA8DcQHDBoAbA/0C9QBACwKHAv6gA0ALAHEgC4AHCcMGcQGHAv4BwwbgAwMNugHfFO8H9QC6AboBkAPgAcsMcQHvB2UAwwb+AYcCcSADBboBwwbRAEAHQAMFwwa6AYcCwBcRugHDBnEBOws3AcMGugGHAroBQAeAAwBxIAcL/gHvB/UAwwZxAYcCgCMDOwu6AUATBYcCugHDBoADA4cCcQFACwL9AjcgAwPRAMMGQA8LNwHDBroBhwK6Ae8HQBME/gHDBrogAws3Ad8UMwX1AP4BqhpAD4ADA4cC/gFACw/9AvUA7wf1AIcCNwE5EroBQBMPhwK6ATsLNwHDBv4B/QI3AUAPQAMBORLAZwlxATsLugH9AnEBQAsLwwb1AJ4sugE7C3EB',
      ],
      'metadata' => [
        'switch' => [
          'timestamp' => time(),
        ],
      ]
    ],
  ];

  $m = json_encode($g);

  // $t = 'java_middleware/router_things/shadow/update/delta';
  $client->publish($t, $m, 1);

  $t = 'zigbee2mqtt/0x00158de67719693b/set';
  $client->publish($t, $m, 1);

} catch (\Mosquitto\Exception $exception) {
  var_dump('err', $exception->getMessage());
}
