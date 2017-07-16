<?php
/**
 * Created by PhpStorm.
 * User: yueping
 * Date: 2017/7/12
 * Time: 22:37
 * email:596169733@qq.com
 */
namespace App\Http\Controllers;
use App\Model\Order;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class OrderController extends BaseController {

    public function rushBuy(Request $request)
    {
        if ($request->has('goods_id')) {
            $order = new Order();
            $data = $order->rushBuy($request);
            return json_encode($data);
        } else {
            return json_encode(["error"=>4001, "message"=>"缺少参数goods_id"]);
        }
    }
}