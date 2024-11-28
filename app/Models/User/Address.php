<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Models\User;

use App\Models\Model;
use Xin\LaravelFortify\Validation\ValidationException;

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
     */
    public static function optimizeWithRelationId(array $data)
    {
        $provinceName = $data['province'];
        /** @var Region $province */
        $province = Region::where('name', $provinceName)->find();
        if (empty($province)) {
            ValidationException::throwException("请联系管理员完善地址库！");
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
     */
    public static function getUserDefaultPlainInfo($userId)
    {
        return static::simple()->where([
            'user_id' => $userId,
            'is_default' => 1,
        ])->orderByDesc('is_default')->first();
    }

}
