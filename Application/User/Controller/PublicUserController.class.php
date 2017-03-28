<?php
namespace User\Controller;

use Base\Controller\BaseController;

class PublicUserController extends BaseController
{

    /*----------------------------------------*\
            管理员管理的公众号信息
    \*----------------------------------------*/

    /**
     * 获取用户所管理的所有公众号
     * @param $userId
     * @return mixed
     */
    public function getPublic($userId)
    {
        return D('Base/PublicUser')->getPublic($userId);
    }


    /**
     * 添加公众号管理员
     * @param $publicId
     * @param $userId
     * @return mixed
     */
    public function addPublicList($publicId, $userId)
    {
        echo $publicId.$userId;
        if( $this->isPublicAdmin($publicId, $userId) )
        {
            return false;
        }else{
            if( $userList = D('Base/PublicUser')->getPublicAdmin($publicId))
            {
                $userList = $userList.','.$userId;
                return D('Base/PublicUser')->setPublicList($publicId, $userList);
            }else{
                return D('Base/PublicUser')->addPublicList($publicId, $userId);
            }
        }
    }






    /*-----------------------------------------*\
                  公众号所对应的信息
    \*-----------------------------------------*/

    /**
     * 获取公众号所有的管理员
     * @param $publicId
     * @return mixed
     */
    public function getPublicAdmin($publicId)
    {
        return D('Base/PublicUser')->getPublicAdmin($publicId);
    }

    /**
     * 验证是否为公众号管理员
     * @param $publicId  公众号ID
     * @param $userId   用户ID
     * @return mixed
     */
    public function isPublicAdmin($publicId, $userId)
    {
        return D('Base/PublicUser')->isPublicAdmin($publicId, $userId);
    }


    /**
     * 修改最新的Token
     * @param $openid
     * @param $access_token
     * @return mixed
     */
    public function setToken($openid, $access_token, $refresh_token)
    {
        return D('Base/Token')->setToken($openid, $access_token, $refresh_token);
    }



}