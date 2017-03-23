<?php
namespace User\Controller;

use Base\Controller\BaseController;

/**
 * 用户处理模块
 */
class UserController extends BaseController
{

    /**
     * 获取用户信息
     * @param str   用户userID
     * @return array
     */
    public function getUserInfo($userId)
    {
        if( $data = D('User/User')->getUserInfo($userId) )
        {
            return array(
                'errcode'=>0,
                'errmsg'=>$data,
            );
        }else{
            return array('errcode'=>10002,'errmsg'=>'不合法的用户');
        }
    }


    /***
     * 获取微信上的信息
     * @param $openid
     * @param $access_token
     * @return mixed
     */
    public function getWecahtUserInfo($openid, $access_token)
    {
        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid;
        $json = https_request($url);
        return json_decode($json,true);
    }




    /***
     * 绑定或修改手机号码
     * @param $userId
     * @param $phone
     * @return array
     */
    public function bindingPhone($userId, $phone)
    {
        if( D('User/User')->setData(array('user_id'=>$userId,'phone'=>$phone)) )
        {
            return array('errcode'=>0,'errmsg'=>'请求成功');
        }else{
            return array('errcode'=>-1,'errmsg'=>'请求失败');
        }
    }


    /***
     * 绑定或修改邮箱
     * @param $userId
     * @param $phone
     * @return array
     */
    public function bindingEmail($userId, $email)
    {
        if( D('User/User')->setData(array('user_id'=>$userId,'email'=>$email)) )
        {
            return array('errcode'=>0,'errmsg'=>'请求成功');
        }else{
            return array('errcode'=>-1,'errmsg'=>'请求失败');
        }
    }


    /**
     * 修改密码
     * @param $userId
     * @param $password
     * @return array
     */
    public function setPassword($userId, $password)
    {
        if( D('User/User')->setData(array('user_id'=>$userId,'password'=>md5($password))) )
        {
            return array('errcode'=>0,'errmsg'=>'请求成功');
        }else{
            return array('errcode'=>-1,'errmsg'=>'请求失败');
        }
    }





}