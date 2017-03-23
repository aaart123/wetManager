<?php
namespace User\Model;

use Base\Model\AppModel;

/**
 * 用户模型
 */
class UserModel extends AppModel
{
    protected $trueTableName = 'kdgx_plat_user';


    public function __construct()
    {
        parent:: __construct();
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
        return $this->where(array('user_id',$data['user_id']))->setfield($data);
    }


    /***
     * 验证登录
     * @param $username
     * @param $password
     * @return mixed
     */
    Public function verifyLogin($username, $password)
    {
        if( strpos($username, '@') )
        {
            return D('User')->verifyEmail($username,md5($password));//邮箱登录
        }else{
            return D('User')->verifyPhone($username,md5($password));//手机登录
        }
    }

    /***
     * 登录验证手机号
     * @param $phone
     * @param $password
     * @return userID
     */
    private function verifyPhone($phone, $password)
    {
        return $this->where(array('phone'=>$phone,'password'=>$password))->getfield('user_id');
    }

    /***
     * 登录验证邮箱
     * @param $email
     * @param $password
     * @return userId
     */
    private function verifyEmail($email, $password)
    {
        return $this->where(array('email'=>$email,'password'=>$password))->getfield('user_id');
    }




    /**
     * 判断信息是否被注册
     * @param $array
     * @return mixed
     */
    public function isOccupy($array)
    {
        return $this->where($array)->find();
    }


    /**
     * 获取用户的信息
     */
    Public function getUserInfo($userId)
    {
        return $this->fied('phone,email,openid')->where(array('user_id'=>$userId))->find();
    }




}