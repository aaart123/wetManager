<?php
namespace Event\Controller;

use Base\Controller\BaseController;

/**
 * 关注/取关事件处理类
 */
class SubscribeController extends BaseController
{
    public function __construct()
    {
        parent:: __construct();
    }

    /**
     * 改变关注状态
     * @param str openid
     * @param int 关注与否
     * @return boolean
     */
    public function changeSubscribe($openId, $subscribe = 1)
    {
        $data = [
            'openid' => $openId,
            'subscribe' => $subscribe
        ];
        return $this->editOpenidData($data);
    }

}