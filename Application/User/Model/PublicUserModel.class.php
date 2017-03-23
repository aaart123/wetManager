<?php
namespace User\Model;

use Base\Model\AppModel;

/**
 * 公众号管理员表模型
 */
class UserModel extends AppModel
{
    protected $trueTableName = 'kdgx_plat_public_user';


    public function __construct()
    {
        parent:: __construct();
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
        $sql = "select public_id from `kdgx_plat_public_user`  where public_id='$publicId' find_in_set('$userId',`user_list`)";
        return  $this->query($sql);
    }

    

}

