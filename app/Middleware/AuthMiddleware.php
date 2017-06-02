<?php

namespace App\Middleware;

/**
 * Class AuthMiddleware - check user authorization
 *
 * @package App\Middleware
 */
class AuthMiddleware extends Middleware
{

    public function __invoke($request,$responce,$next)
    {
        /*check user authorization
            if user is not authorized, redirect for authorization page*/
       if(!$this->container->auth->check())
       {
           return $responce->withRedirect($this->container->router->pathFor("signin"));
       }

       $responce = $next($request,$responce);

       return $responce;
    }
}