<?php

//curl 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=92e83753-ba9f-41e8-b146-75c37b17f8ef' \
//  -H 'Content-Type: application/json' \
//  -d '
//   {
//        "msgtype": "text",
//        "text": {
//        "content": "",
//        "mentioned_list":["@all"],
//        }
//   }'

const WECHAT_BOT_URL = 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send';
// AC controller key
const WECHAT_BOT_KEY_ACC = '92e83753-ba9f-41e8-b146-75c37b17f8ef';