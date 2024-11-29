<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Controllers\Article;

use App\Http\Api\Controllers\Article\ModelNotFoundException;
use App\Http\Controller;
use App\Models\article\Article;
use App\Models\article\Category;
use App\Models\User\Favorite;
use Xin\Hint\Facades\Hint;

/**
 * 文章接口
 */
class IndexController extends Controller
{

    /**
     * 文章列表
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $keywords = $this->request->keywordsSql();

        $order = 'publish_time';
        if (!empty($keywords)) {
            $order = 'view_count';
        }

        $search = $this->request->query();
        $data = Article::simple()
            ->search($search)
            ->with('category')
            ->where('status', 1)
            ->orderByDesc($order)
            ->paginate($this->request->paginate());

        return Hint::result($data);
    }

    /**
     * 获取文章详情信息
     *
     * @return \Illuminate\Http\Response
     */
    public function detail()
    {
        $id = $this->request->validId();
        $userId = $this->auth->getUserId(false);

        /** @var Article $info */
        $info = Article::with('category')->where('id', $id)->findOrFail();
        if ($info->status == 0 && (!$userId || $info->user_id != $userId)) {
            throw new ModelNotFoundException("文章不存在！", 'Article');
        }
        $info->inc('view_count')->update([]);
        $info->isAutoWriteTimestamp(false);
        $info->view_count += 1;
        $info->isAutoWriteTimestamp(true);

        // 其他扩展数据
        $info['is_favorite'] = false;
        $info['is_manager'] = false;

        // 如果用户登录
        if ($userId) {
            $info['is_favorite'] = Favorite::isFavorite(Article::MORPH_TYPE, $info->id, $userId);
            $info['is_manager'] = $info->user_id == $userId;
        }

        $info['good_categories'] = Category::getGoodList([], 'sort asc', 1, 4);

        return Hint::result($info);
    }

}
