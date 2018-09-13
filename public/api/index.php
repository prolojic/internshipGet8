<?php

use App\Common\Bootstrap\Bootstrap;

define('ROOT_PATH', __DIR__ . '/../..');
define('APP_PATH', ROOT_PATH . '/app');
define('VENDOR_PATH', ROOT_PATH . '/vendor');

require_once VENDOR_PATH . '/autoload.php';

try {
    require_once APP_PATH . '/common/Bootstrap/Bootstrap.php';
    require_once APP_PATH . '/common/Bootstrap/Environment.php';
    require_once APP_PATH . '/common/Bootstrap/Application.php';

    $app = new Bootstrap(
        new \Phalcon\Di\FactoryDefault(),
        \App\Common\Bootstrap\Application::api(),
        \App\Common\Bootstrap\Environment::fromString('dev')
    );

    /** @var \Phalcon\Mvc\Router $router */
    $router = $app->getDi()->get('router');

    /** @var Phalcon\Mvc\Dispatcher $dispatcher */
    $dispatcher = $app->getDi()->get('dispatcher');

    $router->add(
        '/blog/:controller/:action/:params',
        [
            'namespace'  => 'App\\Blog\\Controller\\',
            'controller' => 1,
            'action'     => 2,
            'params'     => 3,
        ]
    );

    $router->handle();

    if (!$router->wasMatched()) {
        (new \Phalcon\Http\Response())
            ->setContentType('application/json')
            ->setStatusCode(404)
            ->send();

        return;
    }

    $dispatcher->setControllerName($router->getControllerName());
    $dispatcher->setActionName($router->getActionName());
    $dispatcher->setParams($router->getParams());
    $dispatcher->setDefaultNamespace($router->getNamespaceName());

    /**
     * Запуск контроллера
     */
    $dispatcher->dispatch();

    $response = $dispatcher->getReturnedValue();

    if ($response instanceof \Phalcon\Http\ResponseInterface) {
        $response->send();
    }
} catch (\Exception $e) {
    (new \Phalcon\Http\Response())
        ->setContentType('application/json')
        ->setStatusCode(500)
        ->setJsonContent(
            [
                'success'    => false,
                'data'       => null,
                'error'      => $e->getMessage(),
                'error_code' => $e->getCode(),
            ]
        )
        ->send();
}