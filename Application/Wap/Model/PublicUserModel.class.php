<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/4/12
 * Time: 11:41
 */

namespace Wap\Model;

use Wap\Model\BaseModel;

class PublicUserModel extends BaseModel
{

    protected $tableName = 'kdgx_plat_public_user';


    public function getPublicInfo($userId)
    {
        $sql = 'SELECT `nick_name` ,`user_name` ,`head_img`,`fans`
                FROM `kdgx_wap_public`
                WHERE `user_name` IN(
                SELECT public_id
                FROM `kdgx_plat_public_user`
                WHERE find_in_set('.$userId.', `user_list`))';
        return $this->query($sql);
    }


}