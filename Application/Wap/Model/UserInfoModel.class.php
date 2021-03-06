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


    public function setData($array)
    {
        if($this->where(array('user_id'=>$array['user_id']))->getField('user_id'))
        {
            $this->create($array,2);
            return $this->save();
        }else{
            $this->create($array,1);
            return $this->add();
        }
    }



    
    public function getUserInfo($userId)
    {
        $sql = "SELECT i.`user_id`,
                       p.`user_name`,
                       i.`nickname`,
                       i.`headimgurl`,
                       p.`nick_name`as publicname
                FROM `pocket`.`kdgx_user_info` AS i
                INNER JOIN `kdgx_wap_conf` AS c ON c.`user_id`= i.`user_id`
                LEFT JOIN `kdgx_wap_public` AS p ON c.`login_public`= p.`user_name`
                WHERE i.`user_id`= '$userId'";
        $data = $this->query($sql);
        return $data[0];
    }

}