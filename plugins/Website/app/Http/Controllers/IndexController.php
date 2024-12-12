<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Website\App\Http\Controllers;

use App\Http\Controller;
use Illuminate\Http\Response;
use Plugins\Website\App\Models\About;
use Plugins\Website\App\Models\LeaveMessage;
use Xin\Hint\Facades\Hint;

class IndexController extends Controller
{

    /**
     * 关于我们
     *
     * @return Response
     */
    public function about()
    {
        $info = About::query()->where([])->first();

        return Hint::result($info);
    }

    /**
     * 留言
     *
     * @return Response
     */
    public function submitLeavingMsg()
    {
        $forms = $this->request->param('data/a');

        $data['content'] = json_encode($forms, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $data['ip'] = $this->request->ip();
        $data['user_agent'] = $this->request->server('HTTP_USER_AGENT');
        $data['referer'] = $this->request->server('HTTP_REFERER');

        LeaveMessage::query()->create($data);

        return Hint::success("已留言！");
    }

}
