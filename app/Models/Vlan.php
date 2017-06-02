<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class Vlan extends Model
{
    /**
     * @var string
     */
    protected $table = "vlans";

    /**
     * @var array
     */
    protected $fillable = [
        "id",
        'vlan',
        'created',
        'updated',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get vlan data
     *
     * @param integer $skip - offset
     *
     * @param integer $limit - limit
     *
     * @return mixed
     */
    public static function getVlan($skip,$limit)
    {
        return self::skip($skip)->take($limit)->get();
    }

    /**
     * Get vlans count
     *
     * @return mixed
     */
    public static function getCount()
    {
        return self::count();
    }
}