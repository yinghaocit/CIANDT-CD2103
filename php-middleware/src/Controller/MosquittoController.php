<?php

namespace zigbee2mqtt\src\controller;

defined('OPEN_OFFICE_LIGHT_LIMT') || define('OPEN_OFFICE_LIGHT_LIMT', 9);
defined('CLOSE_MEETING_LIGHT_WAIT') || define('CLOSE_MEETING_LIGHT_WAIT', 10);

require_once("./src/Device/DeviceBase.php");

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
        //        $logContent .= "- Payload: " . json_encode(json_decode($item['payload'], TRUE), JSON_PRETTY_PRINT) . PHP_EOL;

        // 操作日志
        error_log($logContent, 3, "log/operation/" . date("YmdH") . ".log");
      }
    }

    //zigbee2mqtt/0x70ac08fffe65a18c/set
    //    $payload =  json_decode($payload, TRUE);
    //    $topic = explode('/', $topic);
    //    $count = count($topic);
    //
    //    if ($count == 2 || ($topic[1] == '0x70ac08fffe65a18c' && $topic[2] == 'set')) {
    //
    //      // 智能化 start
    //      $AirConditionerIRCodeMap = require_once('./config/AirConditionerIRCodeMap.php');
    //
    //      $push_data = [];
    //
    //      // 无线开关面板
    //      if ($topic[1] == '0x00158d607fe00e5a') {
    //        $a = $payload['action'];
    //        switch ($a) {
    //          case "single_left":
    //            // 会议室灯 关闭
    //            $push_data[] = [
    //              'topic' => $this->topic_zigbee . "/0x00124b00257c6e2c/set",
    //              'payload' => json_encode(['state' => 'off'])
    //            ];
    //            // 会议室灯带 关闭
    //            $push_data[] = [
    //              'topic' => $this->topic_zigbee . "/0x00124b00257c6e5f/set",
    //              'payload' => json_encode(['state' => 'off'])
    //            ];
    //            // 空调 关闭
    //            $push_data[] = [
    //              'topic' => $this->topic_zigbee . "/0x70ac08fffe65a18c/set",
    //              'payload' => json_encode(['ir_code_to_send' => 'DQMSAxIjAj4GIwIjAlgCQAdAAwQjAlgCIyABgAtAAQFYAuAHC8AP4AEHgCMBPgaAA0ABwAtAB4ABAlgCI6ABgBOAD0ALwAOAAYAX4AUBgBOAJwBYoAeADwEDEkABASMCQA9AAUAHQAOAAYArwAHgARcBIwJAC0ADQAHgAwdAC8ADQAGAC+AFR8ABAz4GIwLAAUALwAPgFQGAS8ArBz4GIwI+BiMC'])
    //            ];
    //            break;
    //          case "single_right":
    //            // // 会议室灯 开启
    //            // $push_data[] = [
    //            //   'topic' => $this->topic_zigbee . "/0x00124b00257c6e2c/set",
    //            //   'payload' => json_encode(['state' => 'on'])
    //            // ];
    //            // // 会议室灯带 开启
    //            // $push_data[] = [
    //            //   'topic' => $this->topic_zigbee . "/0x00124b00257c6e5f/set",
    //            //   'payload' => json_encode(['state' => 'on'])
    //            // ];
    //            // 空调 开启
    //            $push_data[] = [
    //              'topic' => $this->topic_zigbee . "/0x70ac08fffe65a18c/set",
    //              'payload' => json_encode(['ir_code_to_send' => 'CSISIhIfAlUGHwJAAUAHQAPAAQRVBlsCH6ABA1UGHwLAAUALQANAAUAH4BcD4BcBwD9AAUAL4A8BwBtAB+ADAwEiEkABAR8CQBNAAUAHQAPAAeATC8AbQAfgFwPgFwHAP0ABQAvgDwHAG0AH4AMDASISQAEBHwLAE0ABQAtAAeAHB8AB4A8X4FMBQHPAA0ABQAsLHwIfAh8CHwJVBh8C'])
    //            ];
    //            break;
    //            case "double_right":
    //              // 空调 关闭
    //              $push_data[] = [
    //                'topic' => $this->topic_zigbee . "/0x70ac08fffe65a18c/set",
    //                'payload' => json_encode(['ir_code_to_send' => 'DQMSAxIjAj4GIwIjAlgCQAdAAwQjAlgCIyABgAtAAQFYAuAHC8AP4AEHgCMBPgaAA0ABwAtAB4ABAlgCI6ABgBOAD0ALwAOAAYAX4AUBgBOAJwBYoAeADwEDEkABASMCQA9AAUAHQAOAAYArwAHgARcBIwJAC0ADQAHgAwdAC8ADQAGAC+AFR8ABAz4GIwLAAUALwAPgFQGAS8ArBz4GIwI+BiMC'])
    //              ];
    //              break;
    //        }
    //      }
    //
    //      // 红外检测
    //      if ($topic[1] == '0x00124b0025177796') {
    //        $meetingRoomMonitorOriginal = file_get_contents('./config/local.meetingRoomUnattendedMonitor.json');
    //        $meetingRoomMonitor = json_decode($meetingRoomMonitorOriginal, true);
    //        $a = $payload['occupancy'] ? "open" : "close";
    //        switch ($a) {
    //          case 'open':
    //            $meetingRoomMonitor['someoneHere'] = true;
    //            $meetingRoomMonitor['lastPresenceTime'] = time();
    //            //关闭 会议室自动关闭逻辑
    //            $meetingRoomMonitor['enable'] = false;
    //            break;
    //          case 'close':
    //            $meetingRoomMonitor['someoneHere'] = false;
    //            $meetingRoomMonitor['lastUnattendedTime'] = time();
    //            //开启 会议室自动关闭逻辑
    //            $meetingRoomMonitor['enable'] = true;
    //            break;
    //        }
    //        ksort($meetingRoomMonitor);
    //        $meetingRoomMonitor = json_encode($meetingRoomMonitor);
    //        //如果有变化 更新本地配置
    //        if($meetingRoomMonitor !== $meetingRoomMonitorOriginal){
    //          file_put_contents('./config/local.meetingRoomUnattendedMonitor.json', $meetingRoomMonitor);
    //        }
    //      }
    //
    //      // Pisces温湿度传感器
    //      if($topic[1] == '0x00124b0022ceaed4'){
    //        $piscesTemperaturers = json_decode(file_get_contents('./config/local.piscesTemperaturer.json'), true) ?? [];
    //        ksort($piscesTemperaturers);
    //        if(count($piscesTemperaturers) >= 10){
    //          // 只保留10条记录 卸载掉最老的那个记录
    //          unset($piscesTemperaturers[array_keys($piscesTemperaturers)[0]]);
    //        };
    //        $piscesTemperaturers[time()] = $payload['temperature'];
    //        ksort($piscesTemperaturers);
    //        file_put_contents('./config/local.piscesTemperaturer.json', json_encode($piscesTemperaturers));
    //      }
    //
    //      // 执行
    //      foreach ($push_data as $item) {
    //        $this->client->publish($item['topic'], $item['payload'], 1);
    //      }
    //
    //      // 智能化 end
    //
    //      $device_type_map = DeviceBase::DEVICE_TYPE_MAP;
    //
    //      if (!isset($device_type_map[$topic[1]])) return;
    //
    //      $device = $device_type_map[$topic[1]];
    //
    //      $reported = [];
    //      foreach ($device['attributes'] as $attribute) {
    //        $reported[$attribute] = $payload[$attribute] ?? "-";
    //      }
    //
    //      $topic_data = [
    //        'friendly_name' => $topic[1],
    //        'payload' => [
    //          'timestamp' => time(),
    //          'reported' => $reported,
    //        ]
    //      ];
    //
    //      $topic_data = json_encode($topic_data);
    //
    //      $maps = DeviceBase::MAPS;
    //      foreach ($maps as $r => $s) {
    //        $topic_data = str_replace($s, $r, $topic_data);
    //      }
    //      $topic_send = "php_middleware/" . $device['topic'] . "/shadow/update/" . $device['topic_action'];
    //      echo PHP_EOL;
    //      echo "topic:" . $topic_send . PHP_EOL;
    //      echo "payload:" . $topic_data . PHP_EOL;
    //      echo '-------------------------end-------------------------' . PHP_EOL;
    //
    //      $this->client->publish($topic_send, $topic_data, 1);
    //    }
  }

  public function javaClientData($data) {
    // Get topic.
    $topic = $data->topic;
    $payload = $data->payload;
    echo PHP_EOL;
    echo PHP_EOL;
    echo '-------------------------start-------------------------' . PHP_EOL;
    echo date("Y-m-d H:i:s") . PHP_EOL;
    echo "sub: java topic:" . $topic . PHP_EOL;
    echo "sub: java payload:" . $payload . PHP_EOL;
    // Chagnge String to Array.
    $topic = explode('/', $topic);
    $payload = json_decode($payload, TRUE);

    $topic_data = json_encode($payload['payload']['state']);

    $deviceClass = $this->getDeviceClass($topic[1]);

    if ($deviceClass) {
      require_once("./src/Device/{$deviceClass}.php");
    }
    $maps = $deviceClass::MAPS;
    // filter
    foreach ($maps as $s => $r) {
      $topic_data = str_replace($s, $r, $topic_data);
    }
    $topic_send = $this->topic_zigbee . "/" . $payload['friendly_name'] . '/set';

    echo "topic:" . $topic_send . PHP_EOL;
    echo "payload:" . $topic_data . PHP_EOL;
    echo '-------------------------end-------------------------' . PHP_EOL;

    $this->client->publish($topic_send, $topic_data, 1);
  }

  private function getDeviceClass($device_type) {
    $device_class = '';
    if (empty($device_type)) {
      return $device_class;
    }

    $device_class_array = explode('_', $device_type);
    foreach ($device_class_array as $class) {
      $device_class .= ucfirst($class);
    }
    return $device_class;
  }

}
