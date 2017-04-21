<?php

namespace Wap\Controller;

use Wap\Controller\LoginController;

class BaseController extends LoginController
{

    public function __construct()
    {
        parent::__construct();
        //授权公众号
        $userId = $_SESSION['plat_user_id'];
        $data = D('Wap/PublicUser')->getPublicInfo($userId);
        if( $data = D('Wap/PublicUser')->getPublicInfo($userId) )
        {
            $login_public = D('Wap/conf')->where(array('user_id'=>$userId))->getfield('login_public');
            $_SESSION['plat_public_id'] = $login_public;
        }else{
            header('Location:http://www.koudaidaxue.com/index.php/wap/Login/binding');exit;
        }
    }

}