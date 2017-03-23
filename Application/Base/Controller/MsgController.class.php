<?php
namespace Base\Controller;

use Base\Controller\CommonController;

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
    }

    /**
     * 消息体分发中心
     * @param array 消息体
     */
    public function replyMsg($param)
    {
        switch ($param['MsgType']) {
            case 'text':    # 文本消息
                return $this->distribute($param);
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
    private function distribute($param)
    {
        if ($keys = $this->publicKeyModel->getKeyStrategy($param['ToUserName'], $param['Content'], 'text')) {
            $this->distributeText($param, $keys);
        } elseif ($appMsg = $this->publicKeyModel->getKeyStrategy($param['ToUserName'], $param['Content'], 'app')) {
            $this->distributeApp($param, $appMsg);
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
        if ($keys = $this->eventModel->getEventStrategy($param['ToUserName'], $param['event'], 'text')) {
            $this->distributeText($param, $keys);
        } elseif ($appMsg = $this->eventModel->getEventStrategy($param['ToUserName'], $param['event'], 'app')) {
            $this->distributeApp($param, $appMsg);
        } else {
            echo 'success';
            exit;
        }
    }

    /**
     * 文本消息分发
     * @param array 关键字数组
     * @return xml 回复消息体
     */
    private function distributeText($param, $keys)
    {
        foreach ($keys as $key) {
            $textmsg = $this->textModel->getData($key['strategyId']);
            $msg = sprintf($this->msgTemplate['text'], $param['FromUserName'], $param['ToUserName'], time(), $textmsg['msg']);
            $this->sendMsg($msg);
        }
    }

    /**
     * 处理app应用消息
     * @param array 关键字数组
     * @return xmlstring 转发回调消息
     */
    private function distributeApp($param, $keys)
    {
        foreach ($keys as $key) {
            if ($appData = $this->appModel->getAppData($key['strategyId'])) {
                switch ($appData['type']) {
                    case 1: # 消息回复类应用
                        $url = $appData['url']."?type=trigger&media_id=".$param['ToUserName'];
                        $data = arr2Xml($param);
                        $header = array('content-type: application/xml');
                        $response = httpRequest($url, $data, $header);
                        $this->sendMsg($response);
                }
            }
        }
    }

    /**
     * 添加关键词
     * @param str 公众号id
     * @param arr 消息数组key和msg
     * @return boolean 返回值
     */
    public function addText($publicId, $keyMsg)
    {
        if (empty($keyMsg['msg']) || empty($keyMsg['key'])) {
            return false;
        }
        $data = [
            'msg' => $keyMsg['msg']
            ];
        $textId = $this->textModel->addData($data);
        $data = [
            'public_id' => $publicId,
            'keyword' => $keyMsg['key'],
            'strategy_id' => $textId,
            'type' => 'text'
        ];
        $keyId = $this->publicKeyModel->addData($data);
        return $keyId;
    }

    /**
     * 添加事件
     * @param str 公众号id
     * @param arr 消息数组事件和msg
     * @return boolean 返回值
     */
    public function addEvent($publicId, $eventMsg)
    {
        if (empty($eventMsg['msg']) || empty($eventMsg['event'])) {
            return false;
        }
        $data = [
            'msg' => $eventMsg['msg']
            ];
        $textId = $this->textModel->addData($data);
        $data = [
            'public_id' => $publicId,
            'event' => $eventMsg['event'],
            'strategy_id' => $textId,
            'type' => 'text'
        ];
        !empty($eventMsg['event_key']) && $data['event_key'] = $eventMsg['eventKey'];
        $eventId = $this->eventModel->addData($data);
        return $eventId;        
    }
}
