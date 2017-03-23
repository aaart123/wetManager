<?php
namespace User\Controller;

use Base\Controller\BaseController;

/**
 * 登录模块
 */

class LoginController extends BaseController
{


    public function index()
    {
        $this->display();
    }

    /***
     * 首次注册
     * @param $array
     * @return array
     */
    public function register($array)
    {
        if( D('User/User')->addData($array) )
        {
            return array('errcode'=>0,'errmsg'=>'请求成功！');
        }else{
            return array('errcode'=>-1,'errmsg'=>'网络错误！');
        }
    }


    /***
     * 用户登录
     * @param $data
     * @return array
     */
    public function login($username, $password)
    {
        if( $data = D('User/User')->verifyLogin($username, $password) )
        {
            return array('errcode'=>0,'errmsg'=>$data);
        }else{
            return array('errcode'=>10005,'errmsg'=>'用户名或密码错误！');
        }
    }



















    
}