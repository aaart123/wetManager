<?php
namespace  Base\Controller;

use Base\Controller\BaseController;

class AuthController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getCode()
    {
        $url = "https://open.weixin.qq.com/connect/qrconnect?appid={APPID}&
        redirect_uri={REDIRECT_URI}&response_type=code&scope=SCOPE&state=STATE#wechat_redirect";
    }
}
