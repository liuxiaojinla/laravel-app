<?php


namespace Plugins\Order\App\Models;

use App\Models\Model;
use Xin\Http\Client;

class Express extends Model
{

    /**
     * @return string[]
     */
    public static function getSimpleFields()
    {
        return [
            'id', 'title', 'code', 'logo',
        ];
    }

    /**
     * 获取物流信息
     *
     * @param string $type
     * @param string $expressNo
     * @return array
     */
    public static function getTacks($type, $expressNo)
    {
        $rand = str_shuffle(str_repeat('1234567890', 2));
        $rand = substr($rand, 0, 17);
        $url = "https://www.kuaidi100.com/query?type={$type}&postid={$expressNo}&temp=0.{$rand}";
        $result = Client::get($url);

        return $result->toArray();
    }

}
