<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Helpers\Session;
use App\Models\Profile;
use App\Models\Users;
use \Respect\Validation\Validator as valid;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class UserController - for managing user data
 *
 * @package App\Controllers\User
 */
class UserController extends BaseController
{
    /**
     * Get user first name and last name
     *
     * @return bool|string
     */
    public function getUserData()
    {
        return Session::get("id")
            ? Profile::getUserData(Session::get("id"))
            : false;
    }

    /**
     * Show page for updating user profile
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function pageProfile($request,$responce)
    {
        $profile = Profile::getUserData(Session::get("id"));
        return $this->view->render($responce, "/user/profile.twig",["profile" => $profile]);
    }

    /**
     * Update user profile
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
   public function updateUserProfile($request,$responce)
    {
        //validate user insert data
        $validation = $this->validator->validate($request,[
            'first_name' => valid::noWhitespace()->notEmpty()->alpha(),
            'last_name' => valid::noWhitespace()->notEmpty()->alpha(),
            'email' => valid::noWhitespace()->notEmpty()->email(),
        ]);

        if($validation->failed())
        {
            return $responce->withRedirect($this->router->pathFor("user.profile"));
        }

        //update database
        Profile::updateUserData(Session::get("id"),$request->getParams());

        //create log message
        $user = Users::getUser(Session::get("id"));
        $this->logger->info("Change user profile data", array("profile owner" => $request->getParam("first_name")." ".$request->getParam("last_name"),"user" => $user->login));

        //create inform message
        $this->flash->addMessage("success", strtoupper("Profile has been updated successfully!"));
        return $responce->withRedirect($this->router->pathFor("dashboard"));
    }

    /**
     * Show page for changing user password
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function pagePassword($request,$responce)
    {
        return $this->view->render($responce, "/user/password.twig");
    }

    /**
     * Change user password
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function changeUserPassword($request,$responce)
    {
        $user = Users::getUser(Session::get("id"));

        //validate user data
        $validation = $this->validator->validate($request,[
            "password_old" => valid::noWhitespace()->notEmpty()->MatchesPassword($user->password),
            "password_new" => valid::noWhitespace()->notEmpty(),
        ]);

        if($validation->failed())
        {
            return $responce->withRedirect($this->router->pathFor("user.password"));
        }

        //change user password
        $user->setPassword($request->getParam("password_new"));

        //create log message
        $user = Users::getUser(Session::get("id"));
        $this->logger->info("Change user password", array("owner" => $user->login,"user" => $user->login));

        //create inform message
        $this->flash->addMessage("success", strtoupper("Password has been updated successfully!"));
        return $responce->withRedirect($this->router->pathFor("dashboard"));
    }

    /**
     * Show users page
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function pageUsers($request,$responce)
    {
        return $this->view->render($responce, "/user/users.twig");
    }

    /**
     * Get all users from DB and decode to json
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return json
     */
    public function usersList($request,$responce)
    {
        //order parametres
        $orderby = $request->getParam('order')[0]["column"];
        $sort['col'] = $request->getParam('columns')[$orderby]['data'];
        $sort['dir'] = $request->getParam('order')[0]["dir"];

        //search request
        $search = $request->getParam('search')['value'];

        //select rows from db by search request
        $query = Users::with("profile")->whereHas('profile', function ($q) use ($search){
            $q->where('first_name', 'like', '%' . $search . '%')->orWhere('last_name', 'like', '%' . $search . '%');
        });

        //rows count
        $output['recordsTotal'] = $query->count();

        //get data from query request
        $output['data'] = $query->orderBy($sort["col"],$sort["dir"])->skip($request->getParam('start'))->take($request->getParam('length',10))->get();

        //data filter
        $output['recordsFiltered'] = $output['recordsTotal'];

        //draw parametr
        $output['draw'] = intval($request->getParam('draw'));

        //create array of users data
        foreach ($output["data"] as $key => $value)
        {
            $value["full_name"] = $value->profile["first_name"]."&nbsp;".$value->profile["last_name"];
            $datetime = explode(" ",$value['last_login']);
            $date = explode("-",$datetime[0]);
            $value["last_log"] = date("F j, Y",mktime(0,0,0,intval($date[1]),intval($date[2]), intval($date[0])))." ".$datetime[1];
            if($value->profile["privilege"]==null)
            {
                $value["privilege"] = "user";
            }
            else  $value["privilege"] = "admin";

            if($value["active"]==1)
            {
                $value["status"] = "<span style='color:limegreen;'><b>online</b></span>";
            }
            else   $value["status"] = "<span style='color:red;'><b>offline</b></span>";

        }

        //encode users data to json
        $json = json_encode($output);

        echo $json;

    }

    /**
     * Get action
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function getUserAction($request,$responce)
    {
        switch ($request->getParam("action"))
        {
            case "add":
                return $responce->withRedirect($this->router->pathFor("user.new"));
                break;
            case "del":
                $this->delUser($request->getParam("users_id"));
                $this->flash->addMessage("warning", strtoupper("Selected users were deleted!"));
                return $responce->withRedirect($this->router->pathFor("user.management"));
                break;
        }
    }

    /**
     * Show page for adding new user
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function pageAddUser($request,$responce)
    {
        return $this->view->render($responce, "/user/new.twig");
    }

    protected function getUserPic()
    {
        $part = ROOT_PATH."/public";
        $pictures = glob($this->userpic_directory."/*.png");
        $pic = array_rand($pictures,1);
        $userpic = explode($part,$pictures[$pic]);
        return $userpic[1];
    }

    /**
     * Create new user
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function addUser($request,$responce)
    {
        //validate user data
        $validation = $this->validator->validate($request,[
            'login' => valid::noWhitespace()->notEmpty()->LoginAvailable(),
            'password' => valid::noWhitespace()->notEmpty(),
            'first_name' => valid::noWhitespace()->notEmpty()->alpha(),
            'last_name' => valid::noWhitespace()->notEmpty()->alpha(),
            'email' => valid::noWhitespace()->notEmpty()->email(),
        ]);

        if($validation->failed())
        {
            return $responce->withRedirect($this->router->pathFor("user.new"));
        }

        //create new user and insert data to db
        $user = Users::createUser($request->getParams());

        //create new user profile and insert data to db
        Profile::createProfile($user,$request->getParams(),$this->getUserPic());

        //create log message
        $user = Users::getUser(Session::get("id"));
        $this->logger->info("Create user", array("created user" =>$request->getParam("login"),"user" => $user->login));

        //create inform message
        $this->flash->addMessage("success", strtoupper("New user is created!"));
        return $responce->withRedirect($this->router->pathFor("user.management"));
    }

    /**
     * Delete selected users
     *
     * @param array $id - users id
     */
    public function delUser($id)
    {
        //delete all rows from users and profile which id was selected
        foreach ($id as $key => $value)
        {
            $userDel = Users::where("id","=",$value)->first();
            Users::deleteUser($value);
            Profile::deleteProfile($value);

            //create log message
            $user = Users::getUser(Session::get("id"));
            $this->logger->info("Delete user", array("deleted user" => $userDel->login,"user" => $user->login));
        }
    }
}