<?php

namespace plugins\order\admin\controller;

use app\admin\Controller;
use Carbon\Carbon;
use Plugins\Order\App\Models\Order;
use Plugins\Order\App\Models\OrderGoods;
use plugins\order\enum\PayStatus;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\db\Query;
use Xin\Hint\Facades\Hint;
use Xin\Support\Time;

class StatisticsController extends Controller
{
    /**
     * @return string
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        $this->assign([
            'basicFilterTime' => now()->toDateString() . ' - ' . now()->toDateString(),
            'chartFilterTime' => now()->subDays(7)->toDateString() . ' - ' . now()->toDateString(),
            'top10FilterTime' => now()->subDays(7)->toDateString() . ' - ' . now()->toDateString(),
        ]);

        return $this->fetch();
    }

    /**
     * 基础数据
     * @return Response
     * @throws DbException
     */
    public function basicData()
    {
        $search = $this->request->get();
        $orderTotalAmount = Order::query()->where('pay_status', PayStatus::SUCCESS)->search($search)->sum('total_amount');
        $orderTotalCount = Order::search($search)->count();
        $orderGoodsTotalCount = OrderGoods::search($search)->sum('goods_num');

        return Hint::result([
            'orderTotalAmount'     => $orderTotalAmount,
            'orderTotalCount'      => $orderTotalCount,
            'orderGoodsTotalCount' => $orderGoodsTotalCount,
        ]);
    }

    /**
     * 图表数据
     * @return Response
     * @throws DbException
     */
    public function chartData()
    {
        $time = $this->request->rangeTime('create_time');

        $everyDayOrderTotalAmountList = Order::field('sum(total_amount) as total_amount,FROM_UNIXTIME(create_time,"%m-%d") as day')
            ->where('pay_status', PayStatus::SUCCESS)
            ->whereTime('create_time', 'between', $time)
            ->group('day')
            ->select()->column('total_amount', 'day');

        $everyDayOrderTotalCountList = Order::field('count(*) as total_count,FROM_UNIXTIME(create_time,"%m-%d") as day')
            ->whereTime('create_time', 'between', $time)
            ->group('day')
            ->select()->column('total_count', 'day');

        $everyDayOrderGoodsTotalCountList = OrderGoods::field('sum(goods_num) as total_count,FROM_UNIXTIME(create_time,"%m-%d") as day')
            ->whereTime('create_time', 'between', $time)
            ->group('day')
            ->select()->column('total_count', 'day');

        $xAxisData = [];
        $everyDayOrderTotalAmountValues = [];
        $everyDayOrderTotalCountValues = [];
        $everyDayOrderGoodsTotalCountValues = [];
        $days = Time::daysUntilOfTimestamp($time[0], $time[1]);
        /** @var Carbon $day */
        foreach ($days as $day) {
            $date = $day->format('m-d');
            $xAxisData[] = $date;
            $everyDayOrderTotalAmountValues[] = $everyDayOrderTotalAmountList[$date] ?? 0;
            $everyDayOrderTotalCountValues[] = $everyDayOrderTotalCountList[$date] ?? 0;
            $everyDayOrderGoodsTotalCountValues[] = $everyDayOrderGoodsTotalCountList[$date] ?? 0;
        }

        return Hint::result([
            'xAxis'                            => $xAxisData,
            'everyDayOrderTotalAmountList'     => $everyDayOrderTotalAmountValues,
            'everyDayOrderTotalCountList'      => $everyDayOrderTotalCountValues,
            'everyDayOrderGoodsTotalCountList' => $everyDayOrderGoodsTotalCountValues,
        ]);
    }

    /**
     * 销售Top10数据
     * @return Response
     * @throws DbException
     */
    public function top10GoodsList()
    {
        $orderGoodsTop10List = OrderGoods::with([
            'goods' => function (Query $query) {
                $query->field([
                    'id', 'title', 'cover',
                ]);
            },
        ])->field('goods_id,sum(goods_num) as goods_total_count')
            ->group('goods_id')->order('goods_total_count desc')->limit(0, 10)->select();

        return Hint::result($orderGoodsTop10List);
    }
}
