<?php
namespace  Base\Controller;

use Base\Controller\BaseController;
use Base\Controller\OauthApiController;

/**
 * 业务核心类
 */

class CommonController extends BaseController
{
    protected $publicKeyModel;
    protected $eventModel;
    protected $appModel;
    protected $textModel;
    protected $openidModel;
    protected $wxBizMsgCrypt; // 加密解密类

    public function __construct()
    {
        parent::__construct();
        $this->publicKeyModel = D('Base/PublicKey');
        $this->appModel = D('Base/App');
        $this->textModel = D('Base/text');
        $this->wxBizMsgCrypt = new \Common\Common\wxBizMsgCrypt(C('TOKEN'), C('ENCODINGAESKEY'), C('COMPONENT_APPID'));
    }

    public function getToken($public_id)
    {
        $oauth = new OauthApiController();
        $appid = $oauth->getAuthorizerAppid($public_id);
        $token = $oauth->getAuths($appid);
        echo $token;
    }
}