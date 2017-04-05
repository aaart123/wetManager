<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/3/28
 * Time: 14:23
 */

namespace Wap\Controller;

use Think\Controller;

class LoginController extends Controller
{
//    const APP_ID = 'wx3f6b8b2eb0483f2f';//微信分配的appID
//    const APP_SECRET = '6861d005542696913ed31a9645690595';//微信分配的key
    const APP_ID = 'wxe8b12da30f8ed757';//微信分配的appID
    const APP_SECRET = '4a266d702e91408183772dcd3a774dfc';//微信分配的key
    
    function index()
    {
        $this->display('Index/login');
    }




    /**
     * 微信静默授权
     */
    public function silentAuthWechat($url)
    {
        if($code = $_GET['code'] )
        {
            $http = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.self::APP_ID.'&secret='.self::APP_SECRET.'&code='.$code.'&grant_type=authorization_code';
            $json = https_request($http);
            $data = json_decode($json,true);
            if( $userId = D('Base/User')->isOccupy(array('new_openid'=>$data['openid'])) )
            {
                $_SESSION['plat_user_id'] = $userId;
                header('Location:'.$url);exit;
            }else{
                $url = 'http://www.koudaidaxue.com/index.php/Wap/Login/authWechat';
                $url = urlencode($url);
                $http = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.self::APP_ID.'&redirect_uri='.$url.'&response_type=code&scope=snsapi_userinfo&state='.rand(1000,9999).'#wechat_redirect';
                header('Location:'.$http);exit;
            }
        }else{
            $url = urlencode($url);
            $http = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.self::APP_ID.'&redirect_uri='.$url.'&response_type=code&scope=snsapi_base&state='.rand(1000,9999).'#wechat_redirect';
            header('Location:'.$http);exit;
        }

    }


    /**
     * 微信网页授权
     */
    public function authWechat()
    {
            $code = $_GET['code'];
            $http = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.self::APP_ID.'&secret='.self::APP_SECRET.'&code='.$code.'&grant_type=authorization_code';
            $json = https_request($http);
            $data = json_decode($json,true);
            $http = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$data['access_token'].'&openid='.$data['openid'].'&lang=zh_CN';
            $json = https_request($http);
            $data = json_decode($json,true);
            $_SESSION['wechat_info'] = $data;
            header('Location:'.'http://www.koudaidaxue.com/index.php/wap/Login/index#/register');exit;
    }


    /**
     * 获取用户头像
     */
    public function getUserInfo()
    {
        $data = array(
            'nickname'=>$_SESSION['wechat_info']['nickname'],
            'headimgurl'=>$_SESSION['wechat_info']['headimgurl'],
        );
        echo json_encode(array(
            'errcode'=>0,
            'errmsg'=>$data,
        ));exit;
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
                'errmsg'=>'手机验证码发送失败',
            ));exit();
        }
    }




    public function banding()
    {
        $phone = $_POST['phone'];
        $code = $_POST['code'];

        if(md5($phone.$code)==session('phoneVerify'))
        {
            $userId = D('Base/User')->where(array('phone'=>$phone))->getfield('user_id');
            if( $userId )
            {
                D('Base/User')->where(array('user_id'=>$userId))->setfield('new_openid',$_SESSION['wechat_info']['openid']);
                $data = $_SESSION['wechat_info'];
                $data['user_id'] = $userId;
                D('UserInfo')->add($data,array(),true);
                $_SESSION['plat_user_id'] = $userId;
                echo json_encode(array(
                    'errcode'=>0,
                    'errmsg'=>'成功',
                ));exit;
            }else{
                echo json_encode(array(
                    'errcode'=>1,
                    'errmsg'=>'没有注册！',
                ));exit;
            }

        }else{
            echo json_encode(array(
                'errcode'=>10001,
                'errmsg'=>'验证码错误',
            ));exit;
        }
    }



public function reg()
{
    $phone = $_POST['phone'];
    $code = $_POST['code'];
    $password = $_POST['password'];
    if(md5($phone.$code)==session('phoneVerify'))
    {
        $data = array(
            'phone'=>$phone,
            'password'=>md5($password),
            'new_openid'=>$_SESSION['wechat_info']['openid'],
        );
        $userId = D('Base/User')->add($data);
        $data = $_SESSION['wechat_info'];
        $data['user_id'] = $userId;
        D('UserInfo')->add($data,array(),true);
        $_SESSION['plat_user_id'] = $userId;
        echo json_encode(array(
            'errcode'=>0,
            'errmsg'=>'注册成功！',
        ));exit;
    }else{
        echo json_encode(array(
            'errcode'=>10001,
            'errmsg'=>'验证码错误',
        ));exit;
    }


}






}