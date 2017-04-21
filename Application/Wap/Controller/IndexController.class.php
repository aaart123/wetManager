<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/4/14
 * Time: 16:57
 */

namespace Wap\Controller;

use Wap\Controller\BaseController;

class IndexController extends BaseController
{

    public function index()
    {
        $this->display('Index/index');
    }
}