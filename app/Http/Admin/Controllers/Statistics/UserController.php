<?php

namespace app\admin\controller\statistics;

use app\admin\Controller;
use app\common\model\User;

class UserController extends Controller
{

    /**
     * 会员概览
     *
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $totalCount = User::count();

        $todayCount = User::whereDay('create_time')->count();
        $yesterdayCount = User::whereDay('create_time', 'yesterday')->count();

        $weekCount = User::whereWeek('create_time')->count();
        $lastWeekCount = User::whereWeek('create_time', 'last week')->count();

        $monthCount = User::whereMonth('create_time')->count();
        $lastMonthCount = User::whereMonth('create_time', 'last month')->count();

        $cityCounts = User::field('province,count(id) as count')
            ->group('province')->order('count desc')->select()
            ->each(function ($item) use ($totalCount) {
                $item['rate'] = ($item['count'] == 0 || $totalCount == 0)
                    ? 0 : bcdiv($item['count'], $totalCount, 4) * 100;

                return $item;
            });
        $cityCountAvg = $cityCounts->count() ? (int)(array_sum($cityCounts->column('count')) / $cityCounts->count()) : 0;

        $this->assign([
            'totalCount' => $totalCount,

            'todayCount' => $todayCount,
            'yesterdayCount' => $yesterdayCount,

            'weekCount' => $weekCount,
            'lastWeekCount' => $lastWeekCount,

            'monthCount' => $monthCount,
            'lastMonthCount' => $lastMonthCount,

            'cityCounts' => $cityCounts,
            'cityCountAvg' => $cityCountAvg,
            'cityCountsData' => $this->toChartCityData($cityCounts->all()),
        ]);

        return $this->fetch();
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