<?php

namespace Wap\Controller;

use Think\Controller;



class BaseController extends Controller
{


//    const APP_ID = 'wx3f6b8b2eb0483f2f';//微信分配的appID
//    const APP_SECRET = '6861d005542696913ed31a9645690595';//微信分配的key
    const APP_ID = 'wxe8b12da30f8ed757';//微信分配的appID
    const APP_SECRET = '4a266d702e91408183772dcd3a774dfc';//微信分配的key
    

    public function __construct()
    {
        parent::__construct();

        //授权微信
        if(empty($_SESSION['plat_user_id']))
        {
            $Object = A('Login');
            $Object->silentAuthWechat();
        }

        //授权公众号
        $userId = $_SESSION['palt_user_id'];
        if( $data = D('Base/PublicUser')->getPublic($userId) )
        {
            foreach ($data as $value) {
                if($info = D('Base/PublicUser')->getPublicInfo($value['public_id']))
                {
                    $array[] = $info;
                }
            }
            if(empty($array))
            {
                header('Location:http://www.koudaidaxue.com/index.php/Wap/login/index#/banding');exit;
            }
        }

        if($login_public = D('Base/User')->where(array('user_id'=>$userId))->getfield('login_public'))
        {
            $_SESSION['plat_public_id'] = $login_public;
        }else{
            $data = D('Base/PublicUser')->getPublic($userId);
            $_SESSION['plat_public_id'] = $data[0]['public_id'];
        }

    }

}