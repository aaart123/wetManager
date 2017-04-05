<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/3/14
 * Time: 11:59
 */

namespace Http\Controller;

use Think\Controller;

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
                $publicList = A('User/PublicUser')->getPublic($userId);
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
        $username = addslashes($_POST['username']);
        $password = addslashes($_POST['password']);
        if( strpos($username, '@') )
        {
            if( $userId = A('User/User')->verifyLogin(array('email'=>$username,'password'=>md5($password))) )//邮箱登录
            {
                $_SESSION['plat_user_id'] = $userId;
                echo json_encode(array('errcode'=>0,'errmsg'=>'登录成功！'));exit();
            }else{
                echo json_encode(array('errcode'=>1000,'errmsg'=>'登录失败！'));exit();
            }
        }else{
            if( $userId = A('User/User')->verifyLogin(array('phone'=>$username,'password'=>md5($password))) )//手机登录
            {
                $_SESSION['plat_user_id'] = $userId;
                echo json_encode(array('errcode'=>0,'errmsg'=>'登录成功！'));exit();
            }else{
                echo json_encode(array('errcode'=>1000,'errmsg'=>'登录失败2！'));exit();
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
                'errmsg'=>'手机验证码发送失败',
            ));exit();
        }
    }


    /**
     * 验证手机验证码
     */
    public function checkPhone(){
        $phonecode = $_POST['phonecode'];
        $phone = $_POST['phone_number'];
        $secode = session('phoneVerify');
        if(md5($phone.$phonecode)==$secode){
            echo json_encode(array(
                'errcode' => 0,
                'errmsg'	=> '成功',
            ));exit;
        }else{
            echo json_encode(array(
                'errcode'	=> 10010,
                'errmsg'	=> '验证码错误!',
            ));exit;
        }
    }


    /**
     * 首次提交注册信息
     */
    public function reg()
    {
        if( D('Base/User')->isOccupy(array('phone'=>$_POST['phone'])) )
        {
            echo json_encode(array(
                'errcode'=>1000,
                'errmsg'=>'手机号已被注册！'
            ));exit();
        }else{
            if(md5($_POST['phone'].$_POST['code']) != session('phoneVerify'))
            {
                echo json_encode(array(
                    'errcode'=>1000,
                    'errmsg'=>'验证码错误！'
                ));exit();
            }
            $data['phone'] = $_POST['phone'];
            $data['password'] = md5($_POST['password']);
            $data['openid'] = isset($_SESSION['plat_openid']) ? $_SESSION['plat_openid'] : '';
            $data['email'] = isset($_SESSION['plat_email']) ? $_SESSION['plat_email'] : '';
            if ($userId= D('Base/User')->add($data)) {
                $_SESSION['plat_user_id'] = $userId;
                echo json_encode(array(
                    'errcode' => 0,
                    'errmsg' => '请求成功！'
                ));exit();
            } else {
                echo json_encode(array(
                    'errcode' => 1000,
                    'errmsg' => '注册失败！'
                ));exit();
            }
        }
    }



    public function index()
    {
        var_dump($_SESSION);
    }

    /***
     * 微信授权登录
     * @param $url
     */
    public function authorizationWechat()
    {
        $url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
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
                    header('Location:http://www.koudaidaxue.com/index.php/Home/#/');exit();
                }else{
                    $_SESSION['plat_openid'] = $data['openid'];
                    header('Location:http://www.koudaidaxue.com/index.php/Home/#/register');exit();//跳入注册界面
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