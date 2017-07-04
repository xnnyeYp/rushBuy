<?php
/**
 * Created by PhpStorm.
 * User: yueping
 * Date: 2017/7/3
 * Time: 23:58
 * email:596169733@qq.com
 */
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Model\User;


class UserController extends BaseController {

    public function register(Request $request)
    {
        if ($request->has('username') && $request->has('password') && $request->has('email')) {
            return User::register($request);
        } else {
            return "请输入完整的用户信息！";
        }
    }

    public function login(Request $request)
    {
        if ($request->has("username") && $request->has("password")) {
            return User::login($request);
        } else {
            return "请输入完整的登录信息！";
        }
    }
    
    public function info()
    {
        return Auth::user();
    }
}