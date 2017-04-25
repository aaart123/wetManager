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
                       u.`user_id`
                FROM `kdgx_wap_conf` AS c
                LEFT JOIN `kdgx_plat_user` AS u ON c.`user_id`= u.`user_id`
                WHERE c.`is_daily`= '1'
                AND c.`user_id` NOT IN(
                    SELECT `user_id`
                    FROM `log_bulletin`
                WHERE `send_date`= '$send_date')";
        $array = M()->query($sql);
        $count = count($array);

        $d = 21-date('H');
        $times = ceil($count/$d);

        $i = 1;

        foreach ($array as $value)
        {
            D('Console/Bulletin')->add(array(
                'user_id'=>$value['user_id'],
                'send_date'=>$send_date,
            ));

            
            if($i==$times)
            {
                exit;
            }

        }

    }
    
    
}