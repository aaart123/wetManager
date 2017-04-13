<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/4/5
 * Time: 18:11
 */

namespace Wap\Controller;

use Think\Controller;

class MessageController extends Controller
{

    public function Daily()
    {
        $data = D('Conf')->getDailyUser();
        foreach ($data AS $value)
        {
            $value['login_public'];

        }

    }


    public function test()
    {

        $array=array(
            'openid'=>'oyTk8w-KvFXtWHLlgP9U7RSXWIsE',
            'url'=>'http://www.koudaidaxue.com',
            'first'=>'你关注的内容有更新',
            'keyword1'=>'姜天放的更新',
            'keyword2'=>'04/25/12:00更新',
            'remark'=>'点此查看详情，回复TD退订此消息',
        );

        $obj = new \Base\Controller\WetchatApiController();
        $obj->publicId = 'gh_243fe4c4141f';
        $a = $obj->setAuthTemplate($array);
        var_dump($a);
    }



}