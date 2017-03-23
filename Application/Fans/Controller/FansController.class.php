<?php

namespace Fans\Controller;

use Base\Controller\WetchatApiController;

class FansController extends WetchatApiController
{
    /**
     * 获取用户信息
     */
    const USERINFO = 'https://api.weixin.qq.com/cgi-bin/user/info';

    /**
     * 批量获取用户
     */
    const BETCH = 'https://api.weixin.qq.com/cgi-bin/user/info/batchget';

    /**
     * 获取用户列表
     */
    const LISTS = 'https://api.weixin.qq.com/cgi-bin/user/get';

    /**
     * 查询用户列表
     */
    public function lists($nextOpenid = null)
    {
        $query = is_null($nextOpenid)
            ? array()
            : array('next_openid'=>$nextOpenid);
        
        $url = static::LISTS.''.$this->accessToken;
        $response = httpRequest($url);

        if( $response['errcode'] != 0 ) {
            throw new \Exception($response['errmsg'], $response['errcode']);
        }

        return $response;
    }

}