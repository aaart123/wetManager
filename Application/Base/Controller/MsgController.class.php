<?php
namespace Base\Controller;

use Base\Controller\CommonController;

use Message\Controller\MessageController;
use App\Controller\AppController;
use Event\Controller\SubscribeController;

/**
 * 消息体处理类
 */
class MsgController extends CommonController
{

    private $subscributeObj;
    public function __construct()
    {
        parent::__construct();
        $this->subscributeObj = new SubscribeController();
    }

    /**
     * 发送消息
     * @param string 消息类型
     * @param array 消息包数组
     */
    private function sendMsg($replyData)
    {
        $timeStamp = time();
        $nonce = getRandomStr();
        $response = $this->wxBizMsgCrypt->encryptMsg($replyData, $timeStamp, $nonce, $encryptMsg);
        echo $encryptMsg;
        exit;
    }

    /**
     * 消息体分发中心
     * @param array 消息体
     */
    public function replyMsg($param)
    {
        switch ($param['MsgType']) {
            case 'text':    # 文本消息
                return $this->distributeMsg($param);
            case 'image':   # 图片消息
                return;
            case 'voice':   # 语音消息
                return;
            case 'video':   # 视频消息
                return;
            case 'shortvideo':  # 小视频消息
                return;
            case 'location':    # 位置信息
                return;
            case 'link':        # 链接消息
                return;
            case 'event':       # 事件
                switch ($param['Event']) {
                    case 'SCAN': #参数二维码事件
                        return $this->scanEvent($param);
                    case 'subscribe':   # 订阅事件
                        $this->subscributeObj->subscribeLog($param);
                        if (!empty($param['EventKey'])) {  # 参数二维码订阅事件
                            return $this->scanEvent($param);
                        }
                        return $this->distributeEvent($param);
                    case 'unsubscribe': # 取消订阅事件
                        $this->subscributeObj->subscribeLog($param);
                        if (!empty($param['EventKey'])) {  # 参数二维码取消订阅事件
                        }
                        return $this->subscribeEvent($param);
                    case 'LOCATION':    # 上报地理位置事件
                        $this->distributeEvent($param);
                        return;
                    case 'CLICK': # 自定义菜单事件
                        return;
                    case 'VIEW':  # 点击菜单跳转链接时的事件
                        return;
                }
                return;
            echo 'success';
        }
    }

    private function caseMsg(&$msg)
    {
        if ($param['Content'] == 'TESTCOMPONENT_MSG_TYPE_TEXT') {
            $content = 'TESTCOMPONENT_MSG_TYPE_TEXT_callback';
            $message = new MessageController();
            $replayMsg = $message->caseText($param, $content);
            $this->sendMsg($replayMsg);
        }
        echo '';
        file_put_contents('./kf.log', json_encode($param));
        $wetchatApi = new \Base\Controller\WetchatApiController();
        $auth_code = explode(':', $param['Content']);
        $auth_code = $auth_code['1'];
        $response = file_get_contents('component_access_token');
        $response = json_decode($response);
        $component_access_token = $response->component_access_token;
        $url = "https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token={$component_access_token}";
        $data = '{
            "component_appid":"wx19961250208a65e8",
            "authorization_code":"'.$auth_code.'"
        }';
        $response = httpRequest($url, $data);
        file_put_contents('./case.log', $response);
        exit;
    }

    /**
     * 消息依次分发
     * @param array 消息体数组
     */
    private function distributeMsg($param)
    {
        if ($key = $this->publicKeyModel->getKeyStrategy($param['ToUserName'], $param['Content'], 'text')) {
            $message = new MessageController();
            $replayMsg = $message->distributeText($param, $key);
            $this->sendMsg($replayMsg);
        } elseif ($appMsg = $this->publicKeyModel->getKeyStrategy($param['ToUserName'], $param['Content'], 'app')) {
            $app = new AppController();
            $replayMsg = $app->distributeApp($param, $appMsg);
            $this->sendMsg($replayMsg);
        } else {
            echo 'success';
            exit;
        }
    }

    /**
     * 事件依次分发
     * @param array 事件体数组
     */
    private function distributeEvent($param)
    {
        $eventKey = isset($param['EventKey']) ? $param['EventKey'] : '';
        if ($keys = $this->eventModel->getEventStrategy($param['ToUserName'], $param['Event'], $eventKey, 'text')) {
            $message = new MessageController();
            $replayMsg = $message->distributeText($param, $keys);
            $this->sendMsg($replayMsg);
        } elseif ($appMsg = $this->eventModel->getEventStrategy($param['ToUserName'], $param['Event'], $eventKey, 'app')) {
            $app = new AppController();
            $replayMsg = $app->distributeApp($param, $appMsg);
            $this->sendMsg($replayMsg);
        } else {
            echo 'success';
            exit;
        }
    }

    /**
     * 二维码事件
     */
    public function scanEvent($param)
    {
        $replayMsg = $this->subscributeObj->parseQRcode($param);
        $this->sendMsg($replayMsg);
    }
}
