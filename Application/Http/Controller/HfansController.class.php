<?php

namespace Http\Controller;

use Think\Controller;
use Http\Controller\AuthController;
use Fans\Controller\FansController;

class HfansController extends Controller
{
    private $fansActivity;
    public function __construct()
    {
        parent::__construct();
        $this->fansActivity = new FansController();
    }

    public function getFnasList($public_id)
    {
        // 获取公众号粉丝列表
            // http://www.koudaidaxue.com/index.php/Http/Hfans/getFnasList?public_id=gh_243fe4c4141f
        $list = $this->fansActivity->getAll($public_id);
        echo json_encode([
            'errcode' => 0,
            'errmsg' => $list
        ]);
        exit;
    }
}