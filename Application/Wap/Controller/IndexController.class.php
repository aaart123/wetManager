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
        $userId = $_SESSION['plat_user_id'];
        if(!D('Wap/Article')->where(array('user_id'=>$userId))->count())
        {
            $this->display('Index/newcomer');
        }else{
            $this->display('Index/index');
        }
    }

    public function admin()
    {
        $this->display('Index/manage');
    }

}