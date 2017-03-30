<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/3/28
 * Time: 21:53
 */

namespace Wap\Controller;

use Wap\Controller\BaseController;

use Think\Controller;

class UserController extends Controller
{
    const APP_ID = 'wxe8b12da30f8ed757';//微信分配的appID
    const APP_SECRET = '4a266d702e91408183772dcd3a774dfc';//微信分配的key


    /**
     * 授权获取微信用户信息
     */
    public function getWechatUserInfo()
    {
        $location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        if( $code = $_GET['code'] )
        {
            $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.self::APP_ID.'&secret='.self::APP_SECRET.'&code='.$code.'&grant_type=authorization_code';
            $json = https_request($url);
            $data = json_decode($json,true);
            if( $data['openid'] ){
                #判断手机号是否绑定新媒
                if( $userId = D('Base/User')->isOccupy(array('new_openid'=>$data['openid'])) )
                {
                    $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$data['access_token'].'&openid='.$data['openid'].'&lang=zh_CN';
                    $json = https_request($url);
                    $json = json_decode($json);

                    $data = array(
                        'user_id'       => $userId,
                        'nickname'      => $json->nickname,
                        'real_name'     => $json->real_name,
                        'sex'           => $json->sex,
                        'province'      => $json->province,
                        'city'          => $json->city,
                        'country'       => $json->country,
                        'headimgurl'    => $json->headimgurl,
                    );
                    if(D('UserInfo')->add($data,array(),true))
                    {
                        echo M()->getlastsql();
                    }
                }

            }else{
                $_SESSION['social_openid'] = $data['openid'];
                header('Location:http://www.koudaidaxue.com/index.php/Home/#/register');exit();//跳入注册界面
            }
        }else{
            $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.self::APP_ID.'&redirect_uri='.urlencode($location).'&response_type=code&scope=snsapi_userinfo&state='.rand(100000,999999).'#wechat_redirect';
            header('Location:'.$url);exit();
        }
    }

    

    public function userInfo()
    {
        $userId = $_SESSION['palt_user_id'];
        $data = D('Base/User')->getUserInfo($userId);
        $data['active'] = D('Base/subscribe')->where(array('user_id'=>$userId))->count;
        $data['passive'] = D('Base/subscribe')->where(array('subscribe_user'=>$userId))->count();
        echo json_encode(array(
            'errcode'=>0,
            'errmsg'=>$data,
        ));exit;
    }


    /**、
     * 获取用户所管理的所有公众号
     */
    public function getPublic()
    {
        $userId = $_SESSION['user_id'] = 2;
        if( $data = D('Base/PublicUser')->getPublic($userId) )
        {
            foreach ($data as $value) {
                if($info = D('Base/PublicUser')->getPublicInfo($value['public_id']))
                {
                    $array[] = $info;
                }
            }
            echo json_encode(array(
                'errcode'=>0,
                'errmsg'=>$array,
            ));exit();
        }else{
            echo json_encode(array(
                'errcode'=>1000,
                'errmsg'=>'数据为空！',
            ));exit();
        }
    }

    public function getSubscribeInfo()
    {
        $userId = $_SESSION['user_id'] = 2;
        $data = D('subscribe')->getSubScribeUserInfo($userId);
        echo json_encode(array(
            'errcode'=>0,
            'errmsg'=>$data,
        ));exit;
    }

    public function login()
    {
        $url = 'http://www.koudaidaxue.com/index.php/Wap/user/getSubscribeInfo';
        A('login')->authorization($url);
    }


}