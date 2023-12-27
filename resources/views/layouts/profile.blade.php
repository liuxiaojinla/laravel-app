<?php
$menus = [
    [
        'title' => '用户',
        'icon' => '',
        'items' => [
            [
                'title' => '个人中心',
                'icon' => 'M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605',
                'url' => '/profile',
            ],
            [
                'title' => '修改资料',
                'icon' => '',
                'url' => '/profile/update',
            ],
        ],
    ],
    [
        'title' => '安全',
        'icon' => '',
        'items' => [
            [
                'title' => '修改密码',
                'icon' => '',
                'url' => '/password',
            ],
            [
                'title' => '删除账号',
                'icon' => '',
                'url' => '/profile/delete',
            ],
        ],
    ],
    [
        'title' => '账单',
        'icon' => '',
        'items' => [
            [
                'title' => '我的账单',
                'icon' => '',
                'url' => '',
            ],
        ],
    ],
];
?>
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex">
            <div class="pr-1.5">
                {{--                    dark:bg-gray-900 dark:border-gray-700--}}
                <aside
                    class="hidden md:flex flex-col w-64 px-5 py-6 bg-white shadow-sm sm:rounded-lg overflow-y-auto border-r rtl:border-r-0 rtl:border-l">
                    {{--                    <a href="#">--}}
                    {{--                        <img class="w-auto h-7" src="https://merakiui.com/images/logo.svg" alt="">--}}
                    {{--                    </a>--}}
                    <div class="h-7">
                        <h3 class="font-semibold text-xl text-gray-800 leading-tight">{{$asideTitle??''}}</h3>
                    </div>

                    <div class="flex flex-col justify-between flex-1 mt-6">
                        <nav class="-mx-3 space-y-6 ">
                            @foreach($menus as $group)
                                <div class="space-y-3 ">
                                    <label class="px-3 text-xs text-gray-500 uppercase">{{$group['title']}}</label>
                                    @foreach($group['items'] as $menu)
                                        <a class="flex items-center px-3 py-3 transition-colors duration-300 transform rounded-lg {{request()->is(ltrim($menu['url'],'/'))?'text-white bg-indigo-600':'text-gray-600 hover:bg-gray-100 active:bg-gray-100 hover:text-gray-900'}}"
                                           href="{{$menu['url']}}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                 stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="{{$menu['icon']}}"/>
                                            </svg>

                                            <span class="mx-2 text-sm font-medium">{{$menu['title']}}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endforeach
                        </nav>
                    </div>
                </aside>
            </div>
            <div class="flex-auto pl-1.5">
                <div class="p-4 sm:p-8 bg-white shadow-sm sm:rounded-lg">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
