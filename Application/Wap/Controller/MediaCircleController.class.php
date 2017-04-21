<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/4/6
 * Time: 10:19
 */

namespace Wap\Controller;

use Think\Controller;
use Wap\Controller\ArticleController;

class MediaCircleController extends Controller
{
    public function detail()
    {
        $this->display('Index/detail');
    }

    public function preview()
    {
        $this->display('Index/preview');
    }

    public function getHotList()
    {
        // 获取最热动态
            // http://www.koudaidaxue.com/index.php/wap/MediaCircle/getHotList
        $articleActivity = new ArticleController();
        $list = $articleActivity->getWeightList();
        echo json_encode([
            'errcode' => 0,
            'errmsg' => $list
        ]);
        exit;
    }

}