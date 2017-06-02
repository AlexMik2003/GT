<?php


namespace App\Middleware;

/**
 * Class Middleware
 *
 * @package App\Middleware
 */
class Middleware
{
    /**
     * @var
     */
    protected $container;

    /**
     * Middleware constructor.
     *
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;

    }
}