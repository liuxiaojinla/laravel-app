<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace plugins\website\listener;

use app\Request;
use Plugins\Website\App\Models\Website;
use Plugins\Website\App\Models\WebsiteArticle;
use Plugins\Website\App\Models\WebsiteCase;
use Plugins\Website\App\Models\WebsiteProduct;
use Xin\Support\Fluent;

class ApiIndex
{

    /**
     * @var Request
     */
    private $request;

    /**
     * @param Fluent $data
     * @return void
     */
    public function handle(Fluent $data)
    {
        $this->request = app(Request::class);

        // 菜单导航
        $data['nav_list'] = [
            [
                'icon' => 'http://scrm.res.ixiaochengxu.cc/67/20201225/e50036f3d7d8bc97c4aa1e489340fe84.png',
                'text' => '案例',
                'type' => 'page',
                'url' => '/pages/website/case/index',
            ],
            [
                'icon' => 'http://scrm.res.ixiaochengxu.cc/67/20201225/3f4ffb0766d14c94b4c48500efcb4130.png',
                'text' => '产品',
                'type' => 'page',
                'url' => '/pages/website/product/index',
            ],
            [
                'icon' => 'http://scrm.res.ixiaochengxu.cc/67/20201225/8820ea306fd81e8af177105c3243306c.png',
                'text' => '动态',
                'type' => 'page',
                'url' => '/pages/website/article/index',
            ],
            [
                'icon' => 'http://scrm.res.ixiaochengxu.cc/67/20201225/b813772df5fec47bfb8ef590c8a68a31.png',
                'text' => '关于我们',
                'type' => 'page',
                'url' => '/pages/website/about',
            ],
        ];

        // 获取文章
        $data['article_list'] = WebsiteArticle::query()->where([
            'app_id' => $this->request->appId(),
            'status' => 1,
        ])->order([
            'top_time' => 'desc',
            'id' => 'desc',
        ])->limit(0, 5)->get();

        // 获取产品
        $data['product_list'] = WebsiteProduct::getList([
            'app_id' => $this->request->appId(),
            'status' => 1,
        ])->order([
            'top_time' => 'desc',
            'id' => 'desc',
        ])->limit(0, 5)->get();

        // 获取案例
        $data['case_list'] = WebsiteCase::getList([
            'app_id' => $this->request->appId(),
            'status' => 1,
        ])->order([
            'top_time' => 'desc',
            'id' => 'desc',
        ])->limit(0, 5)->get();

        // 悬浮按钮
        $data['floating_button_list'] = [
            [
                'icon' => '',
                'type' => 'contact',
            ],
            [
                'icon' => '',
                'type' => 'phone',
                'phone' => '13653975075',
            ],
        ];

        // 企业信息
        $data['website_info'] = Website::query()->where([
            'app_id' => $this->request->appId(),
        ])->first();

        // 留言表单
        $data['leaving_msg_form'] = [
            'items' => [
                [
                    'title' => '您的姓名',
                    'name' => 'name',
                    'type' => 'string',
                ],
                [
                    'title' => '您的手机号',
                    'name' => 'phone',
                    'type' => 'number',
                ],
                [
                    'title' => '您要了解的内容',
                    'name' => 'message',
                    'type' => 'text',
                ],
            ],
        ];
    }
}
