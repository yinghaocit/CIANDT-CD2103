<?php

$config = [
  'user' => 'admin',
  'password' => 'o8Uslg',
  'host' => 'mosquitto',
  'port' => 1883,
  'keep_live' => 60,
];
// 如何存在local配置，则使用local配置
if (file_exists(__DIR__ . '/local.mosquitto.php')) {
  $localConfig = include __DIR__ . '/local.mosquitto.php';
  $config = array_merge($config, $localConfig);
}
return $config;
