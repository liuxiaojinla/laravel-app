<?php

namespace App\Admin\Models;

use App\Exceptions\Error;
use App\Models\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Xin\LaravelFortify\Validation\ValidationException;

/**
 * @property-read int $id
 * @property-read string $username
 * @property int $status
 * @property string $status_text
 * @property bool $is_admin
 * @property Collection $roles
 * @property-read int $login_count
 * @property string $login_ip
 */
class Admin extends Model
{

    /**
     * @var string[]
     */
    protected $type = [
        'login_count' => 'int',
        'login_time'  => 'timestamp',
    ];

    /**
     * @var array
     */
    protected $allMenuIds = null;

    /**
     * @var mixed
     */
    protected static $STATUS_TEXT_MAP = [
        '0' => '禁用',
        '1' => '启用',
    ];

    /**
     * 检查是否更新了超级管理员
     * @param array $ids
     * @return void
     * @throws ValidationException
     */
    public static function checkIsUpdateAdmin($ids)
    {
        $adminId = array_search(self::adminId(), $ids, true);
        if (!empty($adminId)) {
            throw Error::validationException("不允许删除超级管理员");
        }
    }

    /**
     * 关联角色模型
     *
     * @return BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(AdminRole::class, AdminAccess::class, 'role_id', 'target_id')
            ->wherePivot('type', 'admin');
    }

    /**
     * 获取登录IP地址
     *
     * @param int $ip
     * @return string
     */
    protected function getLoginIpAttribute($ip)
    {
        return long2ip($ip);
    }

    /**
     * 设置登录IP地址
     *
     * @param string $ip
     * @return false|int
     */
    protected function setLoginIpAttribute($ip)
    {
        return ip2long($ip);
    }

    /**
     * @return mixed|string
     */
    protected function getStatusTextAttribute()
    {
        $status = $this->getAttribute('status');

        return self::$STATUS_TEXT_MAP[$status] ?? '未知';
    }

    /**
     * 是否是超级管理员
     *
     * @return bool
     */
    protected function getIsAdminAttr()
    {
        $id = $this->getAttribute('id');

        return self::checkAdmin($id);
    }

    /**
     * 是否是超级管理员
     *
     * @return bool
     */
    public function isAdministrator()
    {
        return $this->getAttribute('is_admin');
    }

    /**
     * 超级管理员ID
     *
     * @return int
     */
    public static function adminId()
    {
        return config('auth.guards.admin.administrator_id');
    }

    /**
     * 是否是超级管理员
     *
     * @param int $id
     * @return bool
     */
    public static function checkAdmin($id)
    {
        return self::adminId() == $id;
    }

    /**
     * 获取所有菜单ID
     *
     * @return array
     */
    public function getAllMenuIds()
    {
        if ($this->allMenuIds !== null) {
            return $this->allMenuIds;
        }

        if ($this->roles->isEmpty()) {
            return [];
        }

        $result = AdminAccess::query()->where([
            'type' => 'menu',
        ])
            ->whereIn('role_id', $this->roles->pluck('id'))
            ->pluck('target_id')->toArray();

        $result = array_unique($result);

        return $this->allMenuIds = array_values($result);
    }

    /**
     * 清空当前内存菜单ID
     */
    public function flushAllMenuIds()
    {
        $this->allMenuIds = null;
    }

    /**
     * @return array
     */
    public function __serialize()
    {
        return [
            'exists' => $this->isExists(),
            'origin' => $this->getOrigin(),
        ];
    }

}
