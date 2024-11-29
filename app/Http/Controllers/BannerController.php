<?php

namespace App\Http\Controllers;

use App\Http\Controller;
use App\Models\Advertisement\Item as AdvertisementItem;
use App\Models\Advertisement\Position as AdvertisementPosition;
use Xin\Hint\Facades\Hint;

class BannerController extends Controller
{
    /**
     * 获取广告位列表
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $name = trim($this->request->input('name', ''));
        $name = $name ?: 'mobile_home';
        $advertisementId = AdvertisementPosition::query()->where('name', $name)->value('id') ?: 0;
        $data = AdvertisementItem::simple()->where([
            ['status', '=', 1,],
            ['begin_time', '<', $this->request->time(),],
            ['end_time', '>', $this->request->time(),],
            ['advertisement_id', '=', $advertisementId],
        ])->oldest('sort')->latest('id')->get();

        return Hint::result($data);
    }
}
