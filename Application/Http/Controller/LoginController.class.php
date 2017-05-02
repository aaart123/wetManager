<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/3/14
 * Time: 11:59
 */

namespace Http\Controller;

use Think\Controller;

use Base\Controller\WetchatApiController as Wechat;

class LoginController extends Controller
{


    /***
     * 验证是否登录
     */
    public function isLogin()
    {
        if($_SESSION['plat_user_id'])
        {
            $userId = $_SESSION['plat_user_id'];
            if($publicId = D('Base/User')->where(array('user_id'=>$userId))->getfield('login_public'))
            {
                $_SESSION['plat_public_id'] = $publicId;
            }else{
                $publicList = D('Base/PublicUser')->getPublicInfo($userId);
                D('Base/User')->where(array('user_id'=>$userId))->setfield('login_public',$publicList[0]['public_id']);
                $publicList[0]['public_id'] ? $_SESSION['plat_public_id'] = $publicList[0]['public_id'] : '';
            }

            echo json_encode(array(
                'errcode'=>0,
                'errmsg'=>$userId,
            ));exit();
        }else{
            echo json_encode(array(
                'errcode'=>1000,
                'errmsg'=>'用户未登录',
            ));exit();
        }
    }


    /**
     * 注销账户
     */
    public function logout()
    {
        session_unset();
    }


    /**
     * 验证登录密码
     */
    public function login()
    {
        $actionId = $_GET['id'];
        if($user = D('Base/Action')->field('state,openid')->where(array('action_id'=>$actionId))->find())
        {

            if($user['state'] != 0)
            {
                echo json_encode(array(
                    'errcode'=>$user['state'],
                    'errmsg'=>false
                ));exit;
            }
            $openId = $user['openid'];
            if($userId = D('Base/User')->where(array('openid'=>$openId))->getField('user_id'))
            {
                $_SESSION['plat_user_id'] = $userId;
                $login_public = D('Base/User')->where(array('user_id'=>$userId))->getfield('login_public');
                if(D('Base/PublicUser')->isAuthPublic($login_public)){
                    $_SESSION['plat_public_id'] = $login_public;
                }else{
                    $data = D('Base/PublicUser')->getPublicInfo($userId);
                    $_SESSION['plat_public_id'] = $data[0];
                    D('User')->where(array('user_id'=>$userId))->setfield('login_public',$data[0]);
                }
                echo json_encode(array(
                    'errcode'=>0,
                    'errmsg'=>'登录成功',
                ));exit;

            }else{
                $_SESSION['plat_openid'] = $openId;
                echo json_encode(array(
                    'errcode'=>10001,
                    'errmsg'=>'未绑定手机号',
                ));exit;
            }
        }

    }


    /**
     * 发送短信验证码
     */
    public function sendCode()
    {
        $phone = $_POST['phone'];//填写的手机号码
        $number = rand(100000, 999999);
        $alisms = new \Common\Common\Alisms('LTAIAW0QC5C4TFrz','z8Z21kGY59MTTQ6Ro8ERoHtnl6mGe7');
        $templateCode = 'SMS_33645749';
        $paramString = '{"code":"'.$number.'","product":"口袋"}';
        $response = $alisms->smsend($phone, $templateCode, $paramString);
        if(isset($response['RequestId']) && !empty($response['RequestId'])) {
            session('phoneVerify',md5($phone.$number));
            echo json_encode(array(
                'errcode'	=> 0,
                'errmsg'	=> '成功',
            ));exit();
        }else {
            echo json_encode(array(
                'errcode'	=> 1046,
                'errmsg'=>'手机验证码发送过于频繁',
            ));exit();
        }
    }


    /**
     * 首次提交注册信息
     */
    public function reg()
    {
        if(empty($_SESSION['plat_openid']))
        {
            echo json_encode(array(
                'errcode'=>10001,
                'errmsg'=>'未扫码登录',
            ));exit;
        }
        $code = $_POST['code'];
        $phone = $_POST['phone'];
        $openId = $_SESSION['plat_openid'];
        if(md5($phone.$code) != session('phoneVerify'))
        {
            echo json_encode(array(
                'errcode'	=> 10010,
                'errmsg'	=> '验证码错误!',
            ));exit;
        }
        if( D('Base/User')->isOccupy(array('phone'=>$phone)) )
        {
            echo json_encode(array(
                'errcode'  => 10011,
                'errmsg'   => '手机号已被注册！'
            ));exit();
        }
        $data = array(
            'phone'=>$phone,
            'openid'=>$openId,
            'create_time'=>time(),
        );
        $userId = D('Base/User')->add($data);
        $_SESSION['plat_public_id'] = $userId;
        $wechatController = new Wechat();
        $wechatController->publicId = 'gh_243fe4c4141f';
        $access_token = $wechatController->getAccessToken();
        $http = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openId.'&lang=zh_CN';
        $data = json_decode(https_request($http),true);
        $data['user_id'] = $userId;
        $data['create_time'] = time();
        D('Base/UserInfo')->add($data);
        echo json_encode(array(
            'errcode'=>0,
            'errmsg'=>'注册成功！'
        ));exit;
    }



    public function index()
    {
        $this->display('Home@Index/binding');
    }


    
    /***
     * 微信授权登录
     * @param $url
     */
    public function authorizationWechat()
    {
        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $appid = 'wx2e389f57cd3f6f51';
        $secret = 'd4624c36b6795d1d99dcf0547af5443d';
        if( $code = $_GET['code'] )
        {
            $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';
            $json = https_request($url);
            $data = json_decode($json,true);
            if( $data['openid'] ){
                A('User/PublicUser')->setToken($data['openid'], $data['access_token'], $data['refresh_token']);//存储最新access_token
                if( $userId = A('User/User')->isOccupy(array('openid'=>$data['openid'])) )
                {
                    $_SESSION['plat_user_id'] = $userId;
                    header('Location:http://www.koudaidaxue.com/index.php/Home/index/index/#/');exit();
                }else{
                    $_SESSION['plat_openid'] = $data['openid'];
                    header('Location:http://www.koudaidaxue.com/index.php/Home/index/index/#/register');exit();//跳入注册界面
                }
            }else{
                echo json_encode(array(
                    'errcode'=>1000,
                    'errmsg'=>'授权失败！'
                ));exit();
            }

        }else{
            $url = 'https://open.weixin.qq.com/connect/qrconnect?appid='.$appid.'&redirect_uri='.urlencode($url).'&response_type=code&scope=snsapi_login&state='.rand(100000,999999).'#wechat_redirect';
            header('Location:'.$url);exit();
        }
    }


    /**
     * 手机号绑定第三方
     */
    public function  bindingTthirdParty()
    {
        $userId = A('User/User')->verifyLogin(array('phone'=>$_POST['phone']));
        $data = array('user_id'=>$userId);
        $_SESSION['plat_openid'] ? $data['openid']=$_SESSION['plat_openid'] : '';
        $_SESSION['plat_email'] ? $data['email']=$_SESSION['plat_email'] : '';
        if( A('User/User')->setData($data) )
        {
            echo json_encode(array(
                'errcode'=>0,
                'errmsg'=>'绑定成功！'
            ));exit();
        }else{
            echo json_encode(array(
                'errcode'=>1000,
                'errmsg'=>'绑定失败！'
            ));exit();
        }
    }





}