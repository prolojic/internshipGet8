<?php

namespace App\Common\Bootstrap;

use \Phalcon\Config;
use Phalcon\Debug;
use \Phalcon\DI\InjectionAwareInterface;
use Phalcon\DiInterface;
use Phalcon\Loader;

final class Bootstrap implements InjectionAwareInterface
{
    /**
     * Dependency Injector reference
     *
     * @var DiInterface
     */
    private $di;

    /**
     * The loader of namespace
     *
     * @var Loader
     */
    private $loader;

    /**
     * Bootstrap constructor - sets some defaults and stores the di interface
     *
     * @param DiInterface $di
     * @param Application $app
     * @param Environment $env
     *
     * @throws \Exception
     */
    public function __construct(DiInterface $di, Application $app, Environment $env)
    {
        $this->di     = $di;
        $this->loader = new Loader();
        $this->loader->register();
        $this->di->set('env', $env);
        $this->di->set('app', $app);

        $this->loader
            ->registerNamespaces(
                [
                    'App\\Common' => APP_PATH . '/common/',
                    'App\\Blog'   => APP_PATH . '/blog/',
                ],
                true
            );

        // подгружаем необходимые начальные конфиги
        $this
            // первичная настройка фреймворка
            ->addConfig(APP_PATH . '/resource/config/setting.php', true)
            // настройки среды (конекты для внешних сервисов: redis, etc)
            ->addConfig(APP_PATH . '/resource/config/parameter_' . $env . '.php', true)
            // производим первичную загрузку сервисов, необходимых для поднятия приложения
            // (минимум mongo, collectionManager)
            ->addService(APP_PATH . '/resource/config/service_core.php', true)
            ->addService(APP_PATH . '/resource/config/service_' . $app . '.php', true);

        if ($app == 'api' && isset($this->di->get('config')->debug) && $this->di->get('config')->debug) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            (new Debug())->listen();
        }
    }

    /**
     * Добавить очередной конфиг, с возможным подгрузом новых неймспейсов и классов
     *
     * @param string  $path
     * @param boolean $require - если конфиг обязательный, то при отсутсвии файла выкинуть исключение
     *
     * @throws \Exception
     *
     * @return Bootstrap
     */
    private function addConfig($path, $require = false): Bootstrap
    {
        $config = null;
        if (is_readable($path)) {
            $config = new Config(require $path);
        } elseif ($require === true) {
            throw new \RuntimeException('Config "' . basename($path) . '" was not found!', 418);
        } else {
            return $this;
        }

        if ($this->di->has('config')) {
            $this->di->get('config')->merge($config);
        } else {
            $this->di->set('config', $config);
        }

        return $this;
    }

    /**
     * Добавить очередной набор сервисов
     *
     * @param string  $path
     * @param boolean $require - если конфиг обязательный, то при отсутсвии файла выкинуть исключение
     *
     * @return Bootstrap|bool
     *
     * @throws \Exception
     */
    private function addService($path, $require = false)
    {
        $config = null;
        if (is_readable($path)) {
            $services = include $path;
            foreach ($services as $service) {
                $this->di->set($service[0], $service[1], $service[2] ?? false);
            }
        } elseif ($require === true) {
            throw new \RuntimeException('Services file was not found!', 418);
        } else {
            return false;
        }

        return $this;
    }

    /**
     * демонизируем данный процесс, аккуратнее, не применять для web, только cli
     *
     * для поддержание уникальности демона создаём .pid-файл для конкретной команды
     * и демонизируем его (отвязываем от консоли), расположив после инициализации приложения,
     * т.к. нужен конфиг, где указана папка .pid-файлов для демонов
     *
     * @param string $postfix   - уникальный постфикс конкретного типа запроса текущего
     *                          виджета вида <контроллер>_<экшен> для названия .pid-файла
     */
    public function demonize($postfix): void
    {
        // создаем дочерний процесс
        $childPid = pcntl_fork();

        if ($childPid < 0) {
            die('could not fork');
        }

        if ($childPid) {
            // выходим из родительского, привязанного к консоли, процесса
            exit;
        }

        // делаем основным процессом дочерний
        $childPid = posix_setsid();

        if ($childPid < 0) {
            exit;
        }

        // название pid-файла состоить из окружения, чтобы тестить без
        // остановки dev-демонов, имени виджета, контроллера и экшена
        $pidFile = $this->di->get('config')['daemon'] . '/'
            . $this->di->get('env') . '-'
            . ($this->di->get('widgetName') ? : '') . '-'
            . $postfix . '.pid';

        if (is_file($pidFile)) {
            $pid = file_get_contents($pidFile);
            //проверяем на наличие процесса
            if (posix_kill($pid, 0)) {
                die("Already running\n");
            }

            //pid-файл есть, но процесса нет
            if (!unlink($pidFile)) {
                //не могу уничтожить pid-файл. ошибка
                exit(-1);
            }
        }

        file_put_contents($pidFile, getmypid());
    }

    /**
     * @param DiInterface $di
     *
     * @return void
     */
    public function setDi(DiInterface $di): void
    {
        $this->di = $di;
    }

    /**
     * @return DiInterface
     */
    public function getDi(): DiInterface
    {
        return $this->di;
    }
}