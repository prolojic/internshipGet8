<?php

namespace App\Common\Bootstrap;

/**
 * Класс для создания типа приложения, чтобы в дальнейшем в зависимости от типа менять поведение
 * создаётся с помощью статического вызова несуществующего метода, где имя и будет типом приложения
 *
 * @example Application::api()
 *
 * @method static api()
 */
final class Application
{
    /**
     * The specified application.
     *
     * @var string
     */
    private $slug;

    private function __construct($slug)
    {
        $this->slug = (string)$slug;
    }

    /**
     * в зависимости от того какой метод вызывается такой тип приложение и создаётся
     *
     * @param $name
     * @param $args
     *
     * @return Application
     */
    public static function __callStatic($name, $args)
    {
        return new self($name);
    }

    /**
     * @param $applicationString
     *
     * @return Application
     */
    public static function fromString($applicationString): Application
    {
        return new self($applicationString);
    }

    /**
     * @param  Application $application
     *
     * @return bool
     */
    public function equals(Application $application): bool
    {
        return $this->slug === (string)$application;
    }

    public function __toString()
    {
        return $this->slug;
    }
}
