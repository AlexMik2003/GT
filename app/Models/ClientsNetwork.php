<?php

namespace App\Models;

use App\Helpers\IP;
use \Illuminate\Database\Eloquent\Model;

class ClientsNetwork extends Model
{
    /**
     * @var string
     */
    protected $table = "clients_network";

    /**
     * @var array
     */
    protected $fillable = [
        'clients_id',
        'network',
        'mask',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clients()
    {
        return $this->belongsTo(Clients::class);
    }

    /**
     * Create clients network for new client
     *
     * @param integer $id - clients id
     *
     * @param IP $ip
     *
     * @param array $network - client network
     *
     * @param array $mask - client network mask
     *
     * @param integer $count - count of networks
     */
    public static function createClientNetworks($id,$ip,$network,$mask,$count)
    {
        for($i=0;$i<$count;$i++)
        {
            self::create([
                'clients_id' => $id,
                'network' => $ip->convertIp2Long($network[$i]),
                'mask' => $ip->convertIp2Long($mask[$i]),
            ]);
        }
    }

    /**Get all data from table
     *
     * @return mixed
     */
    public static function getAllData()
    {
        return self::get();
    }

    /**
     * Delete networks for selected client
     *
     * @param integer $value - client id
     */
    public static function deleteClientNetworks($id)
    {
        self::where("clients_id",$id)->delete();
    }

}