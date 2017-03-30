<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/3/28
 * Time: 11:55
 */

namespace Wap\Model;

use Think\Model;

class SubscribeModel extends Model
{
    protected $tableName = 'kdgx_social_subscribe';



    /**
     * 互相关注的资料
     */
    public function getSubScribeUserInfo($userId)
    {
        $sql = 'SELECT u.`user_id`, u.`nickname` ,u.`sex` ,u.`headimgurl` FROM `pocket`.`kdgx_social_subscribe` AS s INNER JOIN `kdgx_user_info` AS u ON u.`user_id`= s.`subscribe_user` WHERE s.`user_id` ='.$userId;
        $data['active'] = $this->query($sql);
        $sql = 'SELECT u.`user_id`, u.`nickname` ,u.`sex` ,u.`headimgurl` FROM `pocket`.`kdgx_social_subscribe` AS s INNER JOIN `kdgx_user_info` AS u ON u.`user_id`= s.`user_id` WHERE s.`subscribe_user` ='.$userId;
        $data['passive'] = $this->query($sql);
        return $data;
    }


}