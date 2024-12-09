<?php


namespace Plugins\Order\App\Admin\Controllers;

use app\admin\Controller;

class OrderBatchSendController extends Controller
{

    /**
     * 订单批量发货
     */
    public function index()
    {
        return $this->fetch('admin@layout');
    }

}
