<?php

namespace zigbee2mqtt\src\controller;

defined('OPEN_OFFICE_LIGHT_LIMT') || define('OPEN_OFFICE_LIGHT_LIMT', 9);
defined('CLOSE_MEETING_LIGHT_WAIT') || define('CLOSE_MEETING_LIGHT_WAIT', 10);

use \Mosquitto\Client;
use zigbee2mqtt\src\device\DeviceBase;

class MosquittoController {

  public $client;

  protected $config;

  protected $topic_zigbee;

  public function __construct($config) {
    date_default_timezone_set('PRC');
    $this->config = $config;
    $this->topic_zigbee = $config['topic'] ?? 'zigbee2mqtt';
    $this->connect();
  }

  private function connect() {
    $config = $this->config;
    $this->client = new Client();
    $this->client->setCredentials($config['user'], $config['password']);
    $this->client->connect($config['host'], $config['port'], $config['keep_live']);
  }

  public function onZigbeeMsg($data) {
    // Get topic.
    $topic = $data->topic;
    $payload = $data->payload;
    //    echo PHP_EOL;
    //    echo PHP_EOL;
    //    echo '-------------------------start-------------------------' . PHP_EOL;
    //    echo date("Y-m-d H:i:s") . PHP_EOL;
    //    echo "zigbee topic:" . $topic . PHP_EOL;
    //    echo "zigbee payload:" . $payload . PHP_EOL;

    if ($topic == "zigbee2mqtt/0x00158d607fe00e5a") {
      $payload = json_decode($payload, TRUE);
      $action = $payload['action'];
      $push_data = [];
      switch ($action) {
        case "single_left":
        case "double_left":
          // 空调 关闭
          $push_data[] = [
            'action' => $action,
            'topic' => $this->topic_zigbee . "/0x70ac08fffe65a18c/set",
            'payload' => json_encode(['ir_code_to_send' => 'DQMSAxIjAj4GIwIjAlgCQAdAAwQjAlgCIyABgAtAAQFYAuAHC8AP4AEHgCMBPgaAA0ABwAtAB4ABAlgCI6ABgBOAD0ALwAOAAYAX4AUBgBOAJwBYoAeADwEDEkABASMCQA9AAUAHQAOAAYArwAHgARcBIwJAC0ADQAHgAwdAC8ADQAGAC+AFR8ABAz4GIwLAAUALwAPgFQGAS8ArBz4GIwI+BiMC']),
          ];
          break;
        case "single_right":
        case "double_right":
          // 空调 开启
          $push_data[] = [
            'action' => $action,
            'topic' => $this->topic_zigbee . "/0x70ac08fffe65a18c/set",
            'payload' => json_encode(['ir_code_to_send' => 'CRoSGhIbAlcGGwJAAUAHQAPAAeATCwRXBlMCGyABAVcGgANAAcAL4AcHQAFAE+APAUAbQANAAYAHgFvgBwGAGwEbAkAH4AMDARoSQAEBGwJAE0ABQAdAA4ABAVMC4AELARsC4AcLgA+AI4ALARsCQAfgCwNAAUAX4A8BQBtAA0ABwAfgCwHAG0AH4AMDARoSQAEBGwLAE0ABQAtAAeALB8ATQAFAC0AD4AsBAlMCG+AEAeAHD+AvAUBvwAMLGwIbAlcGGwJXBhsC']),
          ];
      }

      // 获取当前时间
      $currentTime = date("Y-m-d H:i:s");
      foreach ($push_data as $item) {
        // 执行操作
        $this->client->publish($item['topic'], $item['payload'], 1);

        // 日志内容
        $logContent = $currentTime .
          " [Action]: " . $item['action'] .
          " [Topic]: " . $item['topic'] .
          " [Payload]: " . json_encode($item['payload']) . PHP_EOL;

        // 操作日志
        error_log($logContent, 3, "log/operation/" . date("YmdH") . ".log");
      }
    }
  }


}
