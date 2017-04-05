<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/3/29
 * Time: 17:40
 */

namespace Wap\Model;

use Wap\Model\BaseModel;

class UserInfoModel extends BaseModel
{

    protected $tableName = 'kdgx_user_info';




    
    public function getUserInfo($userId)
    {
        $sql = 'SELECT  i.`user_id`,
                        i.`nickname`,
                        i.`headimgurl`,
                        p.`nick_name`as publicname
                FROM `pocket`.`kdgx_user_info` AS i
                INNER JOIN `kdgx_plat_user` AS u ON u.`user_id`= i.`user_id`
                LEFT JOIN `kdgx_plat_public` AS p ON u.`login_public`= p.`user_name`
                WHERE i.`user_id`= '.$userId;
        $data = $this->query($sql);
        return $data[0];
    }

}