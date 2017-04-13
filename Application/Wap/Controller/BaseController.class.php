<?php

namespace Wap\Controller;

use Think\Controller;



class BaseController extends Controller
{



    

    public function __construct()
    {
        parent::__construct();

        //授权微信
        if(empty($_SESSION['plat_user_id']) || !isset($_SESSION['plat_user_id']))
        {
            $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $Object = A('Login');
            $Object->silentAuthWechat($url);
        }

        //授权公众号
        $userId = $_SESSION['plat_user_id'];
        $data = D('Base/PublicUser')->getPublicInfo($userId);
        if( $data = D('Base/PublicUser')->getPublicInfo($userId) )
        {
            $login_public = D('User')->where(array('user_id'=>$userId))->getfield('login_public');
            $_SESSION['plat_public_id'] = $login_public;
        }else{
            header('Location:http://www.koudaidaxue.com/index.php/wap/Login/index?type=binding');exit;
        }
    }

}