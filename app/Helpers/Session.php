<?php

namespace App\Helpers;

/**
 * Class Session
 *
 * @package App\Helpers
 */
class Session
{
    /**
     * Initiate session
     *
     * @return void
     */
    public static function init()
    {
        session_start();
    }

    /**
     * Set session parametres
     *
     * @param mixed $key Key in session array
     *
     * @param mixed $value Value in session array
     *
     * @return void
     */
    public static function set($key,$value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get session parametres
     *
     * @param mixed $key Key in session array
     *
     * @return mixed
     */
    public static function get($key)
    {
        return isset($_SESSION[$key])
            ? $_SESSION[$key]
            : null;
    }

    /**
     * Delete session parametres
     *
     * @param $key
     *
     * @return void;
     */
    public static function delete($key)
    {
        if(isset($_SESSION[$key]))
            unset($_SESSION[$key]);
    }

    /**
     * Destroy session
     *
     * @return void
     */
    public static function destroy()
    {
        session_destroy();
    }
}