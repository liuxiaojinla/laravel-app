<?php

namespace App\Supports;

use AlibabaCloud\Client\AlibabaCloud;

class ThirdParty
{
    /**
     * 阿里云客户端
     * @param string $regionId
     * @return \AlibabaCloud\Client\Clients\AccessKeyClient
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public static function alibabaCloud($regionId = "cn-hangzhou")
    {
        $ossConfig = get_oss_config();

        return AlibabaCloud::accessKeyClient(
            $ossConfig['accessKeyId'],
            $ossConfig['accessKeySecret'])
            ->regionId($regionId)
            ->asDefaultClient();
    }

    /**
     * @return \AlibabaCloud\NlsFiletrans\V20180817\NlsFiletransApiResolver
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public static function alibabaCloudNlsFiletrans()
    {
        self::alibabaCloud('cn-shanghai');

        return AlibabaCloud::nlsFiletrans()
            ->v20180817();
    }


}
