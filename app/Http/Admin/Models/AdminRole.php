<?php
namespace App\Http\Admin\Models;

use app\common\model\Model;

/**
 * 分类模型
 *
 * @property-read  int id
 * @property string title
 * @property string description
 */
class AdminRole extends Model
{

    /**
     * @var int
     */
    protected $defaultSoftDelete = 0;

    /**
     * 关联菜单模型
     *
     * @return \think\model\relation\BelongsToMany
     */
    public function menus()
    {
        return $this->belongsToMany(AdminMenu::class, AdminAccess::class, 'target_id', 'role_id')
            ->wherePivot('type', 'menu');
    }

    /**
     * 关联管理员账号
     *
     * @return \think\model\relation\BelongsToMany
     */
    public function admins()
    {
        return $this->belongsToMany(Admin::class, AdminAccess::class, 'target_id', 'role_id')
            ->wherePivot('type', 'admin');
    }

}
