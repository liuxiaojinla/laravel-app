<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\VCard\App\Models;

use App\Models\Model;
use App\Models\User\Browse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $alias
 * @property string $title
 * @property string $avatar
 * @property string $phone
 * @property string $wechat_account
 * @property string $wechat_qrcode
 * @property string $organization
 * @property int $status
 * @property int $view_count
 * @property int $collect_count
 */
class VCard extends Model
{

    use SoftDeletes;

    /**
     * 多态类型
     */
    const MORPH_TYPE = 'vcard';

    /**
     * @var string
     */
    protected $table = 'vcard';

    /**
     * @var string[]
     */
    protected $type = [
        'weapp_qrcode_id' => 'int',
        'lat'             => 'float',
        'lng'             => 'float',
    ];

    /**
     * @return string[]
     */
    public static function getSimpleFields()
    {
        return [
            'id', 'app_id', 'user_id', 'category_id', 'status',
            'name', 'alias', 'position', 'avatar', 'description', 'address', 'lng', 'lat',
            'phone', 'wechat_account', 'wechat_qrcode', 'organization',
            'collect_count', 'like_count', 'view_count',
        ];
    }

    /**
     * 定义浏览关联模型
     */
    public static function defineUserBrowseRelation()
    {
        Browse::define(self::MORPH_TYPE, function () {
            /** @var Browse $this */
            return $this->belongsTo(static::class, 'topic_id')
                ->bind([
                    'name', 'avatar',
                ]);
        });
    }

    /**
     * 微信小程序码关联对象
     *
     * @return BelongsTo
     */
    public function weappQrcode()
    {
        return $this->belongsTo(WechatWeappQrcode::class, 'weapp_qrcode_id');
    }

    /**
     * 生成小程序码
     *
     * @return bool
     */
    public function makeWeappQrcode()
    {
        $qrcodeId = $this->getRawOriginal('weapp_qrcode_id');

        if ($qrcodeId) {
            $qrcode = $this->getAttribute('weapp_qrcode');
        } else {
            $qrcode = WechatWeappQrcode::makeUnlimited('id=' . $this->getRawOriginal('id'), [
                //				'page' => 'pages/vcard/info',
            ], [
                'app_id'  => $this->getRawOriginal('app_id'),
                'user_id' => $this->getRawOriginal('user_id'),
            ]);

            $this->save(['weapp_qrcode_id' => $qrcode->id]);
        }

        return $qrcode;
    }

}
