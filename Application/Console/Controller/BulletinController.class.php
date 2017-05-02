<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/4/24
 * Time: 18:42
 */

namespace Console\Controller;

use Think\Controller;

class BulletinController extends Controller
{
    public function index()
    {

        $send_date = date('Y-m-d');
        $sql = "SELECT u.`openid`,
                       u.`user_id`,
                       i.`nickname`
                FROM `kdgx_wap_conf` AS c
                LEFT JOIN `kdgx_plat_user` AS u ON c.`user_id`= u.`user_id`
                INNER JOIn `kdgx_user_info` AS i ON u.`user_id`=i.`user_id`
                WHERE c.`is_daily`= '1'
                AND c.`user_id` NOT IN(
                    SELECT `user_id`
                    FROM `log_bulletin`
                WHERE `send_date`= '$send_date')";
        $array = M()->query($sql);


        $count = count($array);


        $d = 21-date('H');


        if($d<=10 && $d>=1)
        {
            $times = ceil($count/$d);
        }else{
            exit;
        }


        $i = 0;
        foreach ($array as $value)
        {
            D('Console/Bulletin')->add(array(
                'user_id'=>$value['user_id'],
                'send_date'=>$send_date,
            ));
            $array = array(
                'openid'=>$value['openid'],
                'url'=>'http://www.koudaidaxue.com/index.php/Wap/index/index?page=dialy',
                'first'=>"你订阅的".$send_date."的新媒快报如下\n",
                'keyword1'=>"【".$value['nickname']."】的新媒快报",
                'keyword2'=>"为您精选昨日10条圈内动态。",
                'remark'=>"\n点击查看快报详情",
            );
            $obj = new \Base\Controller\WetchatApiController();
            $obj->publicId = 'gh_243fe4c4141f';
            $obj->setBulletinTemplate($array);
            if(++$i==$times)
            {
                exit;
            }

        }

    }
    
    
}