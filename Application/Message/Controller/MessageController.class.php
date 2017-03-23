<?php
namespace Message\Controller;

use Base\Controller\CommonController;

/**
 * 消息处理类
 */

class MessageController extends CommonController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 文本消息分发
     * @param array 关键字数组
     * @return xml 回复消息体
     */
<<<<<<< HEAD
    public function distributeText($param, $key)
    {
        $textmsg = $this->textModel->getData($key['strategyId']);
        $msg = sprintf($this->msgTemplate['text'], $param['FromUserName'], $param['ToUserName'], time(), $textmsg['msg']);
        return $msg;
=======
    public function distributeText($param, $keys)
    {
        foreach ($keys as $key) {
            $textmsg = $this->textModel->getData($key['strategyId']);
            $msg = sprintf($this->msgTemplate['text'], $param['FromUserName'], $param['ToUserName'], time(), $textmsg['msg']);
            return $msg;
        }
>>>>>>> ceec921bc1e6bc88ee6cd6f194b24a2cdbe918de
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
}
