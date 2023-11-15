<?php

namespace App\View\Composers;

use App\Repositories\UserRepository;
use Illuminate\View\View;

class ProfileComposer
{
    /**
     * 创建新的配置文件合成器。
     */
    public function __construct(
        protected UserRepository $users,
    )
    {
    }

    /**
     * 将数据绑定到视图。
     */
    public function compose(View $view): void
    {
        $view->with('count', $this->users->count());
    }
}
