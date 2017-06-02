<?php

namespace App\Controllers\Log;

use App\Controllers\BaseController;

class LogController extends BaseController
{
    /**
     * @var string
     */
    protected $logFile = "/logs/infoLog";

    /**
     * Show log page
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function pageLog($request,$responce)
    {
        $arr = [];
        $handle = fopen(ROOT_PATH.$this->logFile, "r");
        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {
                array_push($arr,$buffer);
            }
            if (!feof($handle)) {
                echo "Error: unexpected fgets() fail\n";
            }
            fclose($handle);
        }

        $log = array_reverse($arr);
        return $this->view->render($responce, "/log.twig",["log" => $log]);
    }

    public function LogInformMessage()
    {
        $arr = [];
        $handle = fopen(ROOT_PATH.$this->logFile, "r");
        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {
                array_push($arr,$buffer);
            }
            if (!feof($handle)) {
                echo "Error: unexpected fgets() fail\n";
            }
            fclose($handle);
        }

        $log = array_reverse($arr);

        $str = explode("{",$log[0]);
        $time = explode("]",$str[0]);
        $time = str_replace('[', "", $time[0]);
        $user = explode(":",$str[1]);
        $user = explode("}",$user[2]);
        $user = str_replace('"', "", $user[0]);

        $message = "Last changes were made by ".$user." [".$time."]";
        return $message;
    }

}