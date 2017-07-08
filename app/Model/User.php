<?php

namespace App\Model;

use Illuminate\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            return json_encode(["error"=>4002, "message"=>"该用户名已被注册！"]);
        }
        $User = new User();
        $User->username = $request->get("username");
        $User->password = password_hash($request->get("password"), PASSWORD_DEFAULT);
        $User->email = $request->get("email");

        if ($User->save()) {
            return "SUCCESS";
        } else {
            return json_encode(["error"=>4003, "message"=>"该用户名已被注册！"]);
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

        if (empty($user)) {
            return json_encode(["error"=>4004, "message"=>"请输入正确的用户名！"]);
        }

        if (password_verify($request->get("password"), $user->password)) {
            $token = str_random(60);
            $user->api_token = $token;
            $user->save();
            $user_info = [
                'username'  => $user->username,
                'email'     => $user->email,
                'api_token' => $user->api_token
            ];

            return json_encode($user_info);
        } else {
            return json_encode(["error"=>4004, "message"=>"用户名或密码错误！"]);
        }

    }
}
