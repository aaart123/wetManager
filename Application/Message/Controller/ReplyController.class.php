<?php
namespace Msg\Controller;

use Base\Controller\CommonController;

/**
 * 回复管理
 */
class ReplyController extends CommonController
{
    protected $publicKeyModel;
    public function __construct()
    {
        parent::__construct();
        $this->publicKeyModel = D('publicKey');
    }

    public function getReply($public, $type)
    {
        return $this->publicKeyModel->getKeys($publicId, $type);
    }
}