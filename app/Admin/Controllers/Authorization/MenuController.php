<?php

namespace App\Admin\Controllers\Authorization;

use App\Admin\Controllers\Controller;
use Xin\Hint\Facades\Hint;
use Xin\Menu\Contracts\Factory as MenuFactory;

class MenuController extends Controller
{
    /**
     * @param MenuFactory $factory
     * @return mixed
     */
    public function index(MenuFactory $factory)
    {
        [$menus, $breads] = $factory->menu()->generate('');

        return Hint::result($menus);
    }
}
