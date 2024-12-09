<?php

namespace Plugins\Order\App\Admin\Controllers;

use App\Admin\Controller;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Plugins\Order\App\Enums\PayStatus;
use Plugins\Order\App\Models\Order;
use Plugins\Order\App\Models\OrderGoods;
use Xin\Hint\Facades\Hint;
use Xin\Support\Time;

class StatisticsController extends Controller
{
    /**
     * @return string
     */
    public function index()
    {
        return Hint::result([
            'basicFilterTime' => now()->toDateString() . ' - ' . now()->toDateString(),
            'chartFilterTime' => now()->subDays(7)->toDateString() . ' - ' . now()->toDateString(),
            'top10FilterTime' => now()->subDays(7)->toDateString() . ' - ' . now()->toDateString(),
        ]);
    }

    /**
     * 基础数据
     * @return Response
     */
    public function basicData()
    {
        $search = $this->request->query();
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
     */
    public function chartData()
    {
        $time = $this->request->rangeTime('create_time');

        $everyDayOrderTotalAmountList = Order::query()->selectRaw('sum(total_amount) as total_amount,FROM_UNIXTIME(create_time,"%m-%d") as day')
            ->where('pay_status', PayStatus::SUCCESS)
            ->whereTime('create_time', 'between', $time)
            ->groupBy('day')
            ->select()->column('total_amount', 'day');

        $everyDayOrderTotalCountList = Order::query()->selectRaw('count(*) as total_count,FROM_UNIXTIME(create_time,"%m-%d") as day')
            ->whereTime('create_time', 'between', $time)
            ->groupBy('day')
            ->select()->column('total_count', 'day');

        $everyDayOrderGoodsTotalCountList = OrderGoods::query()->selectRaw('sum(goods_num) as total_count,FROM_UNIXTIME(create_time,"%m-%d") as day')
            ->whereTime('create_time', 'between', $time)
            ->groupBy('day')
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
     */
    public function top10GoodsList()
    {
        $orderGoodsTop10List = OrderGoods::with([
            'goods' => function (Builder $query) {
                $query->select([
                    'id', 'title', 'cover',
                ]);
            },
        ])
            ->selectRaw('goods_id,sum(goods_num) as goods_total_count')
            ->groupBy('goods_id')
            ->orderByDesc('goods_total_count')
            ->limit(10)->get();

        return Hint::result($orderGoodsTop10List);
    }
}
