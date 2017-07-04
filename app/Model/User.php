<?php

namespace App\Model;

use Illuminate\Auth\Authenticatable;
use Illuminate\Http\Request;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'api_token'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * 注册
     * @param Request $request
     * @return string
     */
    public static function register(Request $request)
    {
        if (self::where('username', $request->get("username"))->first()) {
            return "该用户名已被注册！";
        }
        $User = new User();
        $User->username = $request->get("username");
        $User->password = password_hash($request->get("password"), PASSWORD_DEFAULT);
        $User->email = $request->get("email");

        if ($User->save()) {
            return "注册成功！";
        } else {
            return "注册失败!";
        }
    }

    /**
     * 登录
     * @param Request $request
     * @return string
     */
    public static function login(Request $request)
    {
        $user = self::where("username", $request->get("username"))->first();

        if (password_verify($request->get("password"), $user->password)) {
            $token = str_random(60);
            $user->api_token = $token;
            $user->save();

            return $token;
        } else {
            return "用户名或密码不正确";
        }

    }
}
