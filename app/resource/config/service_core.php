<?php
return [
    ['url', function () {
        $url = new \Phalcon\Mvc\Url();
        $url->setBaseUri('/');
        return $url;
    }],

    ['redis', function () {
        $config = $this->get('config');

        $redis = new \Redis();
        $redis->connect($config->redis->host, $config->redis->port);

        return $redis;
    }, true],

    ['cache', function () {
        $config = $this->get('config');
        $redis  = $this->get('redis');

        $frontend = new \Phalcon\Cache\Frontend\Data(array(
            'lifetime' => $config->cache->lifetime // сутки
        ));

        $cache = new \Phalcon\Cache\Backend\Redis($frontend, array(
            'redis'  => $redis,
            'prefix' => $config->cache->prefix
        ));

        return $cache;
    }, true],
];