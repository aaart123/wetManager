<?php
namespace Base\Model;

use Base\Model\BaseModel;

/**
 * 公众号管理员表模型
 */
class PublicUserModel extends AppModel
{
    protected $trueTableName = 'kdgx_plat_public_user';


    public function __construct()
    {
        parent:: __construct();
    }




    /**
     * 公众号添加管理员，并成为主管理员
     * @param $publicId
     * @param $userId
     * @return mixed
     */
    public function addPublicList($publicId, $userId)
    {
        return $this->add(array('public_id'=>$publicId,'user_list'=>$userId,'main_user'=>$userId));
    }

    /**
     * 修改公众号管理员
     */
    public function setPublicList($publicId, $userList)
    {
        return $this->where(array('public_id'=>$publicId))->save(array('user_list'=>$userList));
    }

    /**
     * 设置公众号主管理员
     * @param string $publicId
     * @param string $userId
     * @return mixed
     */
    public function setPublicAdminMain($publicId, $userId)
    {
        return $this->where(array('public_id'=>$publicId))->setField('user_Id',$userId);
    }

    /**
     * 验证是否为公众号主管理员
     * @param $publicId 公众号ID
     * @param $mainUser 用户ID
     * @return mixed
     */
    public function isPublicAdminMain($publicId, $userId)
    {
        return $this->where(array('public_id'=>$publicId,'main_user'=>$userId))->getField('public_id');
    }

    /**
     * 验证是否为公众号管理员
     * @param $publicId  公众号ID
     * @param $userId   用户ID
     * @return mixed
     */
    public function isPublicAdmin($publicId, $userId)
    {
        $sql = "select public_id from `kdgx_plat_public_user`  where public_id='$publicId' && find_in_set('$userId',`user_list`)";
        return  $this->query($sql);
    }


    /***
     * 获取用户所管理的公众号
     * @return mixed\
     */
    public function getPublicInfo($userId)
    {
        $sql = "SELECT p.`user_name`,
                       p.`nick_name`,
                       p.`head_img`,
                       p.`service_type_info`
                FROM `pocket`.`kdgx_plat_public` AS p
                INNER JOIN `kdgx_plat_authorizer` AS a ON a.`authorizer_appid`= p.`authorizer_appid`
                WHERE p.`user_name` IN(
                SELECT public_id
                FROM `kdgx_plat_public_user`
                WHERE find_in_set('$userId', `user_list`)  )
                AND a.`authorization_state`= '1'";
        return $this->query($sql);
    }


    public function isAuthPublic($publicId)
    {
        $sql = "SELECT au.`id` 
                FROM `pocket`.`kdgx_plat_authorizer` AS au
                INNER JOIN `kdgx_plat_public` AS p ON p.`authorizer_appid`= au.`authorizer_appid`
                WHERE p.`user_name`= '$publicId' && au.`authorization_state` = '1' ";
        return $this->query($sql);
    }

    
    
    /***
     * 获取公众号的所有管理员
     * @param $publicId
     * @return array\
     */
    public function getPublicAdmin($publicId)
    {
        return  $this->where(array('public_id'=>$publicId))->getfield('user_list');
    }





}

