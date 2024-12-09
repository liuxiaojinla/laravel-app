<?php

namespace App\Http\Controllers;

use App\Exceptions\Error;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Xin\Hint\Facades\Hint;
use Xin\Uploader\Contracts\Factory as Uploader;

class UploadController extends Controller
{
    /**
     * @var Uploader
     */
    protected $uploader;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param Uploader $uploader
     * @param Request $request
     */
    public function __construct(Uploader $uploader, Request $request)
    {
        $this->uploader = $uploader;
        $this->request = $request;
    }

    /**
     * 本地上传
     * @return Response
     * @throws ValidationException
     */
    public function upload()
    {
        $scene = $this->request->input('scene', 'image');
        $targetFile = $this->request->file('file');
        if (empty($targetFile)){
            throw Error::validationException("请上传文件。");
        }

        $result = $this->uploader->file($scene, $targetFile->getFileInfo());

        return Hint::result($result);
    }

    /**
     * 获取上传令牌
     * @return Response
     */
    public function token()
    {
        $scene = $this->request->input('scene', 'image');

        $result = $this->uploader->token($scene);

        return Hint::result($result);
    }

    /**
     * 回调处理
     * @return Response
     */
    public function notify()
    {
        $scene = $this->request->string('scene');

        return $this->uploader->callbackProcessing(function () {

        });
    }
}
