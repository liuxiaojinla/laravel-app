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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Xin\Hint\Facades\Hint;

class CategoryController extends Controller
{

    /**
     * 获取分类列表
     *
     * @return Response
     */
    public function index()
    {
        $needStatisticData = $this->request->integer('need_statistic_data', 0);
        $needFollowUsersData = $this->request->integer('need_follow_users_data', 0);
        $isGood = $this->request->integer('is_good/d', 0);

        $data = Category::simple()
            ->when($isGood, function (Builder $query) use ($isGood) {
                $query->where('good_time', '>', 0);
            })
            ->where(function (Builder $query) use ($isGood) {
                $query->orderBy($isGood ? 'good_time' : 'id');
            })
            ->get()
            ->each(function (Category $item) use ($needStatisticData, $needFollowUsersData) {
                if ($needStatisticData) {
                    $item->append(['article_count', 'article_view_count']);
                }

                if (!$needFollowUsersData) {
                    $item['follow_users'] = $item->getLastFollowUsers(
                        $this->auth->user()
                    );
                }

                return $item;
            });

        return Hint::result($data);
    }

    /**
     * 获取分类详细信息
     *
     * @return Response
     */
    public function info()
    {
        $id = $this->request->validId();
        $userId = $this->auth->id();

        /** @var Category $info */
        $info = Category::with([])->findOrFail($id);
        if ($info['status'] != 1) {
            return Hint::error("分类未发布或不存在！");
        }

        // 获取关注的用户
        $info['follow_users'] = $info->getLastFollowUsers(
            $this->auth->user()
        );

        // 获取第一页数据文章数据
        $info['article_list'] = Article::simpleQuery()->where([
            'status'      => 1,
            'category_id' => $info->id,
        ])->orderByDesc('id')
            ->limit(15)
            ->get();

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
