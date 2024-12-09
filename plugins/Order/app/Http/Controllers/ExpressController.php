<?php


namespace Plugins\Order\App\Http\Controllers;

use App\Http\Controller;
use Illuminate\Http\Response;
use Plugins\Order\App\Models\Express;
use Plugins\Order\App\Models\Order;
use Xin\Hint\Facades\Hint;
use Xin\Support\Str;

class ExpressController extends Controller
{

    /**
     * 获取物流列表
     *
     * @return Response
     */
    public function index()
    {
        $data = Express::getList([
            'status' => 1,
        ], [
            'order' => 'sort asc',
        ]);

        return Hint::result($data);
    }

    /**
     * @return Response
     */
    public function tracks()
    {
        $orderId = $this->request->validId('order_id');
        $order = Order::query()->where('id', $orderId)->firstOrFail();

        $config = Config::get('express.channels.kuaidi100');
        if (empty($config) || empty($config['key']) || empty($config['customer'])) {
            return Hint::error("请联系管理员检查物流配置！");
        }

        //参数设置
        $key = $config['key'];                        //客户授权key
        $customer = $config['customer'];                   //查询公司编号
        $param = [
            'com'      => $order->express_name,             //快递公司编码
            'num'      => $order->express_no,     //快递单号
            // 'phone'    => '',                //手机号
            // 'from'     => '',                 //出发地城市
            // 'to'       => '',                   //目的地城市
            'resultv2' => '1',             //开启行政区域解析
        ];

        //请求参数
        $post_data = [];
        $post_data["customer"] = $customer;
        $post_data["param"] = json_encode($param);
        $sign = md5($post_data["param"] . $key . $post_data["customer"]);
        $post_data["sign"] = strtoupper($sign);
        $post_data = Str::buildUrlQuery($post_data);

        $url = 'http://poll.kuaidi100.com/poll/query.do';    //实时查询请求地址
        //发送post请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);
        if ($result && isset($result['data'])) {
            $data = $result['data'];
        } else {
            $data = [];
        }

        return Hint::result([
            'order' => $order,
            'data'  => $data,
        ]);
    }

}
