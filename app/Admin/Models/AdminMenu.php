<?php
namespace App\Admin\Models;

use App\Models\Model;

class AdminMenu extends Model
{
    /**
     * @inerhitDoc
     */
    public static function getAllowSetFields()
    {
        return array_merge(parent::getAllowSetFields(), [
            'icon' => '',
            'show' => 'number|in:0,1',
            'only_admin' => 'number|in:0,1',
            'only_dev' => 'number|in:0,1',
            'title' => 'length:2,24',
            'url' => 'max:255',
            'sort' => 'number|min:0',
        ]);
    }
}
