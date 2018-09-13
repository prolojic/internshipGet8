<?php

namespace App\Blog\Controller;

use Phalcon\Cache\Backend;
use Phalcon\Mvc\Controller;

/**
 * Class IndexController
 *
 * @property Backend cache
 */
class IndexController extends Controller
{
    public function indexAction()
    {
        print_r($this->request->get());


        return $this->response->setContent('WOW It"s working!');
    }

    public function testAction($di)
    {
        echo 'Test DI: <br/>';

        print_r($this->$di);
    }

    public function cacheAction()
    {
        $counter = 0;
        if ($this->cache->exists('counter')) {
            $counter = $this->cache->get('counter');

            echo 'Счетчик входов:' .  $counter;
        }

        $counter++;
        $this->cache->save('counter', $counter, 84600);
    }
}