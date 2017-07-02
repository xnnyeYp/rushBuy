<?php
/**
 * Created by PhpStorm.
 * User: yueping
 * Date: 2017/6/27
 * Time: 23:42
 * email:596169733@qq.com
 */
namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class IndexController extends BaseController {
    
    public function index()
    {
        return view("index/index");
    }
}