<?php
namespace App\Http\Admin\Models;

use app\common\model\Model;
use think\exception\ValidateException;

/**
 * @property-read int $id
 * @property-read string $username
 * @property int $status
 * @property string $status_text
 * @property bool $is_admin
 * @property \think\Collection $roles
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
        'login_time' => 'timestamp',
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
     */
    public static function checkIsUpdateAdmin($ids)
    {
        $adminId = array_search(self::adminId(), $ids, true);
        if (!empty($adminId)) {
            throw new ValidateException("不允许删除超级管理员");
        }
    }

    /**
     * 关联角色模型
     *
     * @return \think\model\relation\BelongsToMany
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
    protected function getLoginIpAttr($ip)
    {
        return long2ip($ip);
    }

    /**
     * 设置登录IP地址
     *
     * @param string $ip
     * @return false|int
     */
    protected function setLoginIpAttr($ip)
    {
        return ip2long($ip);
    }

    /**
     * @return mixed|string
     */
    protected function getStatusTextAttr()
    {
        $status = $this->getData('status');

        return self::$STATUS_TEXT_MAP[$status] ?? '未知';
    }

    /**
     * 是否是超级管理员
     *
     * @return bool
     */
    protected function getIsAdminAttr()
    {
        $id = $this->getData('id');

        return self::checkAdmin($id);
    }

    /**
     * 是否是超级管理员
     *
     * @return bool
     */
    public function isAdministrator()
    {
        return $this->getAttr('is_admin');
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

        $result = AdminAccess::where([
            'type' => 'menu',
        ])->whereIn('role_id', $this->roles->column('id'))
            ->column('target_id');

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
