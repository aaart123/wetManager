<?php
namespace Message\Controller;

use Base\Controller\BaseController;

/**
 * 关键词控制类
 */
class KeyController extends BaseController
{

    public function __construct()
    {
        parent:: __construct();
    }

    /**
     * 获取关键词回复
     * @param str 公众号id
     */
    public function getReply($publicId)
    {
        $keys = $this->publicKeyModel->getKeys($publicId, 'text');
        $msg = array();
        foreach ($keys as $key) {
            $data = $this->publicKeyModel->getKeyStrategy($publicId, $key['keyword'], 'text');
            $textData = $this->textModel->getData($data['strategyId']);
            $data['info'] = $textData;
            $msg[] = $data;
        }
        $apps = $this->publicKeyModel->getKeys($publicId, 'app');
        foreach ($apps as $app) {
            $data = $this->publicKeyModel->getKeyStrategy($publicId, $app['keyword'], 'app');
            $appData = $this->appModel->getData($data['strategyId']);
            $data['info'] = $appData;
            $msg[] = $data;
        }
        return $msg;
    }

}
