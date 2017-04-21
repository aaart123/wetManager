<?php

namespace Wap\Controller;

use Think\Controller;

class LoginController extends Controller
{
    const APP_ID = 'wx3f6b8b2eb0483f2f';//微信分配的appID
    const APP_SECRET = '6861d005542696913ed31a9645690595';//微信分配的key

    public function __construct()
    {
        parent::__construct();

        if(empty($_SESSION['plat_user_id']) || !isset($_SESSION['plat_user_id']))
        {
            $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            if($code = $_GET['code']){
                $http = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.self::APP_ID.'&secret='.self::APP_SECRET.'&code='.$code.'&grant_type=authorization_code';
                $json = https_request($http);
                $data = json_decode($json,true);
                if( $userId = D('Base/User')->isOccupy(array('openid'=>$data['openid'])) )
                {
                    $_SESSION['plat_user_id'] = $userId;
                    header('Location:'.$url);exit;
                }else{
                    $url = 'http://www.koudaidaxue.com/index.php/Wap/Outside/invite?aid='.$_GET['aid'];
                    header('Location:'.$url);exit;
                }
            }else{
                $url = urlencode($url);
                $http = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.self::APP_ID.'&redirect_uri='.$url.'&response_type=code&scope=snsapi_base&state='.rand(1000,9999).'#wechat_redirect';
                header('Location:'.$http);exit;
            }
        }
    }


    public function binding()
    {
        $this->display('Index/binding');
    }

    public function getUserInfo()
    {
        $userId = $_SESSION['plat_user_id'];
        $data = D('Wap/UserInfo')->getUserInfo($userId);
        echo json_encode(array(
            'errcode'=>0,
            'errmsg'=>$data
        ));
    }

    public function getPublic()
    {
        $userId = $_SESSION['plat_user_id'];
        $data = D('Wap/PublicUser')->getPublicInfo($userId);
        echo json_encode(array(
            'errcode'=>0,
            'errmsg'=>$data
        ));
    }


}