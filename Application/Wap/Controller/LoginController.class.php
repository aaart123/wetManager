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
        print_r($_SESSION);
    }

    /**
     * 新媒静默授权登录
     */
    public function authorization($url)
    {
        if( $code = $_GET['code'] )
        {
            $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.self::APP_ID.'&secret='.self::APP_SECRET.'&code='.$code.'&grant_type=authorization_code';
            $json = https_request($url);
            $data = json_decode($json,true);
            if( $data['openid'] ){
                #判断手机号是否绑定新媒
                if( $userId = D('Base/User')->isOccupy(array('new_openid'=>$data['openid'])) )
                {
                    $_SESSION['plat_user_id'] = $userId;
                    header('Location:http://www.koudaidaxue.com/index.php/Home/#/');exit();//直接登录
                }else{
                    $_SESSION['social_openid'] = $data['openid'];
                    header('Location:http://www.koudaidaxue.com/index.php/Home/#/register');exit();//跳入注册界面
                }
            }else{
                $this->authorization();
            }
        }else{
            $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.self::APP_ID.'&redirect_uri='.urlencode($url).'&response_type=code&scope=snsapi_base&state='.rand(100000,999999).'#wechat_redirect';
            header('Location:'.$url);exit();
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
     * 绑定第三方
     */
    public function  bindingTthirdParty()
    {
        $userId = A('User/User')->verifyLogin(array('phone'=>$_POST['phone']));
        $data = array('user_id'=>$userId);
        $_SESSION['social_openid'] ? $data['new_openid']=$_SESSION['social_openid'] : '';
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
            $data['new_openid'] = isset($_SESSION['social_openid']) ? $_SESSION['social_openid'] : '';
            if (D('Base/User')->add($data)) {
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










}