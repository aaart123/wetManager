<?php
namespace Event\Controller;

use Base\Controller\CommonController;

/**
 * 关注/取关事件处理类
 */
class SubscribeController extends CommonController
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

    public function parseQRcode($param)
    {
        if ($param['ToUserName']=='gh_243fe4c4141f' && $param['EventKey']=='newMediaWap' || $param['EventKey']=='qrscene_newMediaWap') {
            $title = "新媒圈, 邀您内测";
            $description = "高校新媒体人的圈子社区, 分享运营者的智慧";
            $url = "http://www.koudaidaxue.com/index.php/Wap/Index/index?aid=abcd";
            $msg = sprintf($this->msgTemplate['news'], $param['FromUserName'], 
                    $param['ToUserName'], time(), 1, $title, $description, '', $url, 0);
            return $msg;
        }
    }

}