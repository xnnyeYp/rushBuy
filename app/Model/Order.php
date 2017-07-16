<?php
/**
 * Created by PhpStorm.
 * User: yueping
 * Date: 2017/7/12
 * Time: 22:48
 * email:596169733@qq.com
 */
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class Order extends Model  {

    private static $redis;

    public function rushBuy($request)
    {
        self::$redis = new \Redis();
        self::$redis->connect('127.0.0.1');
        $redis = &self::$redis;
        $goods_id = $request->get('goods_id');

        if (!$redis->exists("goods:$goods_id")) {
            return ['error'=>4004, 'message' => "所选商品不存在"];
        }

        $order_user_count = $redis->lLen('order_user');
        $order_users = $redis->lrange('order_user', 0, $order_user_count - 1);
        $user_id = Auth::id();
        if (in_array($user_id, $order_users)) {
            return ['error'=>4010, 'message' => '商品仅限购买一次'];
        }

        //抢锁
        $this->lock();

        //下单业务逻辑
        $res = $this->placeOrder($goods_id);

        $this->unlock();
        return $res;
    }
    
    private  function lock()
    {
        do {
            $timeout = 5000;
            $microtime = microtime(true) * 1000 + $timeout + 1;
            $is_lock = self::$redis->setnx('lock.count', $microtime);

            if (!$is_lock) {
                $previous_time  = self::$redis->get('lock.out');

                if ($previous_time > $microtime) {
                    usleep(5000);
                    continue;
                }

                $previous_time = self::$redis->getSet('lock.count', $microtime);
                if ($previous_time < self::$redis) {
                    break;
                }
            }
        } while (!$is_lock);

    }
    
    private  function unlock()
    {
        self::$redis->del('lock.count');
    }

    private function placeOrder($goods_id)
    {
        $redis = &self::$redis;
        $store = $redis->hget('goods:'.$goods_id, 'store');

        if (!($store > 0)) {
            $this->unlock();
            return ['error' => 4005, 'message' => '您的手速不够，下次可以更快点，继续加油！'];
        }

        //开启事物
        $redis->multi();
        $user_id = Auth::id();

        do {
            $rand = rand(1000, 9999);
            $out_trade_no = time();
            $out_trade_no .= $rand;
            $exists = $redis->exists('order:'.$out_trade_no);
        } while (!$exists);

        $order_data = [
            'out_trade_no'  =>  $out_trade_no,
            'user_id'       =>  $user_id,
            'num'           =>  1,
            'status'        =>  1,
            'goods_id'      =>  $goods_id,
            'create_at'     =>  time(),
            'update_at'     =>  time()
        ];
        $add_order = $redis->hMset('order:'.$out_trade_no, $order_data);

        if (!$add_order) {
            $redis->discard();
            $this->unlock();
            return ['error' => "5000", 'message' => '未知错误'];
        }
        $store --;
        $decr_store = $redis->hSet("goods:$goods_id", 'store', $store);
        if (!$decr_store) {
            $redis->discard();
            $this->unlock();
            return ['error' => "5000", 'message' => '未知错误'];
        }

        //将获得订单的用户加入列表
        $push_user = $redis->lPush('order_user', $user_id);

        if (!$push_user) {
            $redis->discard();
            $this->unlock();
            return ['error' => "5000", 'message' => '未知错误'];
        }

        $redis->exec();

        return ['error'=>2000, 'order'=>$order_data];
    }
}