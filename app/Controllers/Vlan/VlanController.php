<?php

namespace App\Controllers\Vlan;

use App\Controllers\BaseController;
use App\Models\Vlan;

class VlanController extends BaseController
{
    /**
     * Show vlans
     *
     * @param Request $request
     *
     * @param Response $responce
     *
     * @return mixed
     */
    public function pageVlan($request,$responce,$args)
    {
        $page = ($args["page"] > 0) ? $args['page'] : 1;
        $limit = 256;
        $skip = ($page - 1) * $limit;

        $count = Vlan::getCount();

        $output['data'] = Vlan::getVlan($skip,$limit);

        return $this->view->render($responce,"/vlan/vlan.twig",[
            'pagination'    => [
                'needed'        => $count > $limit,
                'count'         => $count,
                'page'          => $page,
                'lastpage'      => (ceil($count / $limit) == 0 ? 1 : ceil($count / $limit)),
                'limit'         => $limit,
            ],
            "vlans" => $output['data']
        ]);
    }
}