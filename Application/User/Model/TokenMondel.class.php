<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/3/14
 * Time: 14:49
 */

namespace User\Model;

use Base\Model\AppModel;

class TokenMondel extends AppModel
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
    public function setToken($openid, $access_token)
    {
        if( $this->where(array('openid'=>$openid))->getfield('access_token') )
        {
            return $this->where(array('openid'=>$openid))->save(array('access_token'=>$access_token,'timestamp'=>time()));
        }else{
            return $this->add(array('openid'=>$openid,'access_token'=>$access_token,'timestamp'=>time()));
        }

    }




}