# Установка и настройка

## Установка скриптом

> Согласно доку, лучший способ установки это нативно через скрипт

1. Установочный скрипт `bash <(curl -Ss https://my-netdata.io/kickstart.sh)`
2. Скорее всего страдаем и ставим кучу завсимостей, решаем кнофликты
3. Редактируем конфиг /etc/netdata/netdata.conf

```shell
ls -la /usr/share/netdata/web/index.html
-rw-r--r--. 1 netdata netdata 89377 May  5 06:30 /usr/share/netdata/web/index.html
```

Тут меняем группу на ту, что в `ls -la`

```shell
[web]
    web files owner = netdata
    web files group = netdata
```

4. Перезагружаем сервис `sudo systemctl restart netdata`

5. Топаем в панельку https://app.netdata.cloud/spaces/ добавляем

6. PROFIT

## История хранения метрик

### В оперативной памяти

1. `nano /etc/netdata/netdata.conf`
2. В секции `[global]` (в ней прописываются общие настройки) найдём параметр `history`. Его значение — это срок (в
   секундах), в течение которого хранятся собранные метрики. От этого срока напрямую зависит и потребление памяти:

Для хранения данных в течение 3600 секунд (1 часа) требуется 15 MБ оперативной памяти;

- в течение 7200 секунд (2 часов) — 30 МБ оперативной памяти;
- в течение 14400 секунд (4 часов) — 60 МБ оперативной памяти;
- в течение 28800 секунд (8 часов) — 120 МБ оперативной памяти;
- в течение 43200 секунд (12 часов) — 180 МБ оперативной памяти;
- в течение 86400 секунд (т.е. суток) — 360 МБ оперативной памяти.

3. `sudo systemctl restart netdata`

### В базе

У netdata встроенный движок хранения на диске, чтоб включить:

1. `nano /etc/netdata/netdata.conf`
2. В секции `[global]`

```shell
[global]
    memory mode = dbengine
    page cache size = 32                  # размер кеша
    dbengine multihost disk space = 256   # размер занимаемого на диске места
```

## Подключение MySQL [MAN](https://learn.netdata.cloud/docs/agent/collectors/go.d.plugin/modules/mysql)

Открываем консоль MySQL под root и выполняем:

```mysql
CREATE USER 'netdata'@'localhost';
GRANT USAGE, REPLICATION CLIENT, PROCESS ON *.* TO 'netdata'@'localhost';
FLUSH PRIVILEGES;
```

Затем `sudo systemctl restart netdata` и PROFIT

## Подключение PHP-FGM [man](https://learn.netdata.cloud/docs/agent/collectors/go.d.plugin/modules/phpfpm)

В документации только настройка под Apache2 Тут будет по nginx

1. `nano /etc/php/7.4/fpm/pool.d/www.conf` (где лежит файл, зависит от вашего образа) снять комментирование
   с `pm.status_path = /status`
2. `nano /etc/nginx/vhost.d/php-fpm.conf`
   Добавляем

```shell
server {

   listen       80;
   #listen       [::]:80 default_server;
   server_name  127.0.0.1;

    location  /status {
        access_log off;
        allow 127.0.0.1;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_index index.php;
        deny all;
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
   }
}
```   

3. `systemctl restart php7.4-fpm`
4. `systemctl restart nginx`
5. `systemctl restart netdata`

Тест, что всё работает `curl http://127.0.0.1/status`, должны видеть статистику.

6. PROFIT

## Проверка доступности сайта

### Простая [httpcheck](https://learn.netdata.cloud/docs/agent/collectors/python.d.plugin/httpcheck) - умеет только проверять состояние одного сервиса

1. Поправим конфиг.

```shell
cd /etc/netdata   # Replace this path with your Netdata config directory, if different
sudo ./edit-config python.d/httpcheck.conf
```

Добавляем в конец файла

```shell
server:
  url: 'https://demo1.esh-derevenskoe.ru/admin/'  # required
  status_accepted:              # optional
    - 200
  timeout: 1                    # optional, supports decimals (e.g. 0.2)
  update_every: 3               # optional
  regex: 'REGULAR_EXPRESSION'   # optional, see https://docs.python.org/3/howto/regex.html
  redirect: yes                 # optional
```

2. `systemctl restart netdata`

### Более продвинутая [HTTP endpoint](https://learn.netdata.cloud/docs/agent/collectors/python.d.plugin/httpcheck)

[Что умеет](https://github.com/netdata/go.d.plugin/blob/master/config/go.d/httpcheck.conf)

1. Поправим конфиг.

```shell
cd /etc/netdata   # Replace this path with your Netdata config directory, if different
sudo ./edit-config go.d/httpcheck.conf
```

2. В секции `[GLOBAL]`

```shell
update_every        : 5 # Частота сбора данных в секундах. По умолчанию: 1.
autodetection_retry : 3 # Интервал перепроверки. Ноль означает не планировать повторную проверку. По умолчанию: 0.
priority            : 70000 # Приоритет является относительным приоритетом графиков, которые отображаются на веб-странице,

```

Так же добавляем ресурсы, которые мы будем проверять:

```shell
jobs:
 - name: "ED"
   url: https://esh-derevenskoe.ru
   status_accepted: [200]
   timeout: 2

 - name: "BS"
   url: https://bs.esh-derevenskoe.ru
   username: demo
   password: demo-password
   status_accepted: [200]
   timeout: 2

 - name: "demo1"
   url: https://demo1.esh-derevenskoe.ru/
   status_accepted: [200]
   timeout: 2

```

3. `systemctl restart netdata`

## Логи [Web server log](https://learn.netdata.cloud/docs/agent/collectors/python.d.plugin/web_log)

[Возможности](https://github.com/netdata/go.d.plugin/tree/master/modules/weblog)

### [Можно собирать обращение к url](https://learn.netdata.cloud/docs/agent/collectors/python.d.plugin/web_log#url-patterns)

```shell
cd /etc/netdata   # Replace this path with your Netdata config directory, if different
sudo ./edit-config python.d/web_log.conf
```

```shell
nginx_netdata:
  name: ED
  path: '/var/log/nginx/access.log'
  categories:
    image    : '^/image/'
    admin    : '^/admin/'
    1c       : '^/1c/'
    printer  : '^/index.php?route=api/printer'
    api      : '^/(api)'
    checkout : '^/index.php?route=checkout/'
```

`systemctl restart netdata`

## Nginx

```shell
nano /etc/nginx/vhost.d/nginx.conf
```

```shell

server {

   listen       80;
   #listen       [::]:80 default_server;
   server_name  127.0.0.1;

   location  /stub_status {
       stub_status;
   }
}
```

```shell
cd /etc/netdata   # Replace this path with your Netdata config directory, if different
sudo ./edit-config python.d/nginx.conf
```

```shell
update_every : 10
priority     : 90100

local:
  url     : 'http://127.0.0.1/stub_status'
```

`systemctl restart nginx`

`systemctl restart netdata`

## Fail2ban

```shell
cd /etc/netdata   # Replace this path with your Netdata config directory, if different
sudo ./edit-config python.d/fail2ban.conf
```

```shell
local:
 log_path: '/var/log/fail2ban.log'
 conf_path: '/etc/fail2ban/jail.local'
 exclude: 'dropbear apache'
```

`systemctl restart netdata`

## Подключение к [netdata.cloud](https://app.netdata.cloud)

Там в ЛК есть прям готовая ссылка, это пример:

```markdown
docker exec -it netdata netdata-claim.sh -token=YOU -rooms=YOU -url=https://app.netdata.cloud
```