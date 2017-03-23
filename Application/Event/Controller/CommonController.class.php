<?php
namespace Event\Controller;

use Base\Controller\BaseController;

/**
 * 事件控制核心类
 */
class CommonController extends BaseController
{
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