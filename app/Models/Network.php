<?php

namespace App\Models;

use App\Helpers\IP;
use \Illuminate\Database\Eloquent\Model;

class Network extends Model
{
    /**
     * @var string
     */
    protected $table = "network";

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'network',
        'netmask',
        'created',
        'updated',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * Create new network
     *
     * @param array $data - network data
     *
     * @return integer
     */
    public static function createNetwork($ip,$data)
    {
         self::create([
             "name" => ucfirst($data["ip_name"]),
             "network" => $ip->convertIp2Long($data["ip_net"]),
             "netmask" => $ip->convertIp2Long($data["ip_mask"]),
        ]);
    }

    /**
     * Delete selected network
     *
     * @param integer $id - network id
     */
    public static function deleteNetwork($id)
    {
        self::find($id)->delete();
    }

    /**
     * Get all data about network
     *
     * @param integer $id - network id
     *
     * @return mixed
     */
    public static function getData($id)
    {
        return self::where("id","=",$id)->first();
    }

}