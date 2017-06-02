<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class Clients extends Model
{
    /**
     * @var string
     */
    protected $table = "clients";

    /**
     * @var array
     */
    protected $fillable = [
        'act_number',
        'client_name',
        'client_it_name',
        'client_address',
        'client_manager',
        'client_sla',
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
    public function clientsNetwork()
    {
        return $this->hasMany(ClientsNetwork::class);
    }

    /**
     * Create new client and return id
     *
     * @param array $data - client data
     *
     * @return integer
     */
    public static function createClient($data)
    {
        $client = self::create([
            'act_number' => $data["act_number"],
            'client_name' => ucfirst(strtolower($data["client_name"])),
            'client_it_name' => ucfirst(strtolower($data["client_it_name"])),
            'client_address' => $data["client_address"],
            'client_manager' => strtoupper($data["client_manager"]),
            'client_sla' => $data["client_sla"],

        ]);

        return $client->id;
    }

    /**
     * Get client data
     *
     * @param integer $id - client id
     *
     * @return mixed
     */
    public static function getClientData($id)
    {
        return self::with("clientsNetwork")->where("id","=",$id)->first();
    }

    /**
     * Update client data
     *
     * @param integer $id - client id
     *
     * @param array $data - client data
     */
    public static function updateClientData($id,$data)
    {
        self::where("id","=",$id)->update([
            'act_number' => $data["act_number"],
            'client_name' => ucfirst(strtolower($data["client_name"])),
            'client_it_name' => ucfirst(strtolower($data["client_it_name"])),
            'client_address' => $data["client_address"],
            'client_manager' => strtoupper($data["client_manager"]),
            'client_sla' => $data["client_sla"],
        ]);
    }

    /**
     * Delete selected client
     *
     * @param integer $id - client id
     */
    public static function deleteClient($id)
    {
        self::find($id)->delete();
    }

}