<?php

namespace App\Admin\Models;

use App\Exceptions\Error;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Xin\LaravelFortify\Model\Modelable;
use Xin\LaravelFortify\Validation\ValidationException;

/**
 * @property-read int $id
 * @property-read string $username
 * @property int $status
 * @property string $status_text
 * @property bool $is_admin
 * @property Collection $roles
 * @property-read int $login_count
 * @property-read string $login_ip
 * @property-read string $login_time
 * @property-read string $password
 */
class Admin extends Authenticatable
{
    use Notifiable, Modelable;

    /**
     * @var mixed
     */
    protected static $STATUS_TEXT_MAP = [
        '0' => '禁用',
        '1' => '启用',
    ];
    /**
     * @var string[]
     */
    protected $fillable = [
        'username',
    ];
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
    protected $hidden = [
        'password',
    ];
    /**
     * @var array
     */
    protected $allMenuIds = null;

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
     * 超级管理员ID
     *
     * @return int
     */
    public static function adminId()
    {
        return config('auth.guards.admin.administrator_id');
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
     * 是否是超级管理员
     *
     * @return bool
     */
    public function isAdministrator()
    {
        return $this->getAttribute('is_admin');
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
     * 登录IP地址访问器修改器
     *
     * @return Attribute
     */
    protected function loginIp(): Attribute
    {
        return Attribute::make(
            function ($ip) {
                return long2ip($ip);
            },
            function ($ip) {
                return ip2long($ip);
            }
        );
    }

    /**
     * 创建IP地址访问器修改器
     *
     * @return Attribute
     */
    protected function createIp(): Attribute
    {
        return Attribute::make(
            function ($ip) {
                return long2ip($ip);
            },
            function ($ip) {
                return ip2long($ip);
            }
        );
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
    protected function getIsAdminAttribute()
    {
        $id = $this->getAttribute('id');

        return self::checkAdmin($id);
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

}
