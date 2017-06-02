<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class Peers extends Model
{
    /**
     * @var string
     */
    protected $table = "peers";

    /**
     * @var array
     */
    protected $fillable = [
        'peer_name',
        'peer_it_name',
        'peer_as',
        'peer_vlan',
        'created',
        'updated',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function peersNetwork()
    {
        return $this->hasMany(PeersNetwork::class);
    }

    /**
     * Create new peer and return id
     *
     * @param array $data - peer data
     *
     * @return integer
     */
    public static function createPeer($data)
    {
        $peer = self::create([
            'peer_name' => ucfirst(strtolower($data["peer_name"])),
            'peer_it_name' => ucfirst(strtolower($data["peer_it_name"])),
            'peer_as' => $data["peer_as"],
            'peer_vlan' => $data["peer_vlan"],

        ]);

        return $peer->id;
    }

    /**
     * Get peer data
     *
     * @param integer $id - peer id
     *
     * @return mixed
     */
    public static function getPeerData($id)
    {
        return self::with("peersNetwork")->where("id","=",$id)->first();
    }

    /**
     * Update peer data
     *
     * @param integer $id - peer id
     *
     * @param array $data - peer data
     */
    public static function updatePeerData($id,$data)
    {
        self::where("id","=",$id)->update([
            'peer_name' => ucfirst(strtolower($data["peer_name"])),
            'peer_it_name' => ucfirst(strtolower($data["peer_it_name"])),
            'peer_as' => $data["peer_as"],
            'peer_vlan' => $data["peer_vlan"],
        ]);
    }

    /**
     * Delete selected peer
     *
     * @param integer $id - peer id
     */
    public static function deletePeer($id)
    {
        self::find($id)->delete();
    }

}