<?php
namespace Base\Controller;

use Base\Controller\CommonController;

/**
 * 关键词控制类
 */
class KeyController extends CommonController
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
            $datas = $this->publicKeyModel->getKeyStrategy($publicId, $key['keyword'], 'text');
            foreach ($datas as $data) {
                $data1 = $this->textModel->getData($data['strategyId']);
                $msg[] = array_merge($data, $data1);
            }
        }
        $apps = $this->publicKeyModel->getKeys($publicId, 'app');
        foreach ($apps as $app) {
            $datas = $this->publicKeyModel->getKeyStrategy($publicId, $app['keyword'], 'app');
            foreach ($datas as $data) {
                $data1 = $this->appModel->getAppData($data['strategyId']);
                $msg[] = array_merge($data, $data1);
            }
        }
        return $mgs;
    }
}
