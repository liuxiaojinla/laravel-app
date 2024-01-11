<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\common\model\user;

use app\common\model\Model;
use app\common\model\Region;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\ValidateException;

/**
 * @property-read int id
 */
class Address extends Model
{

    /**
     * @var string
     */
    protected $name = 'user_address';

    /**
     * 优化关联ID
     * @param array $data
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function optimizeWithRelationId(array $data)
    {
        $provinceName = $data['province'];
        /** @var Region $province */
        $province = Region::where('name', $provinceName)->find();
        if (empty($province)) {
            throw new ValidateException("请联系管理员完善地址库！");
        }
        $data['province_id'] = $province->id;

        $cityName = $data['city'];
        $city = Region::where('name', $cityName)->where('pid', $data['province_id'])->find();
        if (empty($city)) {
            $city = Region::create([
                'pid' => $province->id,
                'shortname' => '',
                'name' => $cityName,
                'merger_name' => $province->name . ',' . $cityName,
                'level' => 2,
            ]);
        }
        $data['city_id'] = $city->id;

        $districtName = $data['district'];
        /** @var Region $district */
        $district = Region::where('name', $districtName)->where('pid', $data['city_id'])->find();
        if (empty($district)) {
            $district = Region::create([
                'pid' => $city->id,
                'shortname' => '',
                'name' => $districtName,
                'merger_name' => $province->name . ',' . $cityName . ',' . $districtName,
                'level' => 3,
            ]);
        }
        $data['district_id'] = $district->id;

        return $data;
    }

    /**
     * @inheritDoc
     */
    public static function getSimpleFields()
    {
        return [
            "name", "phone", "is_default",
            "province", "city", "district", "address",
        ];
    }

    /**
     * 获取当前用户默认简单信息收货地址
     *
     * @param int $userId
     * @return static
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getUserDefaultPlainInfo($userId)
    {
        return static::simple()->where([
            'user_id' => $userId,
            'is_default' => 1,
        ])->order('is_default DESC')->find();
    }

}
