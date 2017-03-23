<?php
namespace Base\Model;

use Base\Model\BaseModel;

/**
 * 用户缓存模型
 */
class UserCacheModel extends BaseModel
{
    protected $trueTableName = 'kddx_user_cache';

    public function __construct()
    {
        parent:: __construct();
    }

    public function getCache($openid)
    {
        
    }
}