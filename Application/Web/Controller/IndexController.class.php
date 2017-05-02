<?php

namespace Web\Controller;

use Think\Controller;
use Base\Controller\WetchatApiController;

class IndexController extends Controller
{
    public function articleQRCode($article_id)
    {
        // 获取官网圈子文章的参数二维码
            // http://www.koudaidaxue.com/index.php/web/index/articleQRCode?article_id=1
        $wechatApi = new WetchatApiController();
        $wechatApi->publicId = 'gh_243fe4c4141f';
        $scene['action'] = 'QR_SCENE';
        $scene['key'] = 'scene_id';
        $scene['expire'] = 300;
        $scene['scene'] = '11'.$article_id;
        $ticket = $wechatApi->getQticket($scene);
        $ticket = urldecode($ticket);
        $url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket={$ticket}";
        $QRcode = httpRequest($url);
        header('Content-type:image/jpg');
        echo $QRcode;
    }

    public function publicRank()
    {
        $data = D('Wap/Data')->getRankData();
        echo json_encode([
            'errcode'=>0,
            'errmsg'=>$data,
        ]);
    }

}