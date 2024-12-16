<?php

namespace App\Admin\Requests;

use Xin\LaravelFortify\Request\FormRequest;

class AdminMenuRequest extends FormRequest
{

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'title' => '标题',
        'menu' => '所属菜单',
        'icon' => '图标',
        'pid' => '父级菜单',
        'sort' => '排序',
        'url' => 'URL',
        'show' => '是否显示',
        'only_admin' => '管理员可见',
        'only_dev' => '开发模式可见',
        'status' => '状态',
        'link' => '菜单模式',
        'router' => '前端路由',
        'view' => '前端页面',
    ];

    /**
     * 验证规则
     *
     * @return array[]
     */
    public function rules()
    {
        return [
            'title' => ['required', 'max:24'],
            "menu" => ['string', 'max:50'],
            "icon" => ['string', 'max:50'],
            "pid" => ['integer'],
            "sort" => ['integer'],
            "url" => ['string', 'max:255'],
            "show" => ['integer', 'in:0,1'],
            "only_admin" => ['integer', 'in:0,1'],
            "only_dev" => ['integer', 'in:0,1'],
            "status" => ['integer', 'in:0,1,2,3'],
            "link" => ['integer', 'in:0,1'],
            "system" => ['integer', 'in:0,1'],
            //            "app"        => ['string', 'max:50'],
            "router" => ['string', 'max:255'],
            "view" => ['string', 'max:255'],
        ];
    }

}
