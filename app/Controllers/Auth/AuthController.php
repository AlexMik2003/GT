<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\Users;
use App\Helpers\Session;

/**
 * Class AuthController - user authorization class
 *
 * @package App\Controllers\Auth
 */
class AuthController extends  BaseController
{
    /**
     * Signin page
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function pageSignIn($request,$responce)
    {
        return $this->view->render($responce, "signin.twig");
    }

    /**
     * User autorization
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function Authorization($request,$responce)
    {
        $auth = $this->auth->attempt(
            $request->getParam("username"),
            $request->getParam("password")
        );

        if(!$auth)
        {
            //create inform message
            $this->flash->addMessage("error", "Invalid User Name/Password Please Retype");
            return $responce->withRedirect($this->router->pathFor("signin"));
        }

        Users::where("id","=",Session::get("id"))->update([
           "last_login" => date("Y-m-d H:i:s"),
        ]);
        return $responce->withRedirect($this->router->pathFor("dashboard"));
    }

    /**
     * User logout
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function SignOut($request,$responce)
    {
        $user = Users::getUser(Session::get("id"));
        Session::delete("id");
        Session::delete("user");
        $user->activeUser(0);
        return $responce->withRedirect($this->router->pathFor("signin"));
    }
}