<?php
namespace Base\Controller;

use Base\Controller\CommonController;

use Message\Controller\MessageController;
use App\Controller\AppController;

/**
 * 消息体处理类
 */
class MsgController extends CommonController
{

    public function __construct()
    {
        parent::__construct();
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
                    case 'subscribe':   # 订阅事件
                        if (isset($param['EventKey'])) {  # 参数二维码订阅事件
                        }
                        return $this->distributeEvent($param);
                    case 'unsubscribe': # 取消订阅事件
                        if (isset($param['EventKey'])) {  # 参数二维码取消订阅事件
                        }
                    case 'LOCATION':    # 上报地理位置事件
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

    /**
     * 消息依次分发
     * @param array 消息体数组
     */
    private function distributeMsg($param)
    {
<<<<<<< HEAD
        if ($key = $this->publicKeyModel->getKeyStrategy($param['ToUserName'], $param['Content'], 'text')) {
            $message = new MessageController();
            $replayMsg = $message->distributeText($param, $key);
            $this->sendMsg($replayMsg);
        } elseif ($appMsg = $this->publicKeyModel->getKeyStrategy($param['ToUserName'], $param['Content'], 'app')) {
            $app = new AppController();
=======
        if ($keys = $this->publicKeyModel->getKeyStrategy($param['ToUserName'], $param['Content'], 'text')) {
            $message = A('Message\Message');
            $replayMsg = $message->distributeText($param, $keys);
            $this->sendMsg($replayMsg);
        } elseif ($appMsg = $this->publicKeyModel->getKeyStrategy($param['ToUserName'], $param['Content'], 'app')) {
            $app = A('App\App');
>>>>>>> ceec921bc1e6bc88ee6cd6f194b24a2cdbe918de
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
<<<<<<< HEAD
            $message = new MessageController();
            $replayMsg = $message->distributeText($param, $keys);
            $this->sendMsg($replayMsg);
        } elseif ($appMsg = $this->eventModel->getEventStrategy($param['ToUserName'], $param['Event'], $eventKey, 'app')) {
            $app = new AppController();
=======
            $message = A('Message\Message');
            $replayMsg = $message->distributeText($param, $keys);
            $this->sendMsg($replayMsg);
        } elseif ($appMsg = $this->eventModel->getEventStrategy($param['ToUserName'], $param['Event'], $eventKey, 'app')) {
            $app = A('App\App');
>>>>>>> ceec921bc1e6bc88ee6cd6f194b24a2cdbe918de
            $replayMsg = $app->distributeApp($param, $appMsg);
            $this->sendMsg($replayMsg);
        } else {
            echo 'success';
            exit;
        }
    }
}
