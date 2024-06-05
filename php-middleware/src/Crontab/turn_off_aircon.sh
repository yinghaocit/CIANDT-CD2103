#!/usr/bin/env bash
set -e
docker exec -it mosquitto mosquitto_pub -t "zigbee2mqtt/0x70ac08fffe65a18c/set" -m '{"ir_code_to_send":"DQMSAxIjAj4GIwIjAlgCQAdAAwQjAlgCIyABgAtAAQFYAuAHC8AP4AEHgCMBPgaAA0ABwAtAB4ABAlgCI6ABgBOAD0ALwAOAAYAX4AUBgBOAJwBYoAeADwEDEkABASMCQA9AAUAHQAOAAYArwAHgARcBIwJAC0ADQAHgAwdAC8ADQAGAC+AFR8ABAz4GIwLAAUALwAPgFQGAS8ArBz4GIwI+BiMC"}'

datetime=$(date +%Y-%m-%d-%H:%M:%S)
curl 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=92e83753-ba9f-41e8-b146-75c37b17f8ef' \
  -H 'Content-Type: application/json' \
  -d '
   {
        "msgtype": "text",
        "text": {
        "content": "'$datetime' 定时关闭空调",
        }
   }'
