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
          $ac = "检测到空调触发了[关闭]操作。";
          break;
        case "single_right":
        case "double_right":
          // 空调 开启
          $push_data[] = [
            'action' => $action,
            'topic' => $this->topic_zigbee . "/0x70ac08fffe65a18c/set",
            'payload' => json_encode(['ir_code_to_send' => 'CRoSGhIbAlcGGwJAAUAHQAPAAeATCwRXBlMCGyABAVcGgANAAcAL4AcHQAFAE+APAUAbQANAAYAHgFvgBwGAGwEbAkAH4AMDARoSQAEBGwJAE0ABQAdAA4ABAVMC4AELARsC4AcLgA+AI4ALARsCQAfgCwNAAUAX4A8BQBtAA0ABwAfgCwHAG0AH4AMDARoSQAEBGwLAE0ABQAtAAeALB8ATQAFAC0AD4AsBAlMCG+AEAeAHD+AvAUBvwAMLGwIbAlcGGwJXBhsC']),
          ];
          $ac = "检测到空调触发了[开启]操作";
          break;
      }

      // 获取当前时间
      $currentTime = date("Y-m-d H:i:s");
      foreach ($push_data as $item) {
        // 执行操作
        //        $this->client->publish($item['topic'], $item['payload'], 1);

        // 日志内容
        $logContent = $currentTime .
          " [Action]: " . $item['action'] .
          " [Topic]: " . $item['topic'] .
          " [Payload]: " . json_encode($item['payload']) . PHP_EOL;

        if (!empty($ac)) {
          if (file_exists(BASE_PATH . '/config/wechatbot.php')) {
            $wechatbot_config = require BASE_PATH . "/config/wechatbot.php";
          }
          if (!empty($wechatbot_config)) {
            $wechatData = [
              'msgtype' => 'text',
              'text' => [
                'content' => $ac,
                'mentioned_list' => ['@all'],
              ],
            ];
            // 下班前（18点），不提醒所有人
            if (date("H") < 18) {
              //              unset($wechatData['text']['mentioned_list']);
            }

            $url = $wechatbot_config['wechat_bot_endpoint'] . "?key=" . $wechatbot_config['wechat_bot_acc_key'];
            $this->curlPostRequest($url, $wechatData);
          }
        }
        $operationlog_path = BASE_PATH . '/log/operation/';
        // 如果日志目录不存在，创建
        if (!is_dir($operationlog_path)) {
          mkdir($operationlog_path);
        }
        // 操作日志
        error_log($logContent, 3, $operationlog_path . date("YmdH") . ".log");
      }
    }
  }

  public function curlPostRequest($url, $data) {
    $payload = json_encode($data);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Content-Type: application/json',
      'Content-Length: ' . strlen($payload),
    ]);

    $result = curl_exec($ch);

    if ($result === FALSE) {
      // 操作日志
      $WeChat = 'Curl error: ' . curl_error($ch);
    }
    else {
      $WeChat = $result;
    }
    $wechatbotlog_path = BASE_PATH . '/log/wechatbot/';
    if (!is_dir($wechatbotlog_path)) {
      mkdir($wechatbotlog_path);
    }
    error_log($WeChat . PHP_EOL, 3, $wechatbotlog_path . date("YmdH") . ".log");
    curl_close($ch);
  }

}
