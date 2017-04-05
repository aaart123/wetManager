<?php
namespace Base\Model;

use Base\Model\BaseModel;

/**
 * 用户模型
 */

class UserModel extends BaseModel
{
    protected $trueTableName = 'kdgx_plat_user';



    public function __construct(){
        parent::__construct();
    }

    /**
     * 添加数据
     * @param array 添加数据
     */
    public function addData($data)
    {
        return $this->add($data);
    }


    /***
     * 修改用户数据
     * @param array $data
     * @return mixed
     */
    public function setData($data)
    {
        return $this->where(array('user_id'=>$data['user_id']))->setfield($data);
    }



    /***
     * 登录验证
     * @param $phone
     * @param $password
     * @return userID
     */
    public function verifyLogin($where)
    {
        return $this->where($where)->getfield('user_id');
    }





    /**
     * 判断信息是否被注册
     * @param $array
     * @return mixed
     */
    public function isOccupy($array)
    {
        return $this->where($array)->getfield('user_id');
    }


    /**
     * 获取用户的信息
     */
    Public function getUserInfo($userId)
    {
        $sql = 'SELECT u.`user_id`, i.`nickname`, i.`headimgurl`, i.`sex`, u.`phone`, u.`email` FROM `pocket`.`kdgx_plat_user` AS u  INNER JOIN `pocket`.`kdgx_user_info` AS i ON i.`user_id` =u.`user_id` WHERE u.`user_id` ='.$userId;
        $data = $this->query($sql);
        return $data[0];
    }


}