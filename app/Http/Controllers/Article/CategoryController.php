<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Controllers\Article;

use App\Http\Api\Controllers\Article\AuthVerifyType;
use App\Http\Controller;
use App\Models\Article\Article;
use App\Models\Article\Category;
use App\Models\User\Favorite;
use Xin\Hint\Facades\Hint;

class CategoryController extends Controller
{

    /**
     * 获取分类列表
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $needStatisticData = $this->request->param('need_statistic_data/d', 0);
        $needFollowUsersData = $this->request->param('need_follow_users_data/d', 0);
        $isGood = $this->request->param('is_good/d', 0);

        $order = 'id DESC';
        if ($isGood) {
            $order = 'good_time DESC';
        }

        $data = Category::simple()
            ->when($isGood, [['good_time', '>', 0]])
            ->order($order)
            ->select()
            //            ->paginate($this->request->paginate())
            ->each(function (Category $item) use ($needStatisticData, $needFollowUsersData) {
                if ($needStatisticData) {
                    $item->append(['article_count', 'article_view_count']);
                }

                if (!$needFollowUsersData) {
                    $item['follow_users'] = $item->getLastFollowUsers(
                        $this->auth->getUser(null, null, AuthVerifyType::NOT)
                    );
                }

                return $item;
            });

        return Hint::result($data);
    }

    /**
     * 获取分类详细信息
     *
     * @return \Illuminate\Http\Response
     */
    public function detail()
    {
        $id = $this->request->validId();
        $userId = $this->auth->getUserId();

        /** @var Category $info */
        $info = Category::with([])->findOrFail($id);
        if ($info['status'] != 1) {
            return Hint::error("分类未发布或不存在！");
        }

        // 获取关注的用户
        $info['follow_users'] = $info->getLastFollowUsers(
            $this->auth->getUser(null, null, AuthVerifyType::NOT)
        );

        // 获取第一页数据文章数据
        $info['article_list'] = Article::where([
            'status'      => 1,
            'category_id' => $info->id,
        ])->order('id desc')
            ->paginate($this->request->paginate());

        // 获取次分类下的文章统计数据
        $info->append(['article_count', 'article_view_count']);

        // 初始数据
        $info['is_favorite'] = 0;

        // 用户已登录的情况
        if ($userId) {
            // 文章是否被收藏
            $info['is_favorite'] = (int)Favorite::isFavorite(Category::MORPH_TYPE, $info->id, $userId);
        }

        return Hint::result($info);
    }

}
