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
        $sql = 'SELECT u.`user_id`,
                       u.`nickname`,
                       u.`sex`,
                       u.`headimgurl`,
                       p.`nick_name` AS publicname
                FROM `pocket`.`kdgx_social_subscribe` AS s
                INNER JOIN `kdgx_user_info` AS u ON u.`user_id`= s.`subscribe_user`
                INNER JOIN `kdgx_wap_conf` AS c ON c.`user_id`= u.`user_id`
                LEFT JOIN `kdgx_wap_public` AS p ON c.`login_public`= p.`user_name`
                WHERE s.`subscribe_state`= \'1\'
                AND s.`user_id`='.$userId;
        $data['active'] = $this->query($sql);

        $sql = 'SELECT u.`user_id`,
                       u.`nickname`,
                       u.`sex`,
                       u.`headimgurl`,
                       p.`nick_name` AS publicname
                FROM `pocket`.`kdgx_social_subscribe` AS s
                INNER JOIN `kdgx_user_info` AS u ON u.`user_id`= s.`user_id`
                INNER JOIN `kdgx_wap_conf` AS c ON c.`user_id`= u.`user_id`
                LEFT JOIN `kdgx_wap_public` AS p ON c.`login_public`= p.`user_name`
                WHERE s.`subscribe_state`= \'1\'
                AND s.`subscribe_user`='.$userId;
        $data['passive'] = $this->query($sql);
        return $data;
    }


    /**
     * 获取推荐用户信息
     * @param $userId
     * @return mixed
     */
    public function getRecommendUser($userId,$page=0)
    {

        $limit = $page
                ?'LIMIT '.(($page-1) *20).' ,20'
                :'';
        $sql = 'SELECT COUNT(`subscribe_user`) AS num,
                       `subscribe_user` AS user_id
                FROM `kdgx_social_subscribe`
                WHERE `subscribe_user` IN(
                    SELECT `user_id`
                    FROM `kdgx_plat_user`
                    WHERE `user_id` NOT IN(
                    SELECT `subscribe_user`
                    FROM `kdgx_social_subscribe`
                    WHERE `user_id`= '.$userId.'
                    AND `subscribe_state`= 1)
                 )
                    AND `subscribe_state`= 1
                 GROUP BY `subscribe_user`
                 ORDER BY COUNT(`subscribe_user`) DESC '.$limit;
        $data = $this->query($sql);
        return $data;
    }


}