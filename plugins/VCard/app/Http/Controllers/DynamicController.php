<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\VCard\App\Http\Controllers;

use App\Exceptions\Error;
use App\Http\Controller;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Plugins\VCard\app\Http\Requests\DynamicRequest;
use Plugins\VCard\app\Models\VCard;
use Plugins\VCard\app\Models\VCardDynamic;
use Xin\Hint\Facades\Hint;

class DynamicController extends Controller
{

    /**
     * 获取动态列表
     *
     * @return Response
     */
    public function index()
    {
        $vcardId = $this->request->validId('vcard_id');

        $data = VCardDynamic::simple()->where([
            ['vcard_id', '=', $vcardId],
        ])
            ->orderByDesc('id')
            ->paginate();

        return Hint::result($data);
    }

    /**
     * 详情
     * @return Response
     */
    public function detail()
    {
        $id = $this->request->validId();

        /** @var VCardDynamic $info */
        $info = VCardDynamic::query()->where('id', $id)->firstOrFail();

        return Hint::result($info);
    }

    /**
     * 创建动态
     *
     * @return Response
     * @throws ValidationException
     */
    public function create(DynamicRequest $request)
    {
        $data = $request->validated();
        $userId = $this->auth->id();

        /** @var VCard $vcard */
        $vcard = VCard::query()->where([
            'user_id' => $userId,
        ]);
        if (empty($vcard)) {
            throw Error::validationException('请先创建名片！');
        }

        $data = array_merge([
            'images' => [],
        ], $data, [
            'user_id'  => $userId,
            'vcard_id' => $vcard->id,
            'status'   => 1,
        ]);

        $model = VCardDynamic::query()->create($data);

        return Hint::success('已发布！', null, $model);
    }

}
