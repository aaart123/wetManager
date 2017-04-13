<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/4/1
 * Time: 11:28
 */

namespace Wap\Model;

use Wap\Model\BaseModel;

class ConfModel extends BaseModel
{
    protected $tableName = 'kdgx_wap_conf';


    public function getDailyUser()
    {
        $sql = 'select u.`user_id`,
                       u.`openid`,
                       u.`login_public`
                from `kdgx_plat_user` AS u
                INNER JOIN `kdgx_wap_conf` AS c ON u.`user_id`= c.`user_id`
                WHERE c.`is_daily`= \'1\'';
        $data = $this->query($sql);
        return $data[0];
    }


    public function getSubscribeOpenid($userId)
    {
        $sql = 'SELECT u.`openid` 
                FROM `kdgx_plat_user` As u
                INNER JOIN `kdgx_wap_conf` AS c ON c.`user_id`= u.`user_id`
                INNER JOIN `kdgx_social_subscribe` AS s ON s.`user_id` =u.`user_id` 
                WHERE `is_subscribe` =\'1\' AND s.`subscribe_user` ='.$userId;
        $data = $this->query($sql);
        return $data;
    }

}