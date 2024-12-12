<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Website\App\Admin\Controllers;

use App\Admin\Controller;
use Illuminate\Http\Response;
use Plugins\Website\app\Http\Requests\WebsiteRequest;
use Plugins\Website\App\Models\WebsiteAbout;
use Plugins\Website\App\Models\Website;
use Xin\Hint\Facades\Hint;

class SettingController extends Controller
{

    /**
     * 官网设置
     *
     * @return Response
     */
    public function index()
    {
        $id = $this->request->validId();
        $info = Website::query()->where('id', $id)->first();

        if ($this->request->isPost()) {
            $data = $this->request->all();
            if (isset($data['region'])) {
                $region = (array)json_decode($data['region'], true);
                unset($data['region']);
                $data = array_merge($region, $data);
            }

            if (isset($data['location'])) {
                $location = explode(',', $data['location'], 2);
                unset($data['location']);
                $data['lng'] = $location[0] ?? '';
                $data['lat'] = $location[1] ?? '';
            }

            $validate = new WebsiteRequest();
            $validate->failException(true)->check($data);

            if (!$info) {
                $info = new Website();
            }

            $info->allowField([])->save($data);

            return Hint::success('已更新！');
        }



        return Hint::result($info);
    }

    /**
     * 关于我们
     *
     * @return Response
     */
    public function about()
    {
        $id = $this->request->validId();
        $info = WebsiteAbout::query()->where('id', $id)->first();

        if ($this->request->isPost()) {
            $content = $this->request->param('content');

            if (!$info) {
                $info = new WebsiteAbout();
            }

            $info->allowField([])->save([
                'content' => $content,
            ]);

            return Hint::success('已更新！');
        }


        return Hint::result($info);
    }

}
