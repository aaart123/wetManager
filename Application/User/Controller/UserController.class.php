<?php
namespace User\Controller;

use Base\Controller\BaseController;

/**
 * 用户处理模块
 */
class UserController extends BaseController
{



    /***
     * 提交用户信息
     * @param $data
     * @return mixed
     */
    public function reg($data)
    {
        if(empty($data['phone']) || !isset($data['phone']))
        {
            return false;
        }
        if(empty($data['password']) || !isset($data['password']))
        {
            return false;
        }
        return D('Base/User')->addData($data);
    }

    /***
     * 获取微信上的信息
     * @param $openid
     * @param $access_token
     * @return mixed
     */
    public function getWechatUserInfo($openId)
    {
        $data = D('Base/Token')->getToken($openId);
        if( time() - $data['timestamp'] > 7200)   //判断assess_token是否过期
        {
            if( $this->refreshToken($openId) )
            {
                return $this->getWechatUserInfo($openId);
            }
        }else{
            $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$data['access_token'].'&openid='.$data['openid'];
            $json = https_request($url);
            return json_decode($json,true);
        }
    }


    /**
     * 刷新access_token
     * @param $openId
     * @return bool
     */
    public function refreshToken($openId)
    {
        $array = D('Base/Token')->getToken($openId);
        $appid = 'wx2e389f57cd3f6f51';
        $url = 'https://api.weixin.qq.com/sns/oauth2/refresh_token?appid='.$appid.'&grant_type=refresh_token&refresh_token='.$array['refresh_token'];
        $json = https_request($url);
        $array = json_decode($json,true);
        if($array['access_token']){
            return D('Base/Token')->setToken($array['openid'], $array['access_token'], $array['refresh_token']);
        }else{
            return false;
        }
    }




    /**
     * 判断信息是否被注册
     * @param $array
     * @return mixed
     */
    public function isOccupy($array)
    {
        return D('Base/User')->isOccupy($array);
    }




    public function verifyLogin($where)
    {
        return D('Base/User')->verifyLogin($where);
    }


    /**
     * 修改资料
     * @param $data
     * @return mixed
     */
    public function setData($data)
    {
        return D('Base/User')->setData($data);
    }



    /***
     * 绑定或修改手机号码
     * @param $userId
     * @param $phone
     * @return array
     */
    public function bindingPhone($userId, $phone)
    {
        return D('Base/User')->setData(array('user_id'=>$userId,'phone'=>$phone));
    }


    /***
     * 绑定或修改邮箱
     * @param $userId
     * @param $phone
     * @return array
     */
    public function bindingEmail($userId, $email)
    {
        return D('Base/User')->setData(array('user_id'=>$userId,'email'=>$email));
    }

    /**
     * 绑定或修改微信号
     * @param $userId
     * @param $openId
     * @return mixed
     */
    public function bindingWechat($userId, $openId)
    {
        return D('Base/User')->setData(array('user_id'=>$userId,'openid'=>$openId));
    }


    /**
     * 修改密码
     * @param $userId
     * @param $password
     * @return array
     */
    public function setPassword($userId, $password)
    {
        return D('Base/User')->setData(array('user_id'=>$userId,'password'=>md5($password)));
    }





}