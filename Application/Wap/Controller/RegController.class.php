<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/4/14
 * Time: 16:21
 */

namespace Wap\Controller;

use Think\Controller;

class RegController extends Controller
{
    const APP_ID = 'wx3f6b8b2eb0483f2f';//微信分配的appID
    const APP_SECRET = '6861d005542696913ed31a9645690595';//微信分配的key

    public function __construct()
    {
        parent::__construct();

        if( !isset($_SESSION['wechat_info']) || empty($_SESSION['wechat_info']))
        {
            $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            if(isset($_GET['code']))
            {
                $code = $_GET['code'];
                $http = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.self::APP_ID.'&secret='.self::APP_SECRET.'&code='.$code.'&grant_type=authorization_code';
                $json = https_request($http);
                $data = json_decode($json,true);
                $http = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$data['access_token'].'&openid='.$data['openid'].'&lang=zh_CN';
                $data = json_decode(https_request($http),true);
                $_SESSION['wechat_info'] = $data;
            }else{
                $url = urlencode($url);
                $http = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.self::APP_ID.'&redirect_uri='.$url.'&response_type=code&scope=snsapi_userinfo&state='.rand(1000,9999).'#wechat_redirect';
                header('Location:'.$http);exit;
            }
        }
    }

    /**
     * 注册页面
     */
    public function index()
    {
        if($user_id = D('Wap/User')->where(array('openid'=>$_SESSION['wechat_info']['openid']))->getField('user_id'))
        {
            header('Location:http://www.koudaidaxue.com/index.php/Wap/Index/index');
        }else{
            $this->display('Index/login');
        }

    }

    /**
     * 获取微信用户信息
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
                'errmsg'=>'手机验证码发送频繁',
            ));exit();
        }
    }


    /**
     * 提交注册信息
     */
    public function reg()
    {
        $phone = $_POST['phone'];
        $code = $_POST['code'];
        $password = $_POST['password'];
        if(md5($phone.$code)==session('phoneVerify'))
        {
            if($userId = D('Wap/User')->where(array('phone'=>$phone))->getfield('user_id'))
            {
                echo json_encode(array(
                    'errcode'=>10002,
                    'errmsg'=>'手机号已被绑定',
                ));exit;
            }

            $data =array(
                'phone'=>$phone,
                'openid'=>$_SESSION['wechat_info']['openid'],
            );
            //用户注册入库
            $user = D('Wap/User');
            $user->create($data);
            $userId = $user->add();
            $_SESSION['plat_user_id'] = $userId;
            //用户信息入库
            $data = $_SESSION['wechat_info'];
            $data['user_id'] = $userId;
            $userinfo = D('Wap/UserInfo');
            $userinfo->create($data);
            $userinfo->add();
            //新媒圈配置表
            D('Wap/Conf')->add(array('user_id'=>$userId,'create_time'=>time()));
            //默认关注小口袋
            D('Wap/Subscribe')->add(array('user_id'=>$userId,'subscribe_user'=>'10007','subscribe_state'=>'1','create_time'=>time()));
            D('Wap/Subscribe')->add(array('user_id'=>'10007','subscribe_user'=>$userId,'subscribe_state'=>'1','create_time'=>time()));
            echo json_encode(array(
                'errcode'=>0,
                'errmsg'=>'注册成功！'
            ));exit;
        }else{
            echo json_encode(array(
                'errcode'=>10001,
                'errmsg'=>'验证码错误',
            ));exit;
        }
    }


    /**
     * 获取新媒圈用户人数
     */
    public function userCount()
    {
        $count = D('Wap/user')->count();
        echo json_encode(array(
            'errcode'=>0,
            'errmsg'=>$count,
        ));exit;
    }

}