<?php

namespace App\Controllers\Area;

use App\Controllers\BaseController;
use App\Models\Area;
use App\Models\Contacts;
use App\Models\Device;
use App\Models\Users;
use App\Helpers\Session;
use \Respect\Validation\Validator as valid;

/**
 * Class AreaController - for managing area information
 *
 * @package App\Controllers\Area
 */
class AreaController extends BaseController
{
    /**
     * Show area page
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function pageArea($request,$responce)
    {
        return $this->view->render($responce, "/area/area.twig");
    }

    /**
     * Show page for adding new area
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function pageAddArea($request,$responce)
    {
        $data = [
            "country" => "Украина",
            "city" => "Киев",
        ];
        return $this->view->render($responce, "/area/new.twig",["geo" => $data]);
    }

    /**
     * Create new area
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function addArea($request,$responce)
    {
        //validate area data
        $validation = $this->validator->validate($request,[
            'area_name' => valid::noWhitespace()->notEmpty()->AreaAvailable(),
            'address' => valid::notEmpty()->stringType(),
            'city' => valid::notEmpty()->stringType(),
            'country' => valid::notEmpty()->stringType(),
            'contacts_name' => valid::arrayVal()->each(valid::when(valid::notEmpty(),valid::stringType(),valid::equals(''))),
            'contacts_phone' => valid::arrayVal()->each(valid::when(valid::notEmpty(),valid::stringType(),valid::equals(''))),
            'contacts_mail' =>  valid::arrayVal()->each(valid::when(valid::notEmpty(),valid::email(),valid::equals(''))),

        ]);

        if($validation->failed())
        {
            return $responce->withRedirect($this->router->pathFor("area.new"));
        }


        //create new area
        $area = Area::createArea($request->getParams());


        //adding contacts for new area
        Contacts::createContacts($area,$request->getParam('contacts_name'),$request->getParam('contacts_phone'),$request->getParam('contacts_mail'),$request->getParam("hide_contacts"));

        //create log message
        $user = Users::getUser(Session::get("id"));
        $this->logger->info("Create area", array("area" => $request->getParam("area_name"),"user"=>$user->login));

        //create inform message
        $this->flash->addMessage("success", strtoupper("New area is created!"));
        return $responce->withRedirect($this->router->pathFor("area.management"));
    }

    /**
     * Get all area from DB and decode to json
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return json
     */
    public function areaList($request,$responce)
    {
        //order parametres
        $orderby = $request->getParam('order')[0]["column"];
        $sort['col'] = $request->getParam('columns')[$orderby]['data'];
        $sort['dir'] = $request->getParam('order')[0]["dir"];

        //search request
        $search = $request->getParam('search')['value'];

        //select rows from db by search request
        $query = Area::with("contacts")->where('area', 'like', '%' . $search . '%');

        //rows count
        $output['recordsTotal'] = $query->count();

        //get data from query request
        $output['data'] = $query->orderBy($sort["col"],$sort["dir"])->skip($request->getParam('start'))->take($request->getParam('length',10))->get();

        //data filter
        $output['recordsFiltered'] = $output['recordsTotal'];

        //draw parametr
        $output['draw'] = intval($request->getParam('draw'));

        //create array of area data
        foreach ($output["data"] as $key => $value)
        {
            $value["address"] = $value["address"].",".$value["city"].",".$value["country"];
            $contacts = '';
            foreach ($value['contacts'] as $item)
            {
                $contacts .= $item->name . "&emsp;" . $item->phone . "&emsp;" . $item->mail . "<br>";
            }
            $value["area_contacts"] = $contacts;
            $value['device_count'] = Device::getDeviceCount($value["id"]);
        }

        //encode area data to json
        $json = json_encode($output);

        echo $json;

    }

    /**
     * Show page for editing area information
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @param array $args - array of other parametres, such as area id
     *
     * @return mixed
     */
    public function pageEditArea($request,$responce,$args)
    {
       $area = Area::getAreaData($args["id"]);

        return $this->view->render($responce, "/area/edit.twig",["area" => $area]);

    }

    /**
     * Edit and update area information
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @param array $args - array of other parametres, such as area id
     *
     * @return mixed
     */
    public function editArea($request,$responce,$args)
    {
        //validate area data
        $validation = $this->validator->validate($request,[
            'area_name' => valid::noWhitespace()->notEmpty(),
            'address' => valid::notEmpty()->stringType(),
            'city' => valid::notEmpty()->stringType(),
            'country' => valid::notEmpty()->stringType(),
            'contacts_name' => valid::arrayVal()->each(valid::when(valid::notEmpty(),valid::stringType(),valid::equals(''))),
            'contacts_phone' => valid::arrayVal()->each(valid::when(valid::notEmpty(),valid::stringType(),valid::equals(''))),
            'contacts_mail' =>  valid::arrayVal()->each(valid::when(valid::notEmpty(),valid::email(),valid::equals(''))),

        ]);

        if($validation->failed())
        {
            return $responce->withRedirect($this->router->pathFor("area.edit",array("id" => $args["id"])));
        }

        //delete contacts
        Contacts::deleteAreaContacts($args["id"]);

        //update area data
        Area::updateAreaData($args["id"],$request->getParams());

        //adding contacts for new area
        Contacts::createContacts($args["id"],$request->getParam('contacts_name'),$request->getParam('contacts_phone'),$request->getParam('contacts_mail'),$request->getParam("hide_contacts"));

        //create log message
        $user = Users::getUser(Session::get("id"));
        $this->logger->info("Change area data", array("area" => $request->getParam("area_name"),"user" => $user->login));

        //create inform message
        $this->flash->addMessage("success", strtoupper("Area is updated!"));
        return $responce->withRedirect($this->router->pathFor("area.management"));
    }

    /**
     * Delete selected area
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @param array $args - array of other parametres, such as area id
     *
     * @return mixed
     */
    public function deleteArea($request,$responce,$args)
    {
        $area = Area::where("id","=",$args["id"])->first();
        Area::deleteArea($args["id"]);
        Contacts::deleteAreaContacts($args["id"]);

        //create log message
        $user = Users::getUser(Session::get("id"));
        $this->logger->info("Delete area", array("area" => $area->area, "user" => $user->login));

        //create inform message
        $this->flash->addMessage("warning", strtoupper("Area is deleted!"));
        return $responce->withRedirect($this->router->pathFor("area.management"));
    }
}