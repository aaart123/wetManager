<?php
/**
 * Created by PhpStorm.
 * User: 李欣
 * Date: 2017/3/28
 * Time: 17:17
 */

namespace Wap\Controller;

use Wap\Controller\BaseController;

class IndexController extends BaseController
{

    function index()
    {
        print_r($_SESSION);
    }

}