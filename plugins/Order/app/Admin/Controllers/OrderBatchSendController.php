<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

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
