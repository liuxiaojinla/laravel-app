<?php

namespace App\Http\Admin\Controllers\Statistics;

use App\Http\Admin\Controllers\Controller;
use App\Models\User;
use Xin\Hint\Facades\Hint;

class UserController extends Controller
{

    /**
     * 会员概览
     *
     * @return string
     */
    public function index()
    {
        $totalCount = User::query()->count();

        $todayCount = User::query()->whereDay('created_at', now())->count();
        $yesterdayCount = User::query()->whereDay('created_at', now()->subDay())->count();

        $weekCount = User::query()->where('created_at', '>=', now()->week())->count();
        $lastWeekCount = User::query()->whereBetween('created_at', [now()->subWeek(), now()])->count();

        $monthCount = User::query()->whereMonth('created_at', now())->count();
        $lastMonthCount = User::query()->whereBetween('created_at', [now()->subMonth(), now()])->count();

        $cityCounts = User::query()->selectRaw('province,count(id) as count')
            ->groupBy('province')->orderByDesc('count')->get()
            ->each(function ($item) use ($totalCount) {
                $item['rate'] = ($item['count'] == 0 || $totalCount == 0)
                    ? 0 : bcdiv($item['count'], $totalCount, 4) * 100;

                return $item;
            });
        $cityCountAvg = $cityCounts->count() ? (int)(array_sum($cityCounts->pluck('count')->toArray()) / $cityCounts->count()) : 0;

        return Hint::result([
            'totalCount' => $totalCount,

            'todayCount'     => $todayCount,
            'yesterdayCount' => $yesterdayCount,

            'weekCount'     => $weekCount,
            'lastWeekCount' => $lastWeekCount,

            'monthCount'     => $monthCount,
            'lastMonthCount' => $lastMonthCount,

            'cityCounts'     => $cityCounts,
            'cityCountAvg'   => $cityCountAvg,
            'cityCountsData' => $this->toChartCityData($cityCounts->all()),
        ]);
    }

    /**
     * 生成每个城市的数据
     *
     * @param array $array
     * @return array[]
     */
    protected function toChartCityData($array)
    {
        $default = [
            ['name' => '北京', 'value' => 0,],
            ['name' => '天津', 'value' => 0,],
            ['name' => '河北', 'value' => 0,],
            ['name' => '山西', 'value' => 0,],
            ['name' => '内蒙古', 'value' => 0,],
            ['name' => '辽宁', 'value' => 0,],
            ['name' => '吉林', 'value' => 0,],
            ['name' => '黑龙江', 'value' => 0,],
            ['name' => '上海', 'value' => 0,],
            ['name' => '江苏', 'value' => 0,],
            ['name' => '浙江', 'value' => 0,],
            ['name' => '安徽', 'value' => 0,],
            ['name' => '福建', 'value' => 0,],
            ['name' => '江西', 'value' => 0,],
            ['name' => '山东', 'value' => 0,],
            ['name' => '河南', 'value' => 0,],
            ['name' => '湖北', 'value' => 0,],
            ['name' => '湖南', 'value' => 0,],
            ['name' => '广东', 'value' => 0,],
            ['name' => '广西', 'value' => 0,],
            ['name' => '海南', 'value' => 0,],
            ['name' => '重庆', 'value' => 0,],
            ['name' => '四川', 'value' => 0,],
            ['name' => '贵州', 'value' => 0,],
            ['name' => '云南', 'value' => 0,],
            ['name' => '西藏', 'value' => 0,],
            ['name' => '陕西', 'value' => 0,],
            ['name' => '甘肃', 'value' => 0,],
            ['name' => '青海', 'value' => 0,],
            ['name' => '宁夏', 'value' => 0,],
            ['name' => '新疆', 'value' => 0,],
            ['name' => '香港', 'value' => 0,],
            ['name' => '澳门', 'value' => 0,],
            ['name' => '台湾', 'value' => 0,],
        ];

        foreach ($array as $item) {
            foreach ($default as &$item2) {
                if ($item['province'] == $item2['name']) {
                    $item2['value'] = $item['count'];
                }
            }
            unset($item2);
        }

        return $default;
    }
}
