<?php 
namespace Event\Controller;

use Base\Controller\CommonController;

/**
 * 事件控制基础类
 */
class BaseController extends CommonController
{
    protected $publicId;
    
    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}