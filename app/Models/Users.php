<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    /**
     * @var string
     */
    protected $table = "users";

    /**
     * @var array
     */
    protected $fillable = [
        'login',
        'password',
        'created',
        'updated',
        'active',
        'last_login',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * Set new user password
     *
     * @param string $password - user password
     */
    public function setPassword($password)
    {
        $this->update([
            "password" => password_hash($password, PASSWORD_DEFAULT, ["cost" => 13]),
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Get user data from DB
     *
     * @param integer $id - user id
     *
     * @return mixed
     */
    public static function getUser($id)
    {
        return self::where("id","=",$id)->first();
    }

    /**
     * Change user active status
     *
     * @param integer $active - user active status
     */
    public function activeUser($active)
    {
        $this->update([
            "active" => $active,
        ]);
    }

    /**
     * Create new user and return id
     *
     * @param array $data - users data
     *
     * @return integer
     */
    public static function createUser($data)
    {
        $user = self::create([
            "login" => strtolower($data["login"]),
            "password" => password_hash($data["password"],PASSWORD_DEFAULT,["cost"=>13])
        ]);

        return $user->id;
    }

    /**
     * Delete selected user
     *
     * @param integer $value - user id
     */
    public static function deleteUser($value)
    {
        self::find($value)->delete();
    }

}