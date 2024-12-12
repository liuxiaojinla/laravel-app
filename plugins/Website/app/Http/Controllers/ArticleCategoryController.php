<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Website\App\Http\Controllers;

use App\Http\Controller;
use Illuminate\Http\Response;
use Plugins\Website\App\Models\Article;
use Plugins\Website\App\Models\ArticleCategory;

use Xin\Hint\Facades\Hint;
use Xin\Support\Arr;
use Xin\Support\Number;

class ArticleCategoryController extends Controller
{

    /**
     * 获取分类列表
     *
     * @return Response
     */
    public function index()
    {
        $isGood = $this->request->integer('is_good', 0);;
        $userId = $this->auth->id();

        $order = 'id DESC';
        if ($isGood) {
            $order = 'good_time DESC';
        }

        $search = $this->request->query();
        $data = ArticleCategory::simple()->search($search)->order($order)
            ->select()->each(function (ArticleCategory $item) use ($isGood, $userId) {
                $postCount = Article::query()->where([
                    'status'      => 1,
                    'category_id' => $item->id,
                ])->count();
                $item['article_count'] = Number::formatSimple($postCount);

                if (!$isGood) {
                    $item['follow_users'] = $item->getLastFollowUsers($userId);
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
    public function detail()
    {
        $id = $this->request->validId();
        $userId = $this->auth->id();

        /** @var ArticleCategory $info */
        $info = ArticleCategory::with([])->findOrFail($id);

        if ($info['status'] != 1) {
            return Hint::error("专题未发布或不存在！");
        }

        $info['follow_users'] = $info->getLastFollowUsers($userId);

        $info['article_list'] = Article::query()->where([
            'status'      => 1,
            'category_id' => $info->id,
        ])->orderByDesc('id')->paginate();

        $info['post_count'] = Article::query()->where('status', 1)->where('category_id', $info->id)->count();

        return Hint::result($info);
    }

    /**
     * 获取行业分类树形数据
     * @return Response
     */
    public function tree()
    {
        $data = ArticleCategory::select();
        $data = Arr::tree($data->toArray());

        return Hint::result($data);
    }

}