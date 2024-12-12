<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Website\App\Http\Controllers;

use App\Http\Controller;
use App\Models\User\Browse;
use App\Models\User\Favorite;
use App\Models\User\UserLike;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Plugins\Website\App\Models\Article;
use Plugins\Website\App\Models\ArticleCategory;
use Xin\Hint\Facades\Hint;


/**
 * 文章接口
 */
class ArticleController extends Controller
{

    /**
     * @var string
     */
    protected $favoriteType = 'website_article';

    /**
     * 文章列表
     *
     * @return Response
     */
    public function index()
    {
        $keywords = $this->request->keywordsSql();

        $order = 'publish_time desc';
        if (!empty($keywords)) {
            $order = 'view_count desc';
        }

        $search = $this->request->query();
        $data = Article::simple()->with('category')
            ->search($search)
            ->where('status', 1)
            ->order($order)
            ->paginate();

        return Hint::result($data);
    }

    /**
     * 获取文章详情信息
     *
     * @return Response
     */
    public function detail()
    {
        $id = $this->request->validId();
        $userId = $this->auth->id();

        /** @var Article $info */
        $info = Article::with(['category'])->where('id', $id)->firstOrFail();
        if ($info->status == 0) {
            throw new ModelNotFoundException("文章不存在！", Article::class);
        }

        // 当有user_id时判断信息
        if ($userId) {
            // 新增浏览数量
            $userBrowse = Browse::attach(Article::MORPH_TYPE, $info->id, $userId);
            if ($userBrowse->view_count == 1) {
                $info->increment('view_count');
                $info->view_count++;
            }

            // 判断是否被收藏
            $info['is_favorite'] = Favorite::isFavorite(Article::MORPH_TYPE, $info->id, $userId);

            // 判断是否被点赞
            $info['is_like'] = UserLike::isLike(Article::MORPH_TYPE, $info->id, $userId);
        }

        $info->increment('view_count');
        $info->view_count += 1;
        $info->append([
            'simply_view_count', 'simply_good_count',
            'simply_collect_count', 'simply_comment_count',
        ]);

        $info['good_categories'] = ArticleCategory::getGoodList([], 'sort asc', 1, 4);

        return Hint::result($info);
    }

}
