<?php
namespace Base\Controller;

use Base\Controller\BaseController;

class FansController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取粉丝列表
     * @param str 公众号id
     */
    public function getFansList($publicId)
    {
        return $this->openidModel->getPublicData($publicId);
    }
}