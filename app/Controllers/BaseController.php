<?php

namespace App\Controllers;

/**
 * Class BaseController
 *
 * @package App\Controllers
 */
class BaseController
{
    /**
     * @var object
     */
    protected $container;

    /**
     * BaseController constructor.
     *
     * @param object $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @param string $name - controller name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if($this->container->get($name))
        {
            return $this->container->get($name);
        }
    }
}