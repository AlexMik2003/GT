<?php

namespace App\Middleware;

use App\Helpers\Session;

/**
 * Class OldInputMiddleware - return old input data
 *
 * @package App\Middleware
 */
class OldInputMiddleware extends Middleware
{
    public function __invoke($request,$responce,$next)
    {
        $this->container->view->getEnvironment()->addGlobal("old",Session::get("old"));
        Session::set("old",$request->getParams());

        $responce = $next($request,$responce);
        return $responce;
    }
}