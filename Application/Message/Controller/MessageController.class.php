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

    public function caseText($param, $content)
    {
        $msg = sprintf($this->msgTemplate['text'], $param['FromUserName'], $param['ToUserName'], time(), $content);
        return $msg;
    }

    /**
     * 文本消息分发
     * @param array 关键字数组
     * @return xml 回复消息体
     */
    public function distributeText($param, $key)
    {
        $textmsg = $this->textModel->getData($key['strategyId']);
        $msg = sprintf($this->msgTemplate['text'], $param['FromUserName'], $param['ToUserName'], time(), $textmsg['msg']);
        return $msg;
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
        $data['msg'] = $keyMsg['msg'];
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
     * 更新关键字
     * @param array 数组
     */
    public function updateKey($data)
    {
        $where['key_id'] = $data['keyId'];
        $add['msg'] = $data['msg'];
        unset($data['keyId']);
        unset($data['msg']);
        $textId = $this->textModel->addData($add);
        $data['strategy_id'] = $textId;
        if ($this->publicKeyModel->updateData($where, $data)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 删除关键词
     * @param int keyId
     */
    public function deleteText($keyId)
    {
        $where['key_id'] = $keyId;
        if ($this->publicKeyModel->deleteKey($where)) {
            return true;
        } else {
            return false;
        }
    }

}
