<?php
/**
 * Конфиг, что размещаем в dependency injector
 */

return [
    'daemon'         => ROOT_PATH . '/daemon', // для .pid-файлов демонов
    'temp'           => ROOT_PATH . '/temp',
    'data'           => ROOT_PATH . '/data',
    'log'            => ROOT_PATH . '/log/app',
    'log_level'      => 9,
    'default_locale' => 'ru',
    'asset'          => []
];
