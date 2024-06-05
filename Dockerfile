FROM phpswoole/swoole:4.7.1-php7.4

LABEL maintainer="EchoJ"

ARG timezone

ENV TIMEZONE=${timezone:-"Asia/Shanghai"}

RUN ln -sf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime \
  && echo "${TIMEZONE}" > /etc/timezone \
  && sed -i s@/archive.ubuntu.com/@/mirrors.aliyun.com/@g /etc/apt/sources.list \
  && apt-get clean \
  && apt-get update -y \
  && apt-get upgrade -y \
  && apt-get cron -y \
  && apt-get install libmosquitto-dev -y \
  && pecl install Mosquitto-alpha \
  && docker-php-ext-enable mosquitto

WORKDIR /opt/www

COPY . /opt/www
