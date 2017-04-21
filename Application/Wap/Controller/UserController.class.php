<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/3/28
 * Time: 21:53
 */

namespace Wap\Controller;

use Wap\Controller\BaseController;
use Wap\Model\PublicSubscribeModel;


class UserController extends BaseController
{
    const APP_ID = 'wxe8b12da30f8ed757';//微信分配的appID
    const APP_SECRET = '4a266d702e91408183772dcd3a774dfc';//微信分配的key


    public function usercenter()
    {
        $this->display('Index/usercenter');
    }

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
                if( $userId = D('Base/User')->isOccupy(array('openid'=>$data['openid'])) )
                {
                    $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$data['access_token'].'&openid='.$data['openid'].'&lang=zh_CN';
                    $json = https_request($url);
                    $json = json_decode($json);

                    $data = array(
                        'user_id'       => $userId,
                        'nickname'      => $json->nickname,
                        'sex'           => $json->sex,
                        'province'      => $json->province,
                        'city'          => $json->city,
                        'country'       => $json->country,
                        'headimgurl'    => $json->headimgurl,
                    );
                    D('UserInfo')->add($data,array(),true);
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


    public function index()
    {
        print_r($_SESSION);
    }


    /**
    *主页信息
    */
    public function homepageUserInfo()
    {
        // 主页信息
        // http://www.koudaidaxue.com/index.php/Wap/user/homepageUserInfo
        // $_POST = array(
        //     'user_id'=>'',
        // );

        $userId = $_POST['user_id'] ? $_POST['user_id'] : $_SESSION['plat_user_id'];
        $data = D('UserInfo')->getUserInfo($userId);
        $data['self'] = $userId==$_SESSION['plat_user_id'] ? 1 : 0;
        $data['active'] = D('subscribe')->where(array('user_id'=>$userId,'subscribe_state' =>'1'))->count();//关注人数
        $data['publics'] = D('PublicSubscribe')->where(array('user_id'=>$userId,'state' =>'1'))->count(); // 关注公众号数
        $data['passive'] = D('subscribe')->where(array('subscribe_user'=>$userId,'subscribe_state' =>'1'))->count();//被关注人数
        $data['is_subscribe'] = D('subscribe')->where(
                        array('subscribe_user'=>$userId,'user_id'=>$_SESSION['plat_user_id'])
                    )->getField('subscribe_state')?:0;
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
        // 获取用户所管理的所有公众号
        // http://www.koudaidaxue.com/index.php/Wap/user/getPublic
        $userId = $_SESSION['plat_user_id'];
        if( $data = D('PublicUser')->getPublicInfo($userId) )
        {
            foreach ($data as &$value) {
                if($value['user_name'] == $_SESSION['plat_public_id'] )
                {
                    $value['now'] = 1;
                }
            }
            echo json_encode(array(
                'errcode'=>0,
                'errmsg'=>$data,
            ));exit;
        }else{
            echo json_encode(array(
                'errcode'=>1000,
                'errmsg'=>'数据为空！',
            ));exit;
        }
    }

    public function recommendPublic()
    {
        // 推荐公众号
            // http://www.koudaidaxue.com/index.php/Wap/user/recommendPublic
         
    }




    /**
     * 关注者信息
     */
    public function getSubscribeInfo()
    {
        // 关注者信息
        // http://www.koudaidaxue.com/index.php/Wap/user/getSubscribeInfo
        // $_POST = array(
        //     'user_id'=>1,
        // );
        $userId = $_POST['user_id'] ? $_POST['user_id'] : $_SESSION['plat_user_id'];

        $data = D('subscribe')->getSubScribeUserInfo($userId);
        foreach ($data['active'] as &$value)
        {
            $value['subscribe'] = D('subscribe')->where(array('subscribe_user'=>$value['user_id'],'subscribe_state'=>'1'))->count();//关注人数
            $value['is_subscribe'] = D('subscribe')->where(
                array('subscribe_user'=>$value['user_id'],'user_id'=>$_SESSION['plat_user_id'])
            )->getField('subscribe_state')?:0;
            $value['self'] = $value['user_id']==$_SESSION['plat_user_id'] ? 1 : 0;
        }
        foreach ($data['passive'] as &$value)
        {
            $value['subscribe'] = D('subscribe')->where(array('subscribe_user'=>$value['user_id'],'subscribe_state'=>'1'))->count();//被关注人数
            $value['is_subscribe'] = D('subscribe')->where(
                array('subscribe_user'=>$value['user_id'],'user_id'=>$_SESSION['plat_user_id'])
            )->getField('subscribe_state')?:0;
            $value['self'] = $value['user_id']==$_SESSION['plat_user_id'] ? 1 : 0;
        }
        echo json_encode(array(
            'errcode'=>0,
            'errmsg'=>$data,
        ));exit;
    }

    public function getSubscribePublic()
    {
        // 关注公众号信息
            // http://www.koudaidaxue.com/index.php/Wap/user/getSubscribePublic
            // $_POST = array(
            //     'user_id'=>1,
            // );
        $userId = $_POST['user_id'] ? $_POST['user_id'] : $_SESSION['plat_user_id'];
        $where = array('user_id'=>$userId,'state' =>'1');
        $publics = D('PublicSubscribe')->getAll($where);
        $publicModel = D('Public');
        $psModel = D('PublicSubscribe');
        $response = [];
        foreach ($publics as $public) {
            $wh['user_name'] = $public['public_id'];
            $data = $publicModel->getData($wh);
            $psWh['public_id'] = $public['public_id'];
            $psWh['state'] = 1;
            $data['subscribes'] = $psModel->where($psWh)->count();
            $psWh['user_id'] = $_SESSION['plat_user_id'];
            $data['is_subscribe'] = D('PublicSubscribe')->where($psWh)->getField('state')?:0;
            $response[] = $data;
        }
        echo json_encode(array(
            'errcode'=>0,
            'errmsg'=>$response
        ));exit;
    }





    /**
     * 关注用户或者取关用户
     */
    public function subscribeUser()
    {
        // 关注用户或者取关用户
        // http://www.koudaidaxue.com/index.php/Wap/user/subscribeUser
        // $_POST =array(
        //     'user_id'=>1,
        //     'state'=>1,
        // );
        $subscribe_user = $_POST['user_id'];
        $subscribe_state = $_POST['state'];
        $userId = $_SESSION['plat_user_id'];

        if( D('Subscribe')->field('subscribe_user')->where(array('subscribe_user'=>$subscribe_user,'user_id'=>$userId))->find())
        {
            D('Subscribe')->where(array('subscribe_user'=>$subscribe_user,'user_id'=>$userId))->setfield('subscribe_state',$subscribe_state);
            $is_subscribe = D('Conf')->where(array('user_id'=>$subscribe_user))->getfield('is_subscribe');
            if($subscribe_state == '1' && $is_subscribe == '1')
            {
                ##关注模板消息推送
            }
        }else{
            $data = array(
                'subscribe_user' => $subscribe_user,
                'user_id' => $userId,
                'subscribe_state' => $subscribe_state
            );
            D('Subscribe')->create($data);
            D('Subscribe')->add();
        }
        echo json_encode(array(
            'errcode'=>0,
            'errmsg'=>'OK',
        ));exit;
    }


    public function subscribePublic()
    {
        $post = I('post.');
        // 取消/关注公众号
            // http://www.koudaidaxue.com/index.php/Wap/user/subscribePublic
            // $post = [
            //     'public_id' => 'gh_1234518'
            // ];
        $publicSubscribe = new PublicSubscribeModel();
        $data = [
            'user_id' => session('plat_user_id'),
            'public_id' => $post['public_id']
        ];
        if ($states= $publicSubscribe->getData($data)) {
            $save['state'] = 0;
            !$states['state'] && $save['state'] = 1;
            $publicSubscribe->editData($data, $save);
        } else {
            $data['state'] = 1;
            $publicSubscribe->addData($data);
        }
        echo json_encode(array(
            'errcode'=>0,
            'errmsg'=>'OK'
        ));exit;
    }


    /**
     * 切换公众号
     */
    public function changePublic()
    {
        $publicId = $_POST['public_id'];
        $userId = $_SESSION['plat_user_id'];
        D('Conf')->where(array('user_id'=>$userId))->setfield('login_public',$publicId);
        $_SESSION['plat_public_id'] = $publicId;
        echo json_encode(array(
            'errcode'=>0,
            'errmsg'=>'OK',
        ));eixt;
    }

    /**
     * 获取日报配置信息
     */
    public function getConf()
    {
        // 获取日报配置信息
        // http://www.koudaidaxue.com/index.php/Wap/user/getConf

        $userId = $_SESSION['plat_user_id'];
        if($data = D('Conf')->field('is_daily,is_subscribe')->where(array('user_id'=>$userId))->find())
        {
            echo json_encode(array(
                'errcode'=>0,
                'errmsg'=>$data,
            ));
        }else{
            $data = array(
                'is_daily'=>1,
                'is_subscribe'=>1,
            );
            echo json_encode(array(
                'errcode'=>0,
                'errmsg'=>$data,
            ));exit;
        }
    }

    /**
     * 设置配置页
     */
    public function setConf()
    {
        //设置日报配置信息
        //http://www.koudaidaxue.com/index.php/Wap/user/setConf
        //        $_POST = array(
        //            'is_daily'=>'0',
        //            'is_subscribe'=>'1',
        //        );
        $userId = $_SESSION['plat_user_id'];
        $data = array(
            'user_id'=>$userId,
            'is_daily'=>$_POST['is_daily'],
            'is_subscribe'=>$_POST['is_subscribe'],
            'modifield_time'=>time(),
        );
        if(D('Conf')->where(array('user_id'=>$userId))->getField('user_id')){
            D('Conf')->where(array('user_id'=>$userId))->save($data);
        }else{
            $data['create_time'] = time();
            D('Conf')->where(array('user_id'=>$userId))->add($data);
        }
        echo json_encode(array(
            'errcode'=>0,
            'errmsg'=>'OK',
        ));
    }


    /**
     * 获取公众号的信息
     */
    public function getPublicInfo()
    {
        if($publicId = $_POST['public_id'])
        {
            $userId = $_SESSION['plat_user_id'];
            $data = D('Wap/Public')->field('user_name,alias,nick_name,head_img')->where(array('user_name'=>$publicId))->find();
            $data['is_sub'] = D('Wap/PublicSubscribe')->where(array('user_id'=>$userId,'public_id'=>$publicId))->getField('state')?:0;
            echo json_encode(array(
                'errmsg'=>0,
                'errmsg'=>$data,
            ));exit;
        }else{
            echo json_encode(array(
                'errcode'=>10001,
                'errmsg'=>'参数错误',
            ));exit;
        }
    }

}