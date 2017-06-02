<?php

namespace App\Models;

use App\Helpers\IP;
use \Illuminate\Database\Eloquent\Model;

class PeersNetwork extends Model
{
    /**
     * @var string
     */
    protected $table = "peers_network";

    /**
     * @var array
     */
    protected $fillable = [
        'peers_id',
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
    public function peers()
    {
        return $this->belongsTo(Peers::class);
    }

    /**
     * Create peer network for new peer
     *
     * @param integer $id - peer id
     *
     * @param IP $ip
     *
     * @param array $network - peer network
     *
     * @param array $mask - peer network mask
     *
     * @param integer $count - count of networks
     */
    public static function createPeerNetworks($id,$ip,$network,$mask,$count)
    {
        for($i=0;$i<$count;$i++)
        {
            self::create([
                'peers_id' => $id,
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
     * @param integer $value - peer id
     */
    public static function deletePeerNetworks($id)
    {
        self::where("peers_id",$id)->delete();
    }
}