<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/3/15
 * Time: 11:13
 */

namespace Http\Controller;

use Http\Controller\BaseController;

class UserController extends BaseController
{



    /**
     * 获取用户信息
     * @param str   用户userID
     * @return array
     */
    public function getUserInfo()
    {
        $userId = $_SESSION['plat_user_id'];
        if( $data = A('User/User')->getUserInfo($userId) )
        {
            echo json_encode(array(
                'errcode'=>0,
                'errmsg'=>$data,
            ));exit();
        }else {
            echo json_encode(array(
                'errcode' => 10002,
                'errmsg' => '不合法的用户'
           ));exit();
        }
    }

    /***
     * 获取微信上的用户信息
     */
    public function getWechatUserInfo()
    {
        $userId = $_SESSION['plat_user_id'];
        $userInfo = A('User/User')->getUserInfo($userId);
        if( $userInfo['openid'] )
        {
            if( $data = A('User/user')->getWechatUserInfo($userInfo['openid']) )
            {
                echo json_encode(array(
                    'errcode'=>0,
                    'errmsg'=>$data
                ));exit();
            }else{
                echo json_encode(array(
                    'errcode'=>1000,
                    'errmsg'=>'获取失败！'
                ));exit();
            }
        }else{
            echo json_encode(array(
                'errcode'=>1000,
                'errmsg'=>'未授权微信号！'
            ));exit();
        }

    }





    /**、
     * 获取用户所管理的所有公众号
     */
    public function getPublic()
    {
        //$_SESSION['plat_user_id']=2;
       
        if( $data = A('User/PublicUser')->getPublic($_SESSION['plat_user_id']) )
        {

            foreach ($data as &$value) {
                $value = D('User/Public')->getPublicInfo($value['public_id']);
            }
            echo json_encode(array(
                'errcode'=>0,
                'errmsg'=>$data,
            ));exit();
        }else{
            echo json_encode(array(
                'errcode'=>1000,
                'errmsg'=>'数据为空！',
            ));exit();
        }
    }

    /**
     * 获取公众号信息
     * @param $publicId
     * @return mixed
     */
    Public function getPublicInfo()
    {
        $publicId = $_SESSION['plat_public_id'];
        $userId = $_SESSION['plat_user_id'];
        if( D('User/PublicUser')->isPublicAdmin($publicId, $userId) )
        {
            echo json_encode(array(
                'errcode'=>0,
                'errmsg'=>D('User/Public')->getPublicInfo($publicId),
            ));exit();
        }else {
            echo json_encode(array(
                'errcode' => 10010,
                'errmsg' => '非公众号管理员！'
            ));exit();
        }
    }

    /***
     * 切换公众号
     */
    public function changePublic()
    {
        if( A('User/PublicUser')->isPublicAdmin($_POST['public_id'], $_SESSION['plat_user_id']) )
        {
            $_SESSION['plat_public_id'] = $_POST['public_id'];
            echo json_encode(array('errcode'=>0,'errmsg'=>'请求成功！'));exit();
        }else{
            echo json_encode(array('errcode'=>1000,''=>'非公众号管理员！'));exit();
        }
    }





    /***
     * 设置主管理员
     * @param $publicId
     * @param $userId
     * @return array
     */
    Public function setPublicAdminMain($publicId, $userId)
    {
        if( D('User/PublicUser')->isPublicAdmin($publicId, $userId) )
        {
            if( D('User/PublicUser')->isPublicAdminMain($publicId, $userId) )
            {
                echo json_encode(array(
                    'errcode'=>10011,
                    'errmsg'=>'已是主管理员!'
                ));exit();
            }else{
                if( D('User/PublicUser')->setPublicAdminMain($publicId, $userId) )
                {
                    echo json_encode(array(
                        'errcode'=>0,
                        'errmsg'=>'请求成功！'
                    ));exit();
                }else{
                    echo json_encode(array(
                        'errcode'=>-1,
                        'errmsg'=>'网络错误！'
                    ));exit();
                }
            }
        }else{
            echo json_encode(array(
                'errcode'=>10010,
                'errmsg'=>'非公众号管理员！'
            ));exit();
        }
    }


    public function getPublicAdmin()
    {
        $publicId = $_SESSION['plat_public_id'];
        $adminList = A('User/PublicUser')->getPublicAdmin($publicId);
        $adminList = explode(',', $adminList);
        foreach($adminList as $value)
        {
            $publicAdmin = null;
            $data = A('User/User')->where(array('public_id'=>$value))->find();
            if(A('User/PublicUser')->isPublicAdmin($publicId,$value))
            {
                array_push($data,array('is_main'=>1));
                array_unshift($publicAdmin,$data);
            }else{
                array_push($data,array('is_main'=>0));
                array_push($publicAdmin,$data);
            }
        }
        echo json_encode(array(
                'errcode'=>0,
                'errmsg'=>$publicAdmin,
            ));exit();


    }








}