#!/usr/bin/env bash
set -e
a=$(docker ps -qf name=citcdoffice-2102_mqtt_1)
docker exec -it citcdoffice-2102_mqtt_1 mosquitto_pub -t "zigbee2mqtt/0x70ac08fffe65a18c/set" -m '{"ir_code_to_send":"DQMSAxIjAj4GIwIjAlgCQAdAAwQjAlgCIyABgAtAAQFYAuAHC8AP4AEHgCMBPgaAA0ABwAtAB4ABAlgCI6ABgBOAD0ALwAOAAYAX4AUBgBOAJwBYoAeADwEDEkABASMCQA9AAUAHQAOAAYArwAHgARcBIwJAC0ADQAHgAwdAC8ADQAGAC+AFR8ABAz4GIwLAAUALwAPgFQGAS8ArBz4GIwI+BiMC"}'
echo "$a"