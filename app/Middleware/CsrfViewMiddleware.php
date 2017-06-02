<?php

namespace App\Middleware;

/**
 * Class CsrfViewMiddleware - csrf security
 *
 * @package App\Middleware
 */
class CsrfViewMiddleware extends Middleware
{
    public function __invoke($request,$responce,$next)
    {
        $this->container->view->getEnvironment()->addGlobal("csrf",
            [
                'field' => '
                <input type="hidden" name="'.$this->container->csrf->getTokenNameKey().'"
                value="'.$this->container->csrf->getTokenName().'">
                <input type="hidden" name="'.$this->container->csrf->getTokenValueKey().'"
                value="'.$this->container->csrf->getTokenValue().'">',
            ]);
        $responce = $next($request,$responce);
        return $responce;
    }
}