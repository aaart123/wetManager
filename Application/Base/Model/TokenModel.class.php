<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/3/15
 * Time: 16:46
 */

namespace Base\Model;

use Base\Model\BaseModel;

class TokenModel extends AppModel
{
    protected $trueTableName = 'kdgx_plat_token';

    public function __construct()
    {
        parent:: __construct();
    }


    /***
     * 修改最新获取的access_token
     * @param $openid
     * @param $access_token
     * @return mixed
     */
    public function setToken($openId, $access_token, $refresh_token,$type=1)
    {
        if( $this->where(array('openid'=>$openId))->getfield('access_token') )
        {
            return $this->where(array('openid'=>$openId))->save(array('access_token'=>$access_token,'refresh_token'=>$refresh_token,'timestamp'=>time(),'type'=>$type));
        }else{
            return $this->add(array('openid'=>$openId,'access_token'=>$access_token,'refresh_token'=>$refresh_token,'timestamp'=>time(),'type'=>$type));
        }

    }


    /**
     * 获取access_token
     * @param $openId
     * @return mixed
     */
    public function getToken($openId)
    {
        return $this->where(array('openid'=>$openId))->find();
    }


}