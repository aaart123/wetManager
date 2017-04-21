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

    private function xinMei($param)
    {
        if (substr($param['EventKey'],0,2)=='10' || substr($param['EventKey'],0,10)=='qrscene_10') {
            if (substr($param['EventKey'],0,2)=='10') {
                $action_id = substr($param['EventKey'], 2);
            } else {
                $action_id = substr($param['EventKey'], 10);
            }
            $actionModel = D('Base/Action');
            $where['action_id'] = $action_id;
            $save['openid'] = $param['FromUserName'];
            $save['state'] = 1;
            $actionModel->editData($where, $save);
            $title = "欢迎使用口袋大学";
            $description = "高校新媒体人的圈子社区, 分享运营者的智慧";
            $url = "http://www.koudaidaxue.com/index.php/Wap/Index/index?aid=abcd";
            $msg = sprintf($this->msgTemplate['news'], $param['FromUserName'], 
                    $param['ToUserName'], time(), 1, $title, $description, '', $url, 0);
            return $msg;
        }
        if ($param['EventKey']=='newMediaWap' || $param['EventKey']=='qrscene_newMediaWap') {
            $title = "新媒圈, 邀您内测";
            $description = "高校新媒体人的圈子社区, 分享运营者的智慧";
            $url = "http://www.koudaidaxue.com/index.php/Wap/Index/index?aid=abcd";
            $msg = sprintf($this->msgTemplate['news'], $param['FromUserName'], 
                    $param['ToUserName'], time(), 1, $title, $description, '', $url, 0);
            return $msg;
        }
        if (substr($param['EventKey'],0,1)==6 || substr($param['EventKey'],0,9)=='qrscene_6') {
            if (substr($param['EventKey'],0,1)==6) {
                $key = substr($param['EventKey'], 1);
            } else {
                $key = substr($param['EventKey'], 9);
            }
            $title = '点此继续账号绑定';
            $description = '绑定账号以正常接收提款红包';
            $url = "http://www.pocketuniversity.cn/index.php/Partner/Vip/addTakeFee?wx_media_id={$key}";
            $msg = sprintf($this->msgTemplate['news'], $param['FromUserName'], 
                    $param['ToUserName'], time(), 1, $title, $description, '', $url, 0);
            file_put_contents('qr.log', '<>'.print_r($msg, true), FILE_APPEND);
            return $msg;
        }
    }

    private function gxZhuShou($param)
    {
        if (substr($param['EventKey'],0,1)== 1 || substr($param['EventKey'],0,9)=='qrscene_1') {
            if (substr($param['EventKey'],0,1)=='1') {
                $key = substr($param['EventKey'], 1);
            } else {
                $key = substr($param['EventKey'], 9);
            }
            $title = '点此继续应用DIY权限申请';
            $description = '权限开通后，你可以进行以下操作：
            背景图片，背景色自定义
            分享标题自定义
            应用版权自定义
            ……
            更多精彩，敬请期待';
            $url = "http://www.pocketuniversity.cn/index.php/Bangdan/Qrcode?media_id={$key}";
            $msg = sprintf($this->msgTemplate['news'], $param['FromUserName'], 
                    $param['ToUserName'], time(), 1, $title, $description, '', $url, 0);
            file_put_contents('qr.log', '<>'.print_r($msg, true), FILE_APPEND);
            return $msg;
        }
        if (substr($param['EventKey'],0,1)== 5 || substr($param['EventKey'],0,9)=='qrscene_5') {
            if (substr($param['EventKey'],0,1)==5) {
                $key = substr($param['EventKey'], 1);
            } else {
                $key = substr($param['EventKey'], 9);
            }
            $orderModel = M('kdgx_traffic_order','','connection');
            $sql = "SELECT o.id,o.`phone`,g.`goods_name` FROM `kdgx_traffic_order` AS o JOIN `kdgx_traffic_goods` as g on o.`goods_id`= g.`id` where o.`id`= {$key} limit 0,1";
            $data = $orderModel->query($sql);
            $title = '点此继续流量充值';
            $description = "{$data[0]['goods_name']}  {$data[0]['phone']}

订单编号: {$key}";
            $url = "http://www.pocketuniversity.cn/index.php/Traffic/Index/showPay?order_id={$key}";
            $msg = sprintf($this->msgTemplate['news'], $param['FromUserName'], 
                    $param['ToUserName'], time(), 1, $title, $description, '', $url, 0);
            file_put_contents('qr.log', '<>'.print_r($msg, true), FILE_APPEND);
            return $msg;
        }
    }

    public function parseQRcode($param)
    {
        file_put_contents('qr.log', print_r($param, true));
        // $param = [
        //     'ToUserName' => 'gh_3347961cee42',
        //     'FromUserName' => 'oDDzCt4iFHZjUc1qClCHIvm8UWGg',
        //     'CreateTime' => '1492759018',
        //     'MsgType' => 'event',
        //     'Event' => 'SCAN',
        //     'EventKey' => '51',
        //     'Ticket' => 'gQF08DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAybWdaeDBUY1liMlQxVXdPVk5vMTUAAgTksflYAwQ8AAAA'
        // ];
        switch ($param['ToUserName']) {
            case 'gh_243fe4c4141f':
                return $this->xinMei($param);
            case 'gh_3347961cee42':
                return $this->gxZhuShou($param);
        }
    }

}