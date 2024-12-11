<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Models\User;

use App\Models\Model;
use App\Models\Region;
use Xin\LaravelFortify\Validation\ValidationException;

/**
 * @property-read int id
 */
class Address extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var string
     */
    protected $table = 'user_addresses';

    /**
     * 优化关联ID
     * @param array $data
     * @return array
     * @throws ValidationException
     */
    public static function optimizeWithRelationId(array $data)
    {
        $provinceName = $data['province'];
        /** @var Region $province */
        $province = Region::query()->where('name', $provinceName)->first();
        if (empty($province)) {
            ValidationException::throwException("请联系管理员完善地址库！");
        }
        $data['province_id'] = $province->id;

        $cityName = $data['city'];
        /** @var Region $city */
        $city = Region::query()->where('name', $cityName)->where('pid', $data['province_id'])->first();
        if (empty($city)) {
            $city = Region::query()->create([
                'pid'         => $province->id,
                'shortname'   => '',
                'name'        => $cityName,
                'merger_name' => $province->name . ',' . $cityName,
                'level'       => 2,
            ]);
        }
        $data['city_id'] = $city->id;

        $districtName = $data['district'];
        /** @var Region $district */
        $district = Region::query()->where('name', $districtName)->where('pid', $data['city_id'])->first();
        if (empty($district)) {
            $district = Region::query()->create([
                'pid'         => $city->id,
                'shortname'   => '',
                'name'        => $districtName,
                'merger_name' => $province->name . ',' . $cityName . ',' . $districtName,
                'level'       => 3,
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
    public static function getUserDefaultSimpleInfo($userId)
    {
        return static::simple()->where([
            'user_id'    => $userId,
            'is_default' => 1,
        ])->orderByDesc('is_default')->first();
    }

}
