<?php

namespace App\Auth;

use App\Models\Users;
use App\Helpers\Session;
/**
 * Class Auth - autorization class
 *
 * @package App\Auth
 */
class Auth
{
    /**
     * Check user and verify password
     *
     * @param string $login - user login
     *
     * @param string $password - user password
     *
     * @return bool
     */
    public function attempt($login,$password)
    {
        $user = Users::where("login",$login)->first();

        if(!$user){
            return false;
        }

        if(password_verify($password,$user->password))
        {
            Session::set("id", $user->id);
            Session::set("user", $user->login);
            $user->activeUser(1);
            return true;
        }

        return false;
    }

    /**
     * Check user authorization
     *
     * @return mixed
     */
    public function check()
    {
        return Session::get("user");
    }
}