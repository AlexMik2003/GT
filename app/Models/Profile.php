<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    /**
     * @var string
     */
    protected $table = "profile";

    /**
     * @var array
     */
    protected $fillable = [
        'users_id',
        'first_name',
        'last_name',
        'email',
        'privilege',
        'userpic',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users()
    {
        return $this->belongsTo(Users::class);
    }

    /**
     * Get user data from DB
     *
     * @param integer $id - user id
     *
     * @return mixed
     */
    public static function getUserData($id)
    {
        return self::with("users")->where("users_id","=",$id)->first();
    }

    public static function updateUserData($id,array $data)
    {
        self::where("users_id","=",$id)->update([
            "first_name" => ucfirst($data["first_name"]),
            "last_name" => ucfirst($data["last_name"]),
            "email" => $data["email"],
        ]);
    }

    /**
     * Create new profile for new user
     *
     * @param integer $id - user id
     *
     * @param array $data - users data
     *
     * @param string $userpic - user profile picture
     */
    public static function createProfile($id,$data,$userpic)
    {
        self::create([
            "users_id" => $id,
            "first_name" => ucfirst($data["first_name"]),
            "last_name" => ucfirst($data["last_name"]),
            "email" => $data["email"],
            "privilege" => $data["admin"],
            "userpic" => $userpic,
        ]);
    }

    /**
     * Delete user profile
     *
     * @param integer $value - users id
     */
    public static function deleteProfile($value)
    {
        self::where("users_id",$value)->delete();
    }

}