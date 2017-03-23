<?php
namespace User\Controller;

use Base\Controller\BaseController;

class PublicUserController extends Controller
{


    /**
     * 获取公众号信息
     * @param $publicId
     * @return mixed
     */
    Public function getPublicInfo($publicId, $userId)
    {
        if( D('User/PublicUser')->isPublicAdmin($publicId, $userId) )
        {
            return array(
                'errcode'=>0,
                'errmsg'=>D('User/Public')->getPublicInfo($publicId),
            );
        }else{
            return array('errcode'=>10010,'errmsg'=>'非公众号管理员！');
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
                return array('errcode'=>10011,'errmsg'=>'已是主管理员');
            }else{
                if( D('User/PublicUser')->setPublicAdminMain($publicId, $userId) )
                {
                    return array('errcode'=>0,'errmsg'=>'请求成功！');
                }else{
                    return array('errcode'=>-1,'errmsg'=>'网络错误！');
                }
            }

        }else{
            return array('errcode'=>10010,'errmsg'=>'非公众号管理员！');
        }
    }





}