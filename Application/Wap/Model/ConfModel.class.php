<?php


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


    /**
     * 获取粉丝开启关注推送的用户openid
     * @param $userId
     * @return mixed
     */
    public function getSubscribeOpenid($userId)
    {
        $sql = 'SELECT u.`openid` 
                FROM `kdgx_plat_user` As u
                INNER JOIN `kdgx_wap_conf` AS c ON c.`user_id`= u.`user_id`
                INNER JOIN `kdgx_social_subscribe` AS s ON s.`user_id` =u.`user_id` 
                WHERE `is_subscribe` =\'1\' 
                 AND  s.`subscribe_state`= \'1\'
                 AND s.`subscribe_user` ='.$userId;
        $data = $this->query($sql);
        return $data;
    }

    /**
     * 获取公众号管理员开启日报推送的用户openid
     * @param $publicId
     */
    public function getDailyOpenid($publicId)
    {
        $sql = "SELECT user_list FROM `kdgx_plat_public_user` WHERE `public_id`='$publicId' ";
        $userList = $this->query($sql);
        $array = explode(',',$userList[0]['user_list']);
        foreach ($array as $v)
        {
            $sql = "SELECT u.openid
                    FROM `kdgx_plat_user` AS u
                    INNER JOIN `kdgx_wap_conf` AS c ON u.`user_id`= c.`user_id`
                    WHERE u.`user_id`= '$v' AND `is_daily`='1'";
            $arr =  $this->query($sql);
            $data[] = $arr[0];
        }
        return $data;
    }



    /**
     * 获取当前登录的公众信息
     */
    public function getLoginPublicInfo($userId)
    {
        $sql = 'SELECT p.`user_name`,
                       p.`nick_name`,
                       p.`alias`,
                       p.`head_img`
                FROM `pocket`.`kdgx_wap_conf` AS c
                INNER JOIN `kdgx_wap_public` AS p ON c.`login_public`= p.`user_name`
                WHERE c.`user_id`='.$userId;
        $data = M()->query($sql);
        return $data[0];
    }

}