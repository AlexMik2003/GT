<?php

namespace App\Middleware;

use App\Helpers\Session;

/**
 * Class ValidationErrorsMiddleware - check and validate input data
 *
 * @package App\Middleware
 */
class ValidationErrorsMiddleware extends Middleware
{
    public function __invoke($request,$responce,$next)
    {
        $this->container->view->getEnvironment()->addGlobal("errors",Session::get("errors"));
        Session::delete("errors");
        $responce = $next($request,$responce);
        return $responce;
    }
}