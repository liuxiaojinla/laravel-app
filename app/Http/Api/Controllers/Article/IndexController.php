<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Api\Controllers\Article;

use App\Models\article\Article;
use App\Models\article\Category;
use App\Models\User\Favorite;
use think\db\exception\ModelNotFoundException;
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
     * @throws \think\db\exception\DbException
     */
    public function index()
    {
        $keywords = $this->request->keywordsSql();

        $order = 'publish_time desc';
        if (!empty($keywords)) {
            $order = 'view_count desc';
        }

        $search = $this->request->get();
        $data = Article::with('category')
            ->simple()
            ->search($search)
            ->where('status', 1)
            ->order($order)
            ->paginate($this->request->paginate());

        return Hint::result($data);
    }

    /**
     * 获取文章详情信息
     *
     * @return \Illuminate\Http\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
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
